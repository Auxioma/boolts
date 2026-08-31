<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\DowngradeReason;
use App\Entity\Billing\Enum\SubscriptionEmailType;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Service\Stripe\StripeInvoiceService;
use App\Service\Stripe\StripePaymentService;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;

final readonly class SubscriptionCancellationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AgencySubscriptionRepository $subscriptionRepository,
        private StripeSubscriptionService $stripeSubscriptionService,
        private StripeInvoiceService $stripeInvoiceService,
        private StripePaymentService $stripePaymentService,
        private SubscriptionSynchronizationService $synchronizationService,
        private SubscriptionDowngradeService $downgradeService,
        private SubscriptionHistoryRecorder $historyRecorder,
        private SubscriptionEmailDispatcher $emailDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function requestCancellation(User $agency): AgencySubscription
    {
        $subscription = $this->subscriptionRepository->findOneActivePaidForAgency($agency);

        if (!$subscription instanceof AgencySubscription) {
            throw new \LogicException('Aucun abonnement payant actif n’est disponible pour cette agence.');
        }

        $this->releasePendingPlanChange($subscription);

        $stripeSubscription = $this->stripeSubscriptionService->scheduleCancellationAtPeriodEnd($subscription);
        $snapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);
        $now = new \DateTimeImmutable();
        $oldStatus = $subscription->getStatus();

        $subscription
            ->setStatus(SubscriptionStatus::CANCEL_SCHEDULED)
            ->setCancelAtPeriodEnd(true)
            ->setCancelRequestedAt($subscription->getCancelRequestedAt() ?? $now)
            ->setCanceledAt($snapshot->canceledAt)
            ->setProviderLatestInvoiceId($snapshot->latestInvoiceId)
            ->setLastStripeSyncAt($now);

        if ($snapshot->currentPeriodStart instanceof \DateTimeImmutable) {
            $subscription->setCurrentPeriodStart($snapshot->currentPeriodStart);
        }

        if ($snapshot->currentPeriodEnd instanceof \DateTimeImmutable) {
            $subscription->setCurrentPeriodEnd($snapshot->currentPeriodEnd);
        }

        $this->historyRecorder->record(
            subscription: $subscription,
            eventType: SubscriptionHistoryEventType::CANCEL_REQUESTED,
            oldStatus: $oldStatus,
            newStatus: SubscriptionStatus::CANCEL_SCHEDULED,
            oldPlan: $subscription->getPlan()->getCode(),
            newPlan: $subscription->getPlan()->getCode(),
            providerInvoiceId: $snapshot->latestInvoiceId,
            metadata: [
                'current_period_end' => $subscription->getCurrentPeriodEnd()?->format(\DATE_ATOM),
            ],
        );

        $this->emailDispatcher->dispatchOnce(
            $subscription,
            SubscriptionEmailType::SUBSCRIPTION_CANCEL_REQUESTED,
            'cancel-requested-'.$subscription->getProviderSubscriptionId(),
            [
                'plan' => $subscription->getPlan()->getCode(),
                'current_period_end' => $subscription->getCurrentPeriodEnd()?->format(\DATE_ATOM),
            ],
        );

        $this->entityManager->flush();

        return $subscription;
    }

    public function revokeCancellation(User $agency): AgencySubscription
    {
        $subscription = $this->subscriptionRepository->findOneActivePaidForAgency($agency);

        if (!$subscription instanceof AgencySubscription || !$subscription->getCancelAtPeriodEnd()) {
            throw new \LogicException('Aucune résiliation programmée n’est disponible pour cette agence.');
        }

        $stripeSubscription = $this->stripeSubscriptionService->reactivateBeforePeriodEnd($subscription);
        $snapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);
        $oldStatus = $subscription->getStatus();

        $subscription
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setCancelAtPeriodEnd(false)
            ->setCancelRequestedAt(null)
            ->setCanceledAt(null)
            ->setProviderLatestInvoiceId($snapshot->latestInvoiceId)
            ->setLastStripeSyncAt(new \DateTimeImmutable());

        $this->historyRecorder->record(
            subscription: $subscription,
            eventType: SubscriptionHistoryEventType::CANCEL_REVOKED,
            oldStatus: $oldStatus,
            newStatus: SubscriptionStatus::ACTIVE,
            oldPlan: $subscription->getPlan()->getCode(),
            newPlan: $subscription->getPlan()->getCode(),
            providerInvoiceId: $snapshot->latestInvoiceId,
        );

        $this->entityManager->flush();

        return $subscription;
    }

    public function finalizeCancellation(
        AgencySubscription $subscription,
        ?\DateTimeImmutable $now = null,
    ): void {
        $now ??= new \DateTimeImmutable();
        $subscriptionId = $subscription->getProviderSubscriptionId();

        if (!\is_string($subscriptionId) || !str_starts_with($subscriptionId, 'sub_')) {
            return;
        }

        try {
            $stripeSubscription = $this->stripeSubscriptionService->retrieve($subscriptionId);
            $snapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);
            $latestInvoice = $this->stripeInvoiceService->latestInvoiceFromSubscription($stripeSubscription);

            if (null !== $latestInvoice) {
                $invoice = $this->stripeInvoiceService->snapshot($latestInvoice);

                if (
                    $invoice->isPaid()
                    && $invoice->billingPeriodStart instanceof \DateTimeImmutable
                    && $subscription->getCurrentPeriodEnd() instanceof \DateTimeImmutable
                    && $invoice->billingPeriodStart >= $subscription->getCurrentPeriodEnd()
                    && !$snapshot->cancelAtPeriodEnd
                ) {
                    $this->synchronizationService->synchronizeFromStripe($subscription, $stripeSubscription, $latestInvoice);

                    return;
                }
            }

            if (
                'canceled' !== $snapshot->status
                && $snapshot->cancelAtPeriodEnd
                && $snapshot->currentPeriodEnd instanceof \DateTimeImmutable
                && $snapshot->currentPeriodEnd > $now
            ) {
                $this->synchronizationService->synchronizeSubscriptionFields($subscription, $stripeSubscription);
                $this->entityManager->flush();

                return;
            }

            if ('canceled' !== $snapshot->status && !$snapshot->cancelAtPeriodEnd) {
                $this->synchronizationService->synchronizeSubscriptionFields($subscription, $stripeSubscription);
                $this->entityManager->flush();

                return;
            }

            $this->historyRecorder->record(
                subscription: $subscription,
                eventType: SubscriptionHistoryEventType::SUBSCRIPTION_ENDED,
                oldStatus: $subscription->getStatus(),
                newStatus: SubscriptionStatus::CANCELED,
                oldPlan: $subscription->getPlan()->getCode(),
                newPlan: 'free',
                providerInvoiceId: $snapshot->latestInvoiceId,
                metadata: [
                    'stripe_status' => $snapshot->status,
                    'period_end' => $snapshot->currentPeriodEnd?->format(\DATE_ATOM),
                ],
            );

            $this->entityManager->flush();
            $this->downgradeService->downgradeToFree($subscription, DowngradeReason::CANCEL_AT_PERIOD_END, $now);
        } catch (ApiErrorException $exception) {
            if ($this->stripePaymentService->isTemporaryStripeFailure($exception)) {
                $this->logger->error('[SUBSCRIPTION CRON] Temporary Stripe error while finalizing cancellation.', [
                    'subscription' => $subscription->getId(),
                    'agency' => $subscription->getAgency()->getId(),
                    'stripe_subscription' => $subscriptionId,
                    'stripe_code' => $exception->getStripeCode(),
                    'http_status' => $exception->getHttpStatus(),
                    'message' => $exception->getMessage(),
                ]);

                return;
            }

            throw $exception;
        }
    }

    /**
     * A cancellation and a scheduled paid-to-paid downgrade cannot both be armed on
     * the same Stripe subscription: releasing the schedule first hands billing control
     * back to the subscription so it can be cancelled at period end.
     */
    private function releasePendingPlanChange(AgencySubscription $subscription): void
    {
        if (!$subscription->hasPendingPlanChange()) {
            return;
        }

        $scheduleId = $subscription->getProviderScheduleId();

        if (\is_string($scheduleId) && '' !== $scheduleId) {
            $this->stripeSubscriptionService->releaseSchedule($scheduleId);
        }

        $subscription->clearPendingPlanChange();

        $this->historyRecorder->record(
            subscription: $subscription,
            eventType: SubscriptionHistoryEventType::PLAN_CHANGE_CANCELED,
            oldStatus: $subscription->getStatus(),
            newStatus: $subscription->getStatus(),
            oldPlan: $subscription->getPlan()->getCode(),
            newPlan: $subscription->getPlan()->getCode(),
            metadata: ['reason' => 'superseded_by_cancellation'],
        );
    }
}
