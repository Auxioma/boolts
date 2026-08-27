<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\DowngradeReason;
use App\Entity\Billing\Enum\SubscriptionEmailType;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Billing\SubscriptionPlanPriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class SubscriptionDowngradeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AgencySubscriptionRepository $subscriptionRepository,
        private SubscriptionPlanPriceRepository $planPriceRepository,
        private SubscriptionEntitlementService $entitlementService,
        private SubscriptionHistoryRecorder $historyRecorder,
        private SubscriptionEmailDispatcher $emailDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function downgradeToFree(
        AgencySubscription $subscription,
        DowngradeReason $reason,
        ?\DateTimeImmutable $now = null,
    ): AgencySubscription {
        $now ??= new \DateTimeImmutable();

        return $this->entityManager->wrapInTransaction(function () use ($subscription, $reason, $now): AgencySubscription {
            $oldStatus = $subscription->getStatus();
            $oldPlan = $subscription->getPlan()->getCode();
            $terminalStatus = DowngradeReason::CANCEL_AT_PERIOD_END === $reason
                ? SubscriptionStatus::CANCELED
                : SubscriptionStatus::EXPIRED;

            $subscription
                ->setStatus($terminalStatus)
                ->setCancelAtPeriodEnd(false)
                ->setNextPaymentRetryAt(null)
                ->setPaymentRecoveryDeadline(null)
                ->setCanceledAt($subscription->getCanceledAt() ?? $now)
                ->setEndedAt($subscription->getEndedAt() ?? $now)
                ->setLastStripeSyncAt($now);

            $freeSubscription = $this->getOrCreateOpenFreeSubscription($subscription, $now);
            $suspendedProperties = $this->entitlementService->applyFreePlanLimits($freeSubscription);

            $this->historyRecorder->record(
                subscription: $subscription,
                eventType: SubscriptionHistoryEventType::DOWNGRADED_TO_FREE,
                oldStatus: $oldStatus,
                newStatus: $terminalStatus,
                oldPlan: $oldPlan,
                newPlan: $freeSubscription->getPlan()->getCode(),
                providerInvoiceId: $subscription->getProviderLatestInvoiceId(),
                metadata: [
                    'reason' => $reason->value,
                    'free_subscription_id' => $freeSubscription->getId(),
                    'suspended_properties' => $suspendedProperties,
                ],
            );

            $emailType = DowngradeReason::CANCEL_AT_PERIOD_END === $reason
                ? SubscriptionEmailType::SUBSCRIPTION_ENDED
                : SubscriptionEmailType::DOWNGRADED_TO_FREE;

            $this->emailDispatcher->dispatchOnce(
                $subscription,
                $emailType,
                $reason->value.'-'.($subscription->getProviderLatestInvoiceId() ?? (string) $subscription->getId()),
                [
                    'plan' => $oldPlan,
                    'ended_at' => $subscription->getEndedAt()?->format(\DATE_ATOM),
                    'reason' => $reason->value,
                    'suspended_properties' => $suspendedProperties,
                ],
            );

            $this->logger->warning('[SUBSCRIPTION DOWNGRADE] Paid subscription downgraded to free.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'old_plan' => $oldPlan,
                'new_plan' => $freeSubscription->getPlan()->getCode(),
                'reason' => $reason->value,
            ]);

            $this->entityManager->flush();

            return $freeSubscription;
        });
    }

    private function getOrCreateOpenFreeSubscription(
        AgencySubscription $paidSubscription,
        \DateTimeImmutable $now,
    ): AgencySubscription {
        $freeSubscription = $this->subscriptionRepository->findOpenFreeForAgency($paidSubscription->getAgency())[0] ?? null;

        if ($freeSubscription instanceof AgencySubscription) {
            return $freeSubscription;
        }

        $freePlanPrice = $this->planPriceRepository->findDefaultFreeMonthlyPrice();

        if (!$freePlanPrice instanceof SubscriptionPlanPrice) {
            throw new \LogicException('Aucune offre gratuite n’est configurée.');
        }

        $freePlan = $freePlanPrice->getPlan();
        $periodEnd = $now->modify('+1 month')->modify('-1 second');

        $freeSubscription = (new AgencySubscription())
            ->setAgency($paidSubscription->getAgency())
            ->setPlan($freePlan)
            ->setPlanPrice($freePlanPrice)
            ->setStatus(SubscriptionStatus::FREE)
            ->setStartedAt($now)
            ->setCurrentPeriodStart($now)
            ->setCurrentPeriodEnd($periodEnd)
            ->setCancelAtPeriodEnd(false)
            ->setPropertyLimitSnapshot($freePlan->getPropertyLimit())
            ->setIncludedBoostsSnapshot($freePlan->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($freePlan->getBoostDurationDays())
            ->setAmountSnapshotMinor($freePlanPrice->getAmountMinor())
            ->setCurrencySnapshot($freePlanPrice->getCurrency());

        $period = (new AgencySubscriptionPeriod())
            ->setSubscription($freeSubscription)
            ->setPeriodStart($now)
            ->setPeriodEnd($periodEnd)
            ->setPropertyLimit($freePlan->getPropertyLimit())
            ->setIncludedBoosts($freePlan->getIncludedBoosts())
            ->setAmountMinor(0)
            ->setCurrency($freePlanPrice->getCurrency())
            ->setStatus(SubscriptionPeriodStatus::FREE);

        $this->entityManager->persist($freeSubscription);
        $this->entityManager->persist($period);

        return $freeSubscription;
    }
}
