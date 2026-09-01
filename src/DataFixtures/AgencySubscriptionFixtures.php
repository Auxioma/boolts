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

namespace App\DataFixtures;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class AgencySubscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    public const AGENCY_SUBSCRIPTION_REFERENCE_PREFIX = 'agency_subscription_';

    public function load(ObjectManager $manager): void
    {
        $plan = $this->getReference(SubscriptionPlanFixtures::FREE_PLAN_REFERENCE, SubscriptionPlan::class);
        $planPrice = $this->getReference(
            SubscriptionPlanPriceFixtures::FREE_PLAN_PRICE_REFERENCE,
            SubscriptionPlanPrice::class,
        );
        $startedAt = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);

        for ($i = 1; $i <= UserFixtures::AGENCY_COUNT; ++$i) {
            $agency = $this->getReference(UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$i, User::class);

            $subscription = (new AgencySubscription())
                ->setAgency($agency)
                ->setPlan($plan)
                ->setPlanPrice($planPrice)
                ->setStatus(SubscriptionStatus::FREE)
                ->setStartedAt($startedAt)
                ->setCurrentPeriodStart($startedAt)
                ->setCancelAtPeriodEnd(false)
                ->setPropertyLimitSnapshot($plan->getPropertyLimit())
                ->setIncludedBoostsSnapshot($plan->getIncludedBoosts())
                ->setBoostDurationDaysSnapshot($plan->getBoostDurationDays())
                ->setAmountSnapshotMinor(0)
                ->setCurrencySnapshot($planPrice->getCurrency());

            $manager->persist($subscription);
            $this->addReference(self::AGENCY_SUBSCRIPTION_REFERENCE_PREFIX.$i, $subscription);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            SubscriptionPlanFixtures::class,
            SubscriptionPlanPriceFixtures::class,
        ];
    }
}
