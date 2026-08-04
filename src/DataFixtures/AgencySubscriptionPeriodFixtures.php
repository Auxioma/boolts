<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Payment;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class AgencySubscriptionPeriodFixtures extends Fixture implements DependentFixtureInterface
{
    public const AGENCY_SUBSCRIPTION_PERIOD_REFERENCE_PREFIX = 'agency_subscription_period_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);
        $periodStart = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $periodEnd = $periodStart->modify('+1 month')->modify('-1 second');

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $planCode = BillingFixtureData::agencyPlanCode($position);
            $subscription = $this->getReference(
                BillingFixtureData::subscriptionReference($agencyKey),
                AgencySubscription::class,
            );
            $payment = null;

            if ('free' !== $planCode) {
                $payment = $this->getReference(
                    BillingFixtureData::subscriptionPaymentReference($agencyKey),
                    Payment::class,
                );
            }

            $period = (new AgencySubscriptionPeriod())
                ->setSubscription($subscription)
                ->setPeriodStart($periodStart)
                ->setPeriodEnd($periodEnd)
                ->setPropertyLimit($subscription->getPropertyLimitSnapshot())
                ->setIncludedBoosts($subscription->getIncludedBoostsSnapshot())
                ->setAmountMinor(BillingFixtureData::SUBSCRIPTION_PRICES[SubscriptionBillingPeriod::MONTHLY->value][$planCode])
                ->setCurrency($currency)
                ->setPayment($payment)
                ->setStatus('free' === $planCode ? SubscriptionPeriodStatus::FREE : SubscriptionPeriodStatus::PAID)
                ->setProviderInvoiceId('free' === $planCode ? null : 'in_fixture_period_'.$agencyKey);

            $manager->persist($period);
            $this->addReference(BillingFixtureData::subscriptionPeriodReference($agencyKey), $period);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AgencySubscriptionFixtures::class,
            PaymentFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les périodes d’abonnement.');
        }

        return $currency;
    }
}
