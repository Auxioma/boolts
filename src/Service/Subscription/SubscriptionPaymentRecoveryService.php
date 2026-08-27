<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Dto\Subscription\PaymentFailureDetails;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\DowngradeReason;
use App\Entity\Billing\Enum\SubscriptionEmailType;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Exception\StripeTemporaryUnavailableException;
use App\Service\Stripe\StripeInvoiceService;
use App\Service\Stripe\StripePaymentService;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice as StripeInvoice;
use Stripe\PaymentIntent;
use Stripe\Subscription as StripeSubscription;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SubscriptionPaymentRecoveryService
{
    public const MAX_ATTEMPTS = 5;
    public const RECOVERY_DAYS = 5;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private StripeSubscriptionService $stripeSubscriptionService,
        private StripeInvoiceService $stripeInvoiceService,
        private StripePaymentService $stripePaymentService,
        private SubscriptionPaymentService $paymentService,
        private SubscriptionDowngradeService $downgradeService,
        private SubscriptionEmailDispatcher $emailDispatcher,
        private SubscriptionHistoryRecorder $historyRecorder,
        private LoggerInterface $logger,
        #[Autowire('%app.subscription_retry_strategy%')]
        private string $retryStrategy,
    ) {
    }

    public static function shouldFinalizeRecovery(
        AgencySubscription $subscription,
        \DateTimeImmutable $now,
    ): bool {
        if ($subscription->getPaymentFailureCount() >= self::MAX_ATTEMPTS) {
            return true;
        }

        $deadline = $subscription->getPaymentRecoveryDeadline();

        return $deadline instanceof \DateTimeImmutable && $deadline < $now;
    }

    public static function resolveNextPaymentRetryAt(
        \DateTimeImmutable $now,
        \DateTimeImmutable $deadline,
        int $attemptNumber,
    ): ?\DateTimeImmutable {
        if ($attemptNumber >= self::MAX_ATTEMPTS) {
            return null;
        }

        $nextRetryAt = $now->modify('+1 day');

        return $nextRetryAt > $deadline ? $deadline : $nextRetryAt;
    }

    public function handleInvoicePaymentFailed(
        AgencySubscription $subscription,
        StripeSubscription $stripeSubscription,
        StripeInvoice $stripeInvoice,
        ?\DateTimeImmutable $now = null,
    ): void {
        $now ??= new \DateTimeImmutable();
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

        if ($invoice->isPaid()) {
            $this->paymentService->recordPaidInvoice(
                $subscription,
                $stripeInvoice,
                $stripeSubscription,
                null,
                SubscriptionHistoryEventType::PAYMENT_RECOVERED,
            );

            return;
        }

        $paymentIntent = $this->fetchPaymentIntentQuietly($invoice->paymentIntentId);
        $failureDetails = $this->stripePaymentService->failureDetailsFromPaymentIntent($paymentIntent);
        $attemptNumber = max(1, $invoice->attemptCount, $subscription->getPaymentFailureCount() + 1);

        $this->registerFailedAttempt(
            $subscription,
            $stripeInvoice,
            $paymentIntent,
            min($attemptNumber, self::MAX_ATTEMPTS),
            $failureDetails,
            $now,
        );
    }

    public function processRetry(
        AgencySubscription $subscription,
        ?\DateTimeImmutable $now = null,
    ): void {
        $now ??= new \DateTimeImmutable();

        if (self::shouldFinalizeRecovery($subscription, $now)) {
            $this->finalizeDefinitiveFailure($subscription, $now);

            return;
        }

        try {
            [$stripeSubscription, $stripeInvoice] = $this->retrieveStripeState($subscription);
            $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

            if ($invoice->isPaid()) {
                $this->recoverPaidInvoice($subscription, $stripeInvoice, $stripeSubscription);

                return;
            }

            if (!$invoice->isOpen()) {
                $this->finalizeDefinitiveFailure($subscription, $now);

                return;
            }

            $attemptNumber = $subscription->getPaymentFailureCount() + 1;

            if ($attemptNumber > self::MAX_ATTEMPTS) {
                $this->finalizeDefinitiveFailure($subscription, $now);

                return;
            }

            if ('stripe_managed' === $this->retryStrategy) {
                $this->observeStripeManagedRetry($subscription, $stripeInvoice, $now);

                return;
            }

            $paidOrFailedInvoice = $this->stripeInvoiceService->payOpenInvoice($invoice->id, $attemptNumber);
            $paidOrFailedSnapshot = $this->stripeInvoiceService->snapshot($paidOrFailedInvoice);

            if ($paidOrFailedSnapshot->isPaid()) {
                $this->recoverPaidInvoice($subscription, $paidOrFailedInvoice, $stripeSubscription, $attemptNumber);

                return;
            }

            $paymentIntent = $this->fetchPaymentIntentQuietly($paidOrFailedSnapshot->paymentIntentId);
            $failureDetails = $this->stripePaymentService->failureDetailsFromPaymentIntent($paymentIntent);

            $this->registerFailedAttempt(
                $subscription,
                $paidOrFailedInvoice,
                $paymentIntent,
                $attemptNumber,
                $failureDetails,
                $now,
            );
        } catch (ApiErrorException $exception) {
            if ($this->stripePaymentService->isTemporaryStripeFailure($exception)) {
                $this->logTemporaryStripeFailure($subscription, $exception, 'PAYMENT_RETRY');

                return;
            }

            $invoiceId = $subscription->getProviderLatestInvoiceId();

            if (null === $invoiceId) {
                throw $exception;
            }

            try {
                $stripeInvoice = $this->retrieveInvoiceAfterPaymentFailure($invoiceId);
            } catch (StripeTemporaryUnavailableException $temporaryException) {
                $this->logger->error('[SUBSCRIPTION CRON] Temporary Stripe error after failed retry, no payment failure counted.', [
                    'subscription' => $subscription->getId(),
                    'agency' => $subscription->getAgency()->getId(),
                    'invoice' => $invoiceId,
                    'message' => $temporaryException->getMessage(),
                ]);

                return;
            }

            $invoiceSnapshot = $this->stripeInvoiceService->snapshot($stripeInvoice);
            $paymentIntent = $this->fetchPaymentIntentQuietly($invoiceSnapshot->paymentIntentId);

            if ($invoiceSnapshot->isPaid()) {
                $stripeSubscription = $this->stripeSubscriptionService->retrieve((string) $subscription->getProviderSubscriptionId());
                $this->recoverPaidInvoice($subscription, $stripeInvoice, $stripeSubscription);

                return;
            }

            $this->registerFailedAttempt(
                $subscription,
                $stripeInvoice,
                $paymentIntent,
                min($subscription->getPaymentFailureCount() + 1, self::MAX_ATTEMPTS),
                $this->stripePaymentService->failureDetailsFromApiException($exception),
                $now,
            );
        }
    }

    public function finalizeDefinitiveFailure(
        AgencySubscription $subscription,
        ?\DateTimeImmutable $now = null,
    ): void {
        $now ??= new \DateTimeImmutable();

        try {
            [$stripeSubscription, $stripeInvoice] = $this->retrieveStripeState($subscription);
            $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

            if ($invoice->isPaid()) {
                $this->recoverPaidInvoice($subscription, $stripeInvoice, $stripeSubscription);

                return;
            }

            if ('canceled' !== (string) $stripeSubscription->status) {
                $this->stripeSubscriptionService->cancelNow($subscription);
            }
        } catch (ApiErrorException $exception) {
            if ($this->stripePaymentService->isTemporaryStripeFailure($exception)) {
                $this->logTemporaryStripeFailure($subscription, $exception, 'FINAL_PAYMENT_FAILURE_CHECK');

                return;
            }

            throw $exception;
        }

        $this->historyRecorder->record(
            subscription: $subscription,
            eventType: SubscriptionHistoryEventType::PAYMENT_DEFINITIVELY_FAILED,
            oldStatus: $subscription->getStatus(),
            newStatus: SubscriptionStatus::EXPIRED,
            oldPlan: $subscription->getPlan()->getCode(),
            newPlan: 'free',
            providerInvoiceId: $subscription->getProviderLatestInvoiceId(),
            metadata: [
                'failure_count' => $subscription->getPaymentFailureCount(),
                'deadline' => $subscription->getPaymentRecoveryDeadline()?->format(\DATE_ATOM),
            ],
        );

        $this->emailDispatcher->dispatchOnce(
            $subscription,
            SubscriptionEmailType::PAYMENT_DEFINITIVELY_FAILED,
            'payment-definitively-failed-'.($subscription->getProviderLatestInvoiceId() ?? (string) $subscription->getId()),
            [
                'plan' => $subscription->getPlan()->getCode(),
                'failure_count' => $subscription->getPaymentFailureCount(),
                'deadline' => $subscription->getPaymentRecoveryDeadline()?->format(\DATE_ATOM),
            ],
        );

        $this->entityManager->flush();
        $this->downgradeService->downgradeToFree($subscription, DowngradeReason::PAYMENT_DEFINITIVELY_FAILED, $now);
    }

    private function registerFailedAttempt(
        AgencySubscription $subscription,
        StripeInvoice $stripeInvoice,
        ?PaymentIntent $paymentIntent,
        int $attemptNumber,
        PaymentFailureDetails $failureDetails,
        \DateTimeImmutable $now,
    ): void {
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);
        $alreadyCounted = $attemptNumber <= $subscription->getPaymentFailureCount();
        $oldStatus = $subscription->getStatus();

        $this->paymentService->recordFailedInvoiceAttempt(
            $subscription,
            $stripeInvoice,
            $paymentIntent,
            $attemptNumber,
            $failureDetails,
        );

        if ($alreadyCounted) {
            return;
        }

        $firstFailureAt = $subscription->getFirstPaymentFailureAt() ?? $now;
        $deadline = $subscription->getPaymentRecoveryDeadline() ?? $firstFailureAt->modify('+'.self::RECOVERY_DAYS.' days');
        $nextRetryAt = self::resolveNextPaymentRetryAt($now, $deadline, $attemptNumber);

        $newStatus = $attemptNumber >= self::MAX_ATTEMPTS
            ? SubscriptionStatus::PAYMENT_FAILED
            : SubscriptionStatus::PAST_DUE;

        $subscription
            ->setStatus($newStatus)
            ->setProviderLatestInvoiceId($invoice->id)
            ->setPaymentFailureCount($attemptNumber)
            ->setFirstPaymentFailureAt($firstFailureAt)
            ->setLastPaymentFailureAt($now)
            ->setPaymentRecoveryDeadline($deadline)
            ->setNextPaymentRetryAt($nextRetryAt)
            ->setLastStripeSyncAt($now);

        $this->historyRecorder->record(
            subscription: $subscription,
            eventType: 1 === $attemptNumber
                ? SubscriptionHistoryEventType::RENEWAL_FAILED
                : SubscriptionHistoryEventType::PAYMENT_RETRY,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            oldPlan: $subscription->getPlan()->getCode(),
            newPlan: $subscription->getPlan()->getCode(),
            providerInvoiceId: $invoice->id,
            providerPaymentIntentId: $invoice->paymentIntentId,
            metadata: [
                'attempt' => $attemptNumber,
                'max_attempts' => self::MAX_ATTEMPTS,
                'next_retry_at' => $nextRetryAt?->format(\DATE_ATOM),
                'recovery_deadline' => $deadline->format(\DATE_ATOM),
                'failure_code' => $failureDetails->failureCode,
                'decline_code' => $failureDetails->declineCode,
            ],
        );

        $this->emailDispatcher->dispatchOnce(
            $subscription,
            1 === $attemptNumber
                ? SubscriptionEmailType::PAYMENT_FAILED_FIRST_ATTEMPT
                : SubscriptionEmailType::PAYMENT_RETRY_FAILED,
            \sprintf('%s-attempt-%d', $invoice->id, $attemptNumber),
            [
                'plan' => $subscription->getPlan()->getCode(),
                'attempt' => $attemptNumber,
                'max_attempts' => self::MAX_ATTEMPTS,
                'next_retry_at' => $nextRetryAt?->format(\DATE_ATOM),
                'recovery_deadline' => $deadline->format(\DATE_ATOM),
            ],
        );

        $this->logger->warning('[PAYMENT RETRY] Subscription payment failure registered.', [
            'subscription' => $subscription->getId(),
            'agency' => $subscription->getAgency()->getId(),
            'invoice' => $invoice->id,
            'payment_intent' => $invoice->paymentIntentId,
            'attempt' => $attemptNumber,
            'status' => 'FAILED',
            'next_retry' => $nextRetryAt?->format(\DATE_ATOM),
        ]);

        $this->entityManager->flush();
    }

    private function recoverPaidInvoice(
        AgencySubscription $subscription,
        StripeInvoice $stripeInvoice,
        StripeSubscription $stripeSubscription,
        ?int $attemptNumber = null,
    ): void {
        $this->paymentService->recordPaidInvoice(
            $subscription,
            $stripeInvoice,
            $stripeSubscription,
            $attemptNumber,
            SubscriptionHistoryEventType::PAYMENT_RECOVERED,
        );

        $this->emailDispatcher->dispatchOnce(
            $subscription,
            SubscriptionEmailType::PAYMENT_RECOVERED,
            'payment-recovered-'.$this->stripeInvoiceService->snapshot($stripeInvoice)->id,
            [
                'plan' => $subscription->getPlan()->getCode(),
                'current_period_end' => $subscription->getCurrentPeriodEnd()?->format(\DATE_ATOM),
            ],
        );
    }

    private function observeStripeManagedRetry(
        AgencySubscription $subscription,
        StripeInvoice $stripeInvoice,
        \DateTimeImmutable $now,
    ): void {
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);
        $nextRetryAt = $invoice->nextPaymentAttemptAt ?? $subscription->getNextPaymentRetryAt();

        $subscription
            ->setProviderLatestInvoiceId($invoice->id)
            ->setPaymentFailureCount(max($subscription->getPaymentFailureCount(), min($invoice->attemptCount, self::MAX_ATTEMPTS)))
            ->setLastPaymentFailureAt($subscription->getLastPaymentFailureAt() ?? $now)
            ->setNextPaymentRetryAt($nextRetryAt)
            ->setLastStripeSyncAt($now);

        $this->logger->info('[PAYMENT RETRY] Stripe-managed retry observed, no local payment triggered.', [
            'subscription' => $subscription->getId(),
            'agency' => $subscription->getAgency()->getId(),
            'invoice' => $invoice->id,
            'attempt_count' => $invoice->attemptCount,
            'next_retry' => $nextRetryAt?->format(\DATE_ATOM),
        ]);

        $this->entityManager->flush();
    }

    /**
     * @return array{0: StripeSubscription, 1: StripeInvoice}
     */
    private function retrieveStripeState(AgencySubscription $subscription): array
    {
        $subscriptionId = $subscription->getProviderSubscriptionId();

        if (!\is_string($subscriptionId) || !str_starts_with($subscriptionId, 'sub_')) {
            throw new \LogicException('Identifiant Subscription Stripe manquant.');
        }

        $stripeSubscription = $this->stripeSubscriptionService->retrieve($subscriptionId);
        $invoiceId = $subscription->getProviderLatestInvoiceId();

        if (!\is_string($invoiceId) || '' === $invoiceId) {
            $invoiceId = $this->stripeSubscriptionService->snapshot($stripeSubscription)->latestInvoiceId;
        }

        if (!\is_string($invoiceId) || !str_starts_with($invoiceId, 'in_')) {
            throw new \LogicException('Identifiant Invoice Stripe manquant pour la relance.');
        }

        return [$stripeSubscription, $this->stripeInvoiceService->retrieve($invoiceId)];
    }

    private function fetchPaymentIntentQuietly(?string $paymentIntentId): ?PaymentIntent
    {
        try {
            return $this->stripePaymentService->retrievePaymentIntent($paymentIntentId);
        } catch (ApiErrorException $exception) {
            return null;
        }
    }

    private function retrieveInvoiceAfterPaymentFailure(string $invoiceId): StripeInvoice
    {
        try {
            return $this->stripeInvoiceService->retrieve($invoiceId);
        } catch (ApiErrorException $exception) {
            if ($this->stripePaymentService->isTemporaryStripeFailure($exception)) {
                throw new StripeTemporaryUnavailableException($exception->getMessage(), previous: $exception);
            }

            throw $exception;
        }
    }

    private function logTemporaryStripeFailure(
        AgencySubscription $subscription,
        ApiErrorException $exception,
        string $action,
    ): void {
        $this->logger->error('[SUBSCRIPTION CRON] Temporary Stripe error, no payment failure counted.', [
            'subscription' => $subscription->getId(),
            'agency' => $subscription->getAgency()->getId(),
            'stripe_subscription' => $subscription->getProviderSubscriptionId(),
            'action' => $action,
            'stripe_code' => $exception->getStripeCode(),
            'http_status' => $exception->getHttpStatus(),
            'message' => $exception->getMessage(),
        ]);
    }
}
