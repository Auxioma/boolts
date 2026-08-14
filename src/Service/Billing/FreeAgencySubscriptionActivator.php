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

namespace App\Service\Billing;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Billing\SubscriptionPlanPriceRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FreeAgencySubscriptionActivator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AgencySubscriptionRepository $agencySubscriptionRepository,
        private SubscriptionPlanPriceRepository $subscriptionPlanPriceRepository,
    ) {
    }

    public function activate(User $agency): AgencySubscription
    {
        $existingSubscription = $this->agencySubscriptionRepository->findLatestForAgency($agency);

        if ($existingSubscription instanceof AgencySubscription) {
            return $existingSubscription;
        }

        $freePlanPrice = $this->subscriptionPlanPriceRepository->findDefaultFreeMonthlyPrice();

        if (!$freePlanPrice instanceof SubscriptionPlanPrice) {
            throw new \LogicException('Aucune offre gratuite n’est configurée.');
        }

        $plan = $freePlanPrice->getPlan();
        $periodStart = new \DateTimeImmutable();
        $periodEnd = $periodStart->modify('+1 month')->modify('-1 second');

        $subscription = (new AgencySubscription())
            ->setAgency($agency)
            ->setPlan($plan)
            ->setPlanPrice($freePlanPrice)
            ->setStatus(SubscriptionStatus::FREE)
            ->setStartedAt($periodStart)
            ->setCurrentPeriodStart($periodStart)
            ->setCurrentPeriodEnd($periodEnd)
            ->setCancelAtPeriodEnd(false)
            ->setPropertyLimitSnapshot($plan->getPropertyLimit())
            ->setIncludedBoostsSnapshot($plan->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($plan->getBoostDurationDays())
            ->setAmountSnapshotMinor($freePlanPrice->getAmountMinor())
            ->setCurrencySnapshot($freePlanPrice->getCurrency());

        $period = (new AgencySubscriptionPeriod())
            ->setSubscription($subscription)
            ->setPeriodStart($periodStart)
            ->setPeriodEnd($periodEnd)
            ->setPropertyLimit($plan->getPropertyLimit())
            ->setIncludedBoosts($plan->getIncludedBoosts())
            ->setAmountMinor($freePlanPrice->getAmountMinor())
            ->setCurrency($freePlanPrice->getCurrency())
            ->setStatus(SubscriptionPeriodStatus::FREE);

        $this->entityManager->persist($subscription);
        $this->entityManager->persist($period);

        return $subscription;
    }
}
