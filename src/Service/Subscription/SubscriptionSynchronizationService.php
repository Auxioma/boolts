<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Repository\Billing\AgencySubscriptionPeriodRepository;
use App\Repository\Billing\SubscriptionPlanPriceRepository;
use App\Service\Stripe\StripeInvoiceService;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Invoice as StripeInvoice;
use Stripe\Subscription as StripeSubscription;

final readonly class SubscriptionSynchronizationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AgencySubscriptionPeriodRepository $periodRepository,
        private SubscriptionPlanPriceRepository $planPriceRepository,
        private StripeSubscriptionService $stripeSubscriptionService,
        private StripeInvoiceService $stripeInvoiceService,
        private SubscriptionPaymentService $paymentService,
        private SubscriptionHistoryRecorder $historyRecorder,
        private LoggerInterface $logger,
    ) {
    }

    public function synchronizeFromStripe(
        AgencySubscription $subscription,
        StripeSubscription $stripeSubscription,
        ?StripeInvoice $stripeInvoice = null,
        ?PaymentType $paymentType = null,
    ): void {
        $stripeSnapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);
        $currentPriceId = $subscription->getProviderPriceId()
            ?? $subscription->getPlanPrice()?->getPaymentProviderPriceId();

        if (
            null === $paymentType
            && null !== $currentPriceId
            && null !== $stripeSnapshot->priceId
            && $currentPriceId !== $stripeSnapshot->priceId
        ) {
            $paymentType = PaymentType::SUBSCRIPTION_UPGRADE;
        }

        $stripeInvoice ??= $this->stripeInvoiceService->latestInvoiceFromSubscription($stripeSubscription);

        if ($stripeInvoice instanceof StripeInvoice) {
            $invoiceSnapshot = $this->stripeInvoiceService->snapshot($stripeInvoice);

            if ($invoiceSnapshot->isPaid()) {
                $eventType = $subscription->getStatus()->isRecoverableFailure()
                    ? SubscriptionHistoryEventType::PAYMENT_RECOVERED
                    : SubscriptionHistoryEventType::RENEWAL_SUCCEEDED;

                $this->synchronizeSubscriptionFields($subscription, $stripeSubscription);
                $this->paymentService->recordPaidInvoice(
                    $subscription,
                    $stripeInvoice,
                    $stripeSubscription,
                    null,
                    $eventType,
                    $paymentType,
                );

                return;
            }
        }

        $this->synchronizeSubscriptionFields($subscription, $stripeSubscription);
        $this->entityManager->flush();
    }

    public function synchronizeSubscriptionFields(
        AgencySubscription $subscription,
        StripeSubscription $stripeSubscription,
    ): void {
        $snapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);
        $oldStatus = $subscription->getStatus();
        $oldPlan = $subscription->getPlan()->getCode();
        $oldPriceId = $subscription->getProviderPriceId()
            ?? $subscription->getPlanPrice()?->getPaymentProviderPriceId();

        $status = $this->mapStripeStatus($snapshot->status, $snapshot->cancelAtPeriodEnd);

        $subscription
            ->setProviderCustomerId($snapshot->customerId)
            ->setProviderSubscriptionItemId($snapshot->subscriptionItemId)
            ->setProviderPriceId($snapshot->priceId)
            ->setProviderProductId($snapshot->productId)
            ->setProviderLatestInvoiceId($snapshot->latestInvoiceId)
            ->setCancelAtPeriodEnd($snapshot->cancelAtPeriodEnd)
            ->setCanceledAt($snapshot->canceledAt)
            ->setEndedAt($snapshot->endedAt ?? $subscription->getEndedAt())
            ->setLastStripeSyncAt(new \DateTimeImmutable());

        if ($snapshot->currentPeriodStart instanceof \DateTimeImmutable) {
            $subscription->setCurrentPeriodStart($snapshot->currentPeriodStart);
        }

        if ($snapshot->currentPeriodEnd instanceof \DateTimeImmutable) {
            $subscription->setCurrentPeriodEnd($snapshot->currentPeriodEnd);
        }

        if (null !== $snapshot->priceId) {
            if (
                null !== $oldPriceId
                && $oldPriceId !== $snapshot->priceId
                && $snapshot->currentPeriodStart instanceof \DateTimeImmutable
            ) {
                $this->deactivatePreviousPeriod($subscription, $snapshot->currentPeriodStart);
            }

            $this->syncLocalPlanPrice($subscription, $snapshot->priceId);
        }

        $this->resolvePendingPlanChange($subscription, $snapshot->priceId);

        $newPlan = $subscription->getPlan()->getCode();

        if ($oldStatus !== $status || $oldPlan !== $newPlan) {
            $subscription->setStatus($status);

            $this->historyRecorder->record(
                subscription: $subscription,
                eventType: SubscriptionHistoryEventType::STRIPE_SYNCHRONIZED,
                oldStatus: $oldStatus,
                newStatus: $status,
                oldPlan: $oldPlan,
                newPlan: $newPlan,
                providerInvoiceId: $snapshot->latestInvoiceId,
                metadata: [
                    'stripe_status' => $snapshot->status,
                    'cancel_at_period_end' => $snapshot->cancelAtPeriodEnd,
                ],
            );
        }

        $this->logger->info('[SUBSCRIPTION CRON] Stripe subscription synchronized.', [
            'subscription' => $subscription->getId(),
            'agency' => $subscription->getAgency()->getId(),
            'stripe_subscription' => $snapshot->id,
            'stripe_status' => $snapshot->status,
            'local_status' => $subscription->getStatus()->value,
            'latest_invoice' => $snapshot->latestInvoiceId,
        ]);
    }

    /**
     * Clears a scheduled paid-to-paid downgrade once Stripe reports the target
     * price as the subscription's active price, i.e. the schedule's second phase
     * has started and been billed. The plan/limit swap itself is already done by
     * {@see syncLocalPlanPrice()}; here we release the now-consumed schedule so the
     * subscription is managed standalone again.
     */
    private function resolvePendingPlanChange(
        AgencySubscription $subscription,
        ?string $activePriceId,
    ): void {
        if (null === $activePriceId || !$subscription->hasPendingPlanChange()) {
            return;
        }

        $pendingPlanPrice = $subscription->getPendingPlanPrice();

        if (null === $pendingPlanPrice || $pendingPlanPrice->getPaymentProviderPriceId() !== $activePriceId) {
            return;
        }

        $scheduleId = $subscription->getProviderScheduleId();

        if (\is_string($scheduleId) && '' !== $scheduleId) {
            $this->stripeSubscriptionService->releaseSchedule($scheduleId);
        }

        $this->historyRecorder->record(
            subscription: $subscription,
            eventType: SubscriptionHistoryEventType::PLAN_CHANGE_APPLIED,
            oldStatus: $subscription->getStatus(),
            newStatus: $subscription->getStatus(),
            oldPlan: $subscription->getPlan()->getCode(),
            newPlan: $pendingPlanPrice->getPlan()->getCode(),
            metadata: [
                'effective_at' => $subscription->getPendingPlanChangeEffectiveAt()?->format(\DATE_ATOM),
                'schedule_id' => $scheduleId,
            ],
        );

        $subscription->clearPendingPlanChange();
    }

    private function syncLocalPlanPrice(
        AgencySubscription $subscription,
        string $priceId,
    ): void {
        $planPrice = $this->planPriceRepository->findOneByPaymentProviderPriceId($priceId);

        if (!$planPrice instanceof SubscriptionPlanPrice) {
            return;
        }

        $subscription
            ->setPlanPrice($planPrice)
            ->setPlan($planPrice->getPlan())
            ->setPropertyLimitSnapshot($planPrice->getPlan()->getPropertyLimit())
            ->setIncludedBoostsSnapshot($planPrice->getPlan()->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($planPrice->getPlan()->getBoostDurationDays())
            ->setAmountSnapshotMinor($planPrice->getAmountMinor())
            ->setCurrencySnapshot($planPrice->getCurrency());
    }

    private function deactivatePreviousPeriod(
        AgencySubscription $subscription,
        \DateTimeImmutable $upgradedAt,
    ): void {
        $period = $this->periodRepository->findPaidContaining($subscription, $upgradedAt)
            ?? $this->periodRepository->findLatestPaidBefore($subscription, $upgradedAt);

        if (null === $period) {
            $this->logger->warning('[SUBSCRIPTION UPGRADE] Previous paid period not found.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'upgraded_at' => $upgradedAt->format(\DATE_ATOM),
            ]);

            return;
        }

        $period->setStatus(SubscriptionPeriodStatus::CANCELED);

        if ($upgradedAt > $period->getPeriodStart()) {
            $period->setPeriodEnd($upgradedAt);
        }
    }

    private function mapStripeStatus(
        string $stripeStatus,
        bool $cancelAtPeriodEnd,
    ): SubscriptionStatus {
        return match ($stripeStatus) {
            'active', 'trialing' => $cancelAtPeriodEnd
                ? SubscriptionStatus::CANCEL_SCHEDULED
                : SubscriptionStatus::ACTIVE,
            'past_due' => SubscriptionStatus::PAST_DUE,
            'unpaid' => SubscriptionStatus::PAYMENT_FAILED,
            'canceled' => SubscriptionStatus::CANCELED,
            'incomplete', 'incomplete_expired' => SubscriptionStatus::INCOMPLETE,
            default => SubscriptionStatus::PAST_DUE,
        };
    }
}
