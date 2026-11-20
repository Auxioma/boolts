<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Service\Subscription;

use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Exception\PlanChangeException;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates a switch to a cheaper paid plan that only becomes effective, and is
 * billed by Stripe, at the end of the current paid period. The actual plan / limit
 * swap when the period rolls over is handled by the existing Stripe synchronisation
 * pipeline (webhook + cron); this service only arms the Stripe subscription schedule
 * and stores the pending change on the subscription for display and gating.
 */
final readonly class SubscriptionPlanChangeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StripeSubscriptionService $stripeSubscriptionService,
        private SubscriptionHistoryRecorder $historyRecorder,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws PlanChangeException                 when a business rule forbids the change
     * @throws \Stripe\Exception\ApiErrorException when Stripe rejects the schedule creation
     */
    public function scheduleDowngrade(
        AgencySubscription $subscription,
        SubscriptionPlanPrice $targetPlanPrice,
        AgencyPaymentMethod $paymentMethod,
    ): AgencySubscription {
        $currentPlanPrice = $subscription->getPlanPrice();

        if (!$currentPlanPrice instanceof SubscriptionPlanPrice) {
            throw new PlanChangeException('billing.plan_change.current_price_not_found');
        }

        if (!\in_array($subscription->getStatus(), [SubscriptionStatus::ACTIVE, SubscriptionStatus::CANCEL_SCHEDULED], true)) {
            throw new PlanChangeException('billing.plan_change.must_be_active');
        }

        if ($subscription->getCancelAtPeriodEnd()) {
            throw new PlanChangeException('billing.plan_change.cancel_scheduled');
        }

        if ($targetPlanPrice->getPlan()->isIsFree() || $targetPlanPrice->getAmountMinor() <= 0) {
            throw new PlanChangeException('billing.plan_change.use_cancellation');
        }

        if ($currentPlanPrice->getCurrency() !== $targetPlanPrice->getCurrency()) {
            throw new PlanChangeException('billing.plan_change.same_currency');
        }

        if ($targetPlanPrice->getAmountMinor() >= $currentPlanPrice->getAmountMinor()) {
            throw new PlanChangeException('billing.plan_change.upgrade_immediate');
        }

        if ($subscription->getPendingPlanPrice()?->getId() === $targetPlanPrice->getId()) {
            return $subscription;
        }

        $effectiveAt = $subscription->getCurrentPeriodEnd();

        if (!$effectiveAt instanceof \DateTimeImmutable) {
            throw new PlanChangeException('billing.plan_change.period_end_unknown');
        }

        return $this->entityManager->wrapInTransaction(function () use (
            $subscription,
            $currentPlanPrice,
            $targetPlanPrice,
            $paymentMethod,
            $effectiveAt,
        ): AgencySubscription {
            $schedule = $this->stripeSubscriptionService->scheduleDowngradeAtPeriodEnd(
                $subscription,
                $targetPlanPrice,
                $paymentMethod,
            );

            $now = new \DateTimeImmutable();

            $subscription
                ->setProviderScheduleId((string) $schedule->id)
                ->setPendingPlanPrice($targetPlanPrice)
                ->setPendingPlanChangeEffectiveAt($effectiveAt)
                ->setPendingPlanChangeRequestedAt($now)
                ->setLastStripeSyncAt($now);

            $this->historyRecorder->record(
                subscription: $subscription,
                eventType: SubscriptionHistoryEventType::PLAN_CHANGE_SCHEDULED,
                oldStatus: $subscription->getStatus(),
                newStatus: $subscription->getStatus(),
                oldPlan: $currentPlanPrice->getPlan()->getCode(),
                newPlan: $targetPlanPrice->getPlan()->getCode(),
                metadata: [
                    'effective_at' => $effectiveAt->format(\DATE_ATOM),
                    'schedule_id' => (string) $schedule->id,
                    'from_amount_minor' => $currentPlanPrice->getAmountMinor(),
                    'to_amount_minor' => $targetPlanPrice->getAmountMinor(),
                ],
            );

            $this->logger->info('[SUBSCRIPTION] Downgrade scheduled at period end.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'from_plan' => $currentPlanPrice->getPlan()->getCode(),
                'to_plan' => $targetPlanPrice->getPlan()->getCode(),
                'effective_at' => $effectiveAt->format(\DATE_ATOM),
                'schedule' => $schedule->id,
            ]);

            $this->entityManager->flush();

            return $subscription;
        });
    }

    /**
     * @throws PlanChangeException when no change is currently scheduled
     */
    public function cancelScheduledDowngrade(AgencySubscription $subscription): AgencySubscription
    {
        if (!$subscription->hasPendingPlanChange()) {
            throw new PlanChangeException('billing.plan_change.none_scheduled');
        }

        return $this->entityManager->wrapInTransaction(function () use ($subscription): AgencySubscription {
            $scheduleId = $subscription->getProviderScheduleId();
            $canceledTargetPlan = $subscription->getPendingPlanPrice()?->getPlan()->getCode();

            if (\is_string($scheduleId) && '' !== $scheduleId) {
                $this->stripeSubscriptionService->releaseSchedule($scheduleId);
            }

            $subscription
                ->clearPendingPlanChange()
                ->setLastStripeSyncAt(new \DateTimeImmutable());

            $this->historyRecorder->record(
                subscription: $subscription,
                eventType: SubscriptionHistoryEventType::PLAN_CHANGE_CANCELED,
                oldStatus: $subscription->getStatus(),
                newStatus: $subscription->getStatus(),
                oldPlan: $subscription->getPlan()->getCode(),
                newPlan: $subscription->getPlan()->getCode(),
                metadata: ['canceled_target_plan' => $canceledTargetPlan],
            );

            $this->logger->info('[SUBSCRIPTION] Scheduled downgrade canceled.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'canceled_target_plan' => $canceledTargetPlan,
            ]);

            $this->entityManager->flush();

            return $subscription;
        });
    }
}
