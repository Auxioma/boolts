<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Dto\Subscription\PaymentFailureDetails;
use App\Dto\Subscription\StripeInvoiceSnapshot;
use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\InvoiceStatus;
use App\Entity\Billing\Enum\InvoiceType;
use App\Entity\Billing\Enum\PaymentAttemptStatus;
use App\Entity\Billing\Enum\PaymentStatus;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentAttempt;
use App\Entity\Booster\BoosterTransaction;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use App\Repository\Billing\AgencySubscriptionPeriodRepository;
use App\Repository\Billing\InvoiceRepository;
use App\Repository\Billing\PaymentAttemptRepository;
use App\Repository\Billing\PaymentRepository;
use App\Repository\Booster\BoosterTransactionRepository;
use App\Service\Stripe\StripeInvoiceService;
use App\Service\Stripe\StripePaymentService;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice as StripeInvoice;
use Stripe\PaymentIntent;
use Stripe\Subscription as StripeSubscription;

final readonly class SubscriptionPaymentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentRepository $paymentRepository,
        private PaymentAttemptRepository $paymentAttemptRepository,
        private AgencyPaymentMethodRepository $paymentMethodRepository,
        private AgencySubscriptionPeriodRepository $periodRepository,
        private InvoiceRepository $invoiceRepository,
        private BoosterTransactionRepository $boosterTransactionRepository,
        private StripeInvoiceService $stripeInvoiceService,
        private StripePaymentService $stripePaymentService,
        private StripeSubscriptionService $stripeSubscriptionService,
        private SubscriptionHistoryRecorder $historyRecorder,
        private LoggerInterface $logger,
    ) {
    }

    public function recordPaidInvoice(
        AgencySubscription $subscription,
        StripeInvoice $stripeInvoice,
        StripeSubscription $stripeSubscription,
        ?int $attemptNumber = null,
        SubscriptionHistoryEventType $eventType = SubscriptionHistoryEventType::RENEWAL_SUCCEEDED,
    ): Payment {
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);
        $stripeSubscriptionSnapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);
        $attemptNumber ??= max(1, $invoice->attemptCount);

        return $this->entityManager->wrapInTransaction(function () use (
            $subscription,
            $invoice,
            $stripeSubscriptionSnapshot,
            $attemptNumber,
            $eventType,
        ): Payment {
            $payment = $this->paymentRepository->findOneByProviderInvoiceId($invoice->id);
            $wasAlreadySucceeded = $payment instanceof Payment
                && PaymentStatus::SUCCEEDED === $payment->getStatus();

            if (!$payment instanceof Payment) {
                $payment = $this->createPaymentSkeleton($subscription, $invoice);
                $this->entityManager->persist($payment);
            }

            $period = $this->upsertSubscriptionPeriod($subscription, $payment, $invoice, SubscriptionPeriodStatus::PAID);
            $this->upsertInvoice($subscription, $period, $payment, $invoice, InvoiceStatus::PAID);

            $payment
                ->setStatus(PaymentStatus::SUCCEEDED)
                ->setAmountSubtotalMinor($invoice->subtotalMinor)
                ->setAmountTotalMinor($invoice->amountTotalMinor)
                ->setAmountPaidMinor($invoice->amountPaidMinor)
                ->setAmountRefundedMinor(0)
                ->setProviderPaymentIntentId($invoice->paymentIntentId)
                ->setProviderChargeId($invoice->chargeId)
                ->setProviderInvoiceId($invoice->id)
                ->setBillingPeriodStart($period->getPeriodStart())
                ->setBillingPeriodEnd($period->getPeriodEnd())
                ->setAttemptNumber($attemptNumber)
                ->setFailureCode(null)
                ->setFailureMessage(null)
                ->setPaidAt($invoice->paidAt ?? new \DateTimeImmutable())
                ->setFailedAt(null)
                ->setMetadata([
                    ...$payment->getMetadata(),
                    'stripe_subscription_id' => $subscription->getProviderSubscriptionId(),
                    'stripe_invoice_status' => $invoice->status,
                ]);

            $paymentIntent = null;

            try {
                $paymentIntent = $this->stripePaymentService->retrievePaymentIntent($invoice->paymentIntentId);
            } catch (ApiErrorException $exception) {
                $this->logger->warning('[PAYMENT] PaymentIntent retrieval failed after paid invoice.', [
                    'subscription' => $subscription->getId(),
                    'invoice' => $invoice->id,
                    'payment_intent' => $invoice->paymentIntentId,
                    'stripe_code' => $exception->getStripeCode(),
                    'http_status' => $exception->getHttpStatus(),
                ]);
            }

            $paymentMethod = $this->resolvePaymentMethod($subscription, $paymentIntent);
            $payment->setPaymentMethod($paymentMethod);
            $payment->setPaymentMethodSnapshot($this->snapshotPaymentMethod($paymentMethod));

            if (!$this->paymentAttemptRepository->hasAttemptNumber($subscription, $invoice->id, $attemptNumber)) {
                $this->entityManager->persist(
                    $this->createPaymentAttempt(
                        $subscription,
                        $payment,
                        $invoice,
                        $paymentMethod,
                        $attemptNumber,
                        PaymentAttemptStatus::SUCCEEDED,
                        PaymentFailureDetails::empty(),
                    )
                );
            }

            $oldStatus = $subscription->getStatus();
            $oldPlan = $subscription->getPlan()->getCode();
            $currentPeriodStart = $stripeSubscriptionSnapshot->currentPeriodStart ?? $period->getPeriodStart();
            $currentPeriodEnd = $stripeSubscriptionSnapshot->currentPeriodEnd ?? $period->getPeriodEnd();
            $subscriptionStatus = $stripeSubscriptionSnapshot->cancelAtPeriodEnd
                ? SubscriptionStatus::CANCEL_SCHEDULED
                : SubscriptionStatus::ACTIVE;

            $subscription
                ->setStatus($subscriptionStatus)
                ->setCurrentPeriodStart($currentPeriodStart)
                ->setCurrentPeriodEnd($currentPeriodEnd)
                ->setCancelAtPeriodEnd($stripeSubscriptionSnapshot->cancelAtPeriodEnd)
                ->setCanceledAt($stripeSubscriptionSnapshot->canceledAt)
                ->setProviderCustomerId($stripeSubscriptionSnapshot->customerId)
                ->setProviderSubscriptionItemId($stripeSubscriptionSnapshot->subscriptionItemId)
                ->setProviderPriceId($stripeSubscriptionSnapshot->priceId)
                ->setProviderProductId($stripeSubscriptionSnapshot->productId)
                ->setProviderLatestInvoiceId($invoice->id)
                ->setPaymentFailureCount(0)
                ->setFirstPaymentFailureAt(null)
                ->setLastPaymentFailureAt(null)
                ->setNextPaymentRetryAt(null)
                ->setPaymentRecoveryDeadline(null)
                ->setLastSuccessfulPaymentAt($payment->getPaidAt())
                ->setLastStripeSyncAt(new \DateTimeImmutable());

            if (!$wasAlreadySucceeded) {
                $this->historyRecorder->record(
                    subscription: $subscription,
                    eventType: $eventType,
                    oldStatus: $oldStatus,
                    newStatus: $subscription->getStatus(),
                    oldPlan: $oldPlan,
                    newPlan: $subscription->getPlan()->getCode(),
                    providerInvoiceId: $invoice->id,
                    providerPaymentIntentId: $invoice->paymentIntentId,
                    metadata: [
                        'amount_paid_minor' => $invoice->amountPaidMinor,
                        'currency' => $invoice->currency,
                        'billing_period_start' => $period->getPeriodStart()->format(\DATE_ATOM),
                        'billing_period_end' => $period->getPeriodEnd()->format(\DATE_ATOM),
                    ],
                );

                $this->createIncludedBoostCredit($subscription, $period, $payment, $invoice);
            }

            $this->logger->info('[PAYMENT] Stripe subscription invoice paid.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'invoice' => $invoice->id,
                'payment_intent' => $invoice->paymentIntentId,
                'status' => 'PAID',
                'amount' => $invoice->amountPaidMinor,
                'currency' => $invoice->currency,
            ]);

            $this->entityManager->flush();

            return $payment;
        });
    }

    public function recordFailedInvoiceAttempt(
        AgencySubscription $subscription,
        StripeInvoice $stripeInvoice,
        ?PaymentIntent $paymentIntent,
        int $attemptNumber,
        PaymentFailureDetails $failureDetails,
    ): Payment {
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

        return $this->entityManager->wrapInTransaction(function () use (
            $subscription,
            $invoice,
            $paymentIntent,
            $attemptNumber,
            $failureDetails,
        ): Payment {
            $payment = $this->paymentRepository->findOneByProviderInvoiceId($invoice->id);

            if (!$payment instanceof Payment) {
                $payment = $this->createPaymentSkeleton($subscription, $invoice);
                $this->entityManager->persist($payment);
            }

            $paymentMethod = $this->resolvePaymentMethod($subscription, $paymentIntent);
            $period = $this->upsertSubscriptionPeriod($subscription, $payment, $invoice, SubscriptionPeriodStatus::FAILED);
            $this->upsertInvoice($subscription, $period, $payment, $invoice, $this->invoiceStatusFromStripe($invoice));

            $payment
                ->setStatus(PaymentStatus::FAILED)
                ->setAmountSubtotalMinor($invoice->subtotalMinor)
                ->setAmountTotalMinor($invoice->amountTotalMinor)
                ->setAmountPaidMinor($invoice->amountPaidMinor)
                ->setProviderPaymentIntentId($invoice->paymentIntentId)
                ->setProviderChargeId($invoice->chargeId)
                ->setProviderInvoiceId($invoice->id)
                ->setBillingPeriodStart($period->getPeriodStart())
                ->setBillingPeriodEnd($period->getPeriodEnd())
                ->setAttemptNumber($attemptNumber)
                ->setPaymentMethod($paymentMethod)
                ->setPaymentMethodSnapshot($this->snapshotPaymentMethod($paymentMethod))
                ->setFailureCode($failureDetails->failureCode ?? $failureDetails->declineCode)
                ->setFailureMessage($failureDetails->failureMessage)
                ->setFailedAt(new \DateTimeImmutable())
                ->setMetadata([
                    ...$payment->getMetadata(),
                    'stripe_subscription_id' => $subscription->getProviderSubscriptionId(),
                    'stripe_invoice_status' => $invoice->status,
                ]);

            if (!$this->paymentAttemptRepository->hasAttemptNumber($subscription, $invoice->id, $attemptNumber)) {
                $this->entityManager->persist(
                    $this->createPaymentAttempt(
                        $subscription,
                        $payment,
                        $invoice,
                        $paymentMethod,
                        $attemptNumber,
                        PaymentAttemptStatus::FAILED,
                        $failureDetails,
                    )
                );
            }

            $this->logger->warning('[PAYMENT RETRY] Stripe subscription invoice attempt failed.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'invoice' => $invoice->id,
                'payment_intent' => $invoice->paymentIntentId,
                'attempt' => $attemptNumber,
                'status' => 'FAILED',
                'failure_code' => $failureDetails->failureCode,
                'decline_code' => $failureDetails->declineCode,
            ]);

            $this->entityManager->flush();

            return $payment;
        });
    }

    private function createPaymentSkeleton(
        AgencySubscription $subscription,
        StripeInvoiceSnapshot $invoice,
    ): Payment {
        $billingProfile = $subscription->getAgency()->getBillingProfile();

        if (!$billingProfile instanceof AgencyBillingProfile) {
            throw new \LogicException('Profil de facturation manquant pour enregistrer le paiement.');
        }

        $currency = $subscription->getCurrencySnapshot()
            ?? $subscription->getPlanPrice()?->getCurrency();

        if (null === $currency) {
            throw new \LogicException('Devise manquante pour enregistrer le paiement.');
        }

        return (new Payment())
            ->setReference('SUB-'.$invoice->id)
            ->setAgency($subscription->getAgency())
            ->setBillingProfile($billingProfile)
            ->setSubscription($subscription)
            ->setType(PaymentType::SUBSCRIPTION_RENEWAL)
            ->setStatus(PaymentStatus::PENDING)
            ->setCurrency($currency)
            ->setProviderInvoiceId($invoice->id)
            ->setMetadata([
                'source' => 'stripe_invoice',
                'stripe_invoice_id' => $invoice->id,
            ]);
    }

    private function upsertSubscriptionPeriod(
        AgencySubscription $subscription,
        Payment $payment,
        StripeInvoiceSnapshot $invoice,
        SubscriptionPeriodStatus $status,
    ): AgencySubscriptionPeriod {
        $periodStart = $invoice->billingPeriodStart ?? $subscription->getCurrentPeriodStart();
        $periodEnd = $invoice->billingPeriodEnd ?? $subscription->getCurrentPeriodEnd();

        if (!$periodStart instanceof \DateTimeImmutable || !$periodEnd instanceof \DateTimeImmutable) {
            throw new \LogicException('Stripe n’a pas fourni de période facturée exploitable.');
        }

        $period = $this->periodRepository->findOneByProviderInvoiceId($invoice->id);

        if (!$period instanceof AgencySubscriptionPeriod) {
            $period = $this->periodRepository->findOneForPeriod($subscription, $periodStart, $periodEnd);
        }

        if (!$period instanceof AgencySubscriptionPeriod) {
            $currency = $subscription->getCurrencySnapshot()
                ?? $subscription->getPlanPrice()?->getCurrency();

            if (null === $currency) {
                throw new \LogicException('Devise manquante pour enregistrer la période d’abonnement.');
            }

            $period = (new AgencySubscriptionPeriod())
                ->setSubscription($subscription)
                ->setPeriodStart($periodStart)
                ->setPeriodEnd($periodEnd)
                ->setPropertyLimit($subscription->getPropertyLimitSnapshot())
                ->setIncludedBoosts($subscription->getIncludedBoostsSnapshot())
                ->setAmountMinor($invoice->amountTotalMinor)
                ->setCurrency($currency)
                ->setProviderInvoiceId($invoice->id);

            $this->entityManager->persist($period);
        }

        $period
            ->setPayment($payment)
            ->setStatus($status)
            ->setAmountMinor($invoice->amountTotalMinor)
            ->setProviderInvoiceId($invoice->id);

        return $period;
    }

    private function upsertInvoice(
        AgencySubscription $subscription,
        AgencySubscriptionPeriod $period,
        Payment $payment,
        StripeInvoiceSnapshot $invoice,
        InvoiceStatus $status,
    ): Invoice {
        $localInvoice = $this->invoiceRepository->findOneBy(['providerInvoiceId' => $invoice->id]);

        if (!$localInvoice instanceof Invoice) {
            $billingProfile = $subscription->getAgency()->getBillingProfile();

            if (!$billingProfile instanceof AgencyBillingProfile) {
                throw new \LogicException('Profil de facturation manquant pour enregistrer la facture.');
            }

            $localInvoice = (new Invoice())
                ->setNumber($invoice->number)
                ->setAgency($subscription->getAgency())
                ->setBillingProfile($billingProfile)
                ->setSubscription($subscription)
                ->setSubscriptionPeriod($period)
                ->setType(InvoiceType::SUBSCRIPTION)
                ->setCurrency($payment->getCurrency())
                ->setSellerSnapshot(['name' => 'Boolts'])
                ->setCustomerSnapshot([
                    'email' => $subscription->getAgency()->getEmail(),
                    'agency_id' => $subscription->getAgency()->getId(),
                ])
                ->setTaxSnapshot([])
                ->setProviderInvoiceId($invoice->id);

            $this->entityManager->persist($localInvoice);
        }

        $localInvoice
            ->setPayment($payment)
            ->setStatus($status)
            ->setSubtotalMinor($invoice->subtotalMinor)
            ->setTaxableTotalMinor($invoice->subtotalMinor)
            ->setTaxTotalMinor(max(0, $invoice->amountTotalMinor - $invoice->subtotalMinor))
            ->setTotalMinor($invoice->amountTotalMinor)
            ->setAmountPaidMinor($invoice->amountPaidMinor)
            ->setAmountDueMinor($invoice->amountDueMinor)
            ->setProviderHostedInvoiceUrl($invoice->hostedInvoiceUrl)
            ->setProviderInvoicePdfUrl($invoice->invoicePdfUrl)
            ->setIssuedAt($invoice->createdAt)
            ->setPaidAt($invoice->paidAt);

        return $localInvoice;
    }

    private function createPaymentAttempt(
        AgencySubscription $subscription,
        Payment $payment,
        StripeInvoiceSnapshot $invoice,
        ?AgencyPaymentMethod $paymentMethod,
        int $attemptNumber,
        PaymentAttemptStatus $status,
        PaymentFailureDetails $failureDetails,
    ): PaymentAttempt {
        return (new PaymentAttempt())
            ->setPayment($payment)
            ->setSubscription($subscription)
            ->setPaymentMethod($paymentMethod)
            ->setAttemptNumber($attemptNumber)
            ->setStatus($status)
            ->setProviderInvoiceId($invoice->id)
            ->setProviderPaymentIntentId($invoice->paymentIntentId)
            ->setProviderChargeId($invoice->chargeId)
            ->setAmountMinor($invoice->amountTotalMinor)
            ->setCurrency($payment->getCurrency())
            ->setRequiresActionType($failureDetails->requiresActionType)
            ->setDeclineCode($failureDetails->declineCode)
            ->setFailureCode($failureDetails->failureCode)
            ->setFailureMessage($failureDetails->failureMessage)
            ->setAttemptedAt(new \DateTimeImmutable())
            ->setCompletedAt(new \DateTimeImmutable());
    }

    private function resolvePaymentMethod(
        AgencySubscription $subscription,
        ?PaymentIntent $paymentIntent,
    ): ?AgencyPaymentMethod {
        $paymentMethodId = null;

        if ($paymentIntent instanceof PaymentIntent) {
            $paymentMethod = $paymentIntent->payment_method ?? null;
            $paymentMethodId = \is_string($paymentMethod)
                ? $paymentMethod
                : (\is_object($paymentMethod) && isset($paymentMethod->id) && \is_string($paymentMethod->id)
                    ? $paymentMethod->id
                    : null);
        }

        if (null !== $paymentMethodId) {
            $paymentMethod = $this->paymentMethodRepository->findOneByStripePaymentMethodId($paymentMethodId);

            if ($paymentMethod instanceof AgencyPaymentMethod) {
                return $paymentMethod;
            }
        }

        return $subscription->getAgency()->getBillingProfile()?->getDefaultPaymentMethod();
    }

    /**
     * @return array<string, scalar|null>
     */
    private function snapshotPaymentMethod(?AgencyPaymentMethod $paymentMethod): array
    {
        if (!$paymentMethod instanceof AgencyPaymentMethod) {
            return [];
        }

        return [
            'brand' => $paymentMethod->getBrand(),
            'last4' => $paymentMethod->getLast4(),
            'exp_month' => $paymentMethod->getExpMonth(),
            'exp_year' => $paymentMethod->getExpYear(),
            'stripe_payment_method_id' => $paymentMethod->getStripePaymentMethodId(),
        ];
    }

    private function invoiceStatusFromStripe(StripeInvoiceSnapshot $invoice): InvoiceStatus
    {
        return match ($invoice->status) {
            'paid' => InvoiceStatus::PAID,
            'void' => InvoiceStatus::VOID,
            'uncollectible' => InvoiceStatus::UNCOLLECTIBLE,
            'open' => InvoiceStatus::OPEN,
            default => InvoiceStatus::DRAFT,
        };
    }

    private function createIncludedBoostCredit(
        AgencySubscription $subscription,
        AgencySubscriptionPeriod $period,
        Payment $payment,
        StripeInvoiceSnapshot $invoice,
    ): void {
        if ($period->getIncludedBoosts() <= 0) {
            return;
        }

        $idempotencyKey = \sprintf(
            'subscription-credit-%d-%s',
            (int) $subscription->getId(),
            $invoice->id,
        );

        if ($this->boosterTransactionRepository->findOneByIdempotencyKey($idempotencyKey) instanceof BoosterTransaction) {
            return;
        }

        $transaction = (new BoosterTransaction())
            ->setAgency($subscription->getAgency())
            ->setQuantity($period->getIncludedBoosts())
            ->setType(BoosterTransactionType::SUBSCRIPTION_CREDIT)
            ->setSubscriptionPeriod($period)
            ->setPayment($payment)
            ->setExpiresAt($period->getPeriodEnd())
            ->setIdempotencyKey($idempotencyKey)
            ->setDescription('Boosts inclus dans l’abonnement.');

        $this->entityManager->persist($transaction);
    }
}
