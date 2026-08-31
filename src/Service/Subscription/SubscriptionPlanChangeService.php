<?php

declare(strict_types=1);

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
            throw new PlanChangeException('Le tarif de l’abonnement actuel est introuvable.');
        }

        if (!\in_array($subscription->getStatus(), [SubscriptionStatus::ACTIVE, SubscriptionStatus::CANCEL_SCHEDULED], true)) {
            throw new PlanChangeException('L’abonnement actuel doit être actif pour programmer un changement de forfait.');
        }

        if ($subscription->getCancelAtPeriodEnd()) {
            throw new PlanChangeException('Une résiliation est déjà programmée ; annulez-la avant de changer de forfait.');
        }

        if ($targetPlanPrice->getPlan()->isIsFree() || $targetPlanPrice->getAmountMinor() <= 0) {
            throw new PlanChangeException('Utilisez la résiliation pour repasser sur l’offre gratuite.');
        }

        if ($currentPlanPrice->getCurrency() !== $targetPlanPrice->getCurrency()) {
            throw new PlanChangeException('La devise du nouveau forfait doit être identique.');
        }

        if ($targetPlanPrice->getAmountMinor() >= $currentPlanPrice->getAmountMinor()) {
            throw new PlanChangeException('Le passage à un forfait de montant supérieur ou égal est immédiat, pas différé.');
        }

        if ($subscription->getPendingPlanPrice()?->getId() === $targetPlanPrice->getId()) {
            return $subscription;
        }

        $effectiveAt = $subscription->getCurrentPeriodEnd();

        if (!$effectiveAt instanceof \DateTimeImmutable) {
            throw new PlanChangeException('La date de fin de période de l’abonnement est inconnue.');
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
            throw new PlanChangeException('Aucun changement de forfait n’est programmé.');
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
