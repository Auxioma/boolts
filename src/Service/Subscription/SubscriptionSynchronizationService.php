<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlanPrice;
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
    ): void {
        $stripeInvoice ??= $this->stripeInvoiceService->latestInvoiceFromSubscription($stripeSubscription);

        if ($stripeInvoice instanceof StripeInvoice) {
            $invoiceSnapshot = $this->stripeInvoiceService->snapshot($stripeInvoice);

            if ($invoiceSnapshot->isPaid()) {
                $eventType = $subscription->getStatus()->isRecoverableFailure()
                    ? SubscriptionHistoryEventType::PAYMENT_RECOVERED
                    : SubscriptionHistoryEventType::RENEWAL_SUCCEEDED;

                $this->paymentService->recordPaidInvoice(
                    $subscription,
                    $stripeInvoice,
                    $stripeSubscription,
                    null,
                    $eventType,
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
            $this->syncLocalPlanPrice($subscription, $snapshot->priceId);
        }

        if ($oldStatus !== $status) {
            $subscription->setStatus($status);

            $this->historyRecorder->record(
                subscription: $subscription,
                eventType: SubscriptionHistoryEventType::STRIPE_SYNCHRONIZED,
                oldStatus: $oldStatus,
                newStatus: $status,
                oldPlan: $oldPlan,
                newPlan: $subscription->getPlan()->getCode(),
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
