<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Repository\Billing\AgencySubscriptionPeriodRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FreeSubscriptionRenewalService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AgencySubscriptionPeriodRepository $periodRepository,
    ) {
    }

    public function renew(
        AgencySubscription $subscription,
        ?\DateTimeImmutable $now = null,
    ): void {
        $now ??= new \DateTimeImmutable();

        if (SubscriptionStatus::FREE !== $subscription->getStatus() || !$subscription->getPlan()->isFree()) {
            throw new \LogicException('Seul un abonnement gratuit peut être renouvelé gratuitement.');
        }

        $currentPeriodEnd = $subscription->getCurrentPeriodEnd();

        if (!$currentPeriodEnd instanceof \DateTimeImmutable) {
            throw new \LogicException('La fin de période de l’abonnement gratuit est manquante.');
        }

        $plan = $subscription->getPlan();
        $planPrice = $subscription->getPlanPrice();
        $currency = $subscription->getCurrencySnapshot() ?? $planPrice?->getCurrency();

        if (null === $currency) {
            throw new \LogicException('La devise de l’abonnement gratuit est manquante.');
        }

        foreach (self::resolveRenewalPeriods($currentPeriodEnd, $now) as $periodDates) {
            $period = $this->periodRepository->findOneForPeriod(
                $subscription,
                $periodDates['start'],
                $periodDates['end'],
            );

            if (!$period instanceof AgencySubscriptionPeriod) {
                $period = (new AgencySubscriptionPeriod())
                    ->setSubscription($subscription)
                    ->setPeriodStart($periodDates['start'])
                    ->setPeriodEnd($periodDates['end'])
                    ->setPropertyLimit($plan->getPropertyLimit())
                    ->setIncludedBoosts($plan->getIncludedBoosts())
                    ->setAmountMinor($planPrice?->getAmountMinor() ?? 0)
                    ->setCurrency($currency)
                    ->setStatus(SubscriptionPeriodStatus::FREE);

                $this->entityManager->persist($period);
            }

            $subscription
                ->setCurrentPeriodStart($periodDates['start'])
                ->setCurrentPeriodEnd($periodDates['end']);
        }

        $subscription
            ->setPropertyLimitSnapshot($plan->getPropertyLimit())
            ->setIncludedBoostsSnapshot($plan->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($plan->getBoostDurationDays())
            ->setAmountSnapshotMinor($planPrice?->getAmountMinor() ?? 0)
            ->setCurrencySnapshot($currency);

        $this->entityManager->flush();
    }

    /**
     * @return list<array{start: \DateTimeImmutable, end: \DateTimeImmutable}>
     */
    public static function resolveRenewalPeriods(
        \DateTimeImmutable $currentPeriodEnd,
        \DateTimeImmutable $now,
    ): array {
        $periods = [];

        while ($currentPeriodEnd <= $now) {
            $periodStart = $currentPeriodEnd->modify('+1 second');
            $periodEnd = $periodStart->modify('+1 month')->modify('-1 second');
            $periods[] = ['start' => $periodStart, 'end' => $periodEnd];
            $currentPeriodEnd = $periodEnd;
        }

        return $periods;
    }
}
