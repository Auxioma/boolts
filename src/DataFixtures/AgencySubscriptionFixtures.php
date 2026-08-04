<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Devise;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class AgencySubscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    public const AGENCY_SUBSCRIPTION_REFERENCE_PREFIX = 'agency_subscription_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);
        $periodStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $periodEnd = $periodStart->modify('+1 month')->modify('-1 second');

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $planCode = BillingFixtureData::agencyPlanCode($position);
            $agency = $this->getReference(BillingFixtureData::agencyReferences()[$agencyKey], User::class);
            $plan = $this->getReference(
                SubscriptionPlanFixtures::SUBSCRIPTION_PLAN_REFERENCE_PREFIX.$planCode,
                SubscriptionPlan::class,
            );
            $planPrice = $this->getReference(
                SubscriptionPlanPriceFixtures::SUBSCRIPTION_PLAN_PRICE_REFERENCE_PREFIX.$planCode.'_'.SubscriptionBillingPeriod::MONTHLY->value,
                SubscriptionPlanPrice::class,
            );
            $amountMinor = BillingFixtureData::SUBSCRIPTION_PRICES[SubscriptionBillingPeriod::MONTHLY->value][$planCode];

            $subscription = (new AgencySubscription())
                ->setAgency($agency)
                ->setPlan($plan)
                ->setPlanPrice($planPrice)
                ->setStatus($plan->isIsFree() ? SubscriptionStatus::FREE : SubscriptionStatus::ACTIVE)
                ->setStartedAt($periodStart)
                ->setCurrentPeriodStart($periodStart)
                ->setCurrentPeriodEnd($periodEnd)
                ->setCancelAtPeriodEnd(false)
                ->setProviderCustomerId('cus_fixture_'.$agencyKey)
                ->setProviderSubscriptionId($plan->isIsFree() ? null : 'sub_fixture_'.$agencyKey)
                ->setProviderSubscriptionItemId($plan->isIsFree() ? null : 'si_fixture_'.$agencyKey)
                ->setPropertyLimitSnapshot($plan->getPropertyLimit())
                ->setIncludedBoostsSnapshot($plan->getIncludedBoosts())
                ->setBoostDurationDaysSnapshot($plan->getBoostDurationDays())
                ->setAmountSnapshotMinor($amountMinor)
                ->setCurrencySnapshot($currency);

            $manager->persist($subscription);
            $this->addReference(BillingFixtureData::subscriptionReference($agencyKey), $subscription);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PaysFixtures::class,
            SubscriptionPlanFixtures::class,
            SubscriptionPlanPriceFixtures::class,
        ];
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les abonnements agence.');
        }

        return $currency;
    }
}
