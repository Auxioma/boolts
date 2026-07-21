<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures;

use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class SubscriptionPlanPriceFixtures extends Fixture implements DependentFixtureInterface
{
    private const PRICES = [
        SubscriptionBillingPeriod::MONTHLY->value => [
            'free' => 0,
            'starter' => 1990,
            'pro' => 4990,
            'premium' => 9990,
        ],
        SubscriptionBillingPeriod::ANNUAL->value => [
            'free' => 0,
            'starter' => 19900,
            'pro' => 49900,
            'premium' => 99900,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les prix des abonnements.');
        }

        foreach (self::PRICES as $billingPeriod => $prices) {
            foreach ($prices as $planCode => $amountMinor) {
                $plan = $this->getReference(
                    SubscriptionPlanFixtures::SUBSCRIPTION_PLAN_REFERENCE_PREFIX.$planCode,
                    SubscriptionPlan::class
                );

                $price = new SubscriptionPlanPrice();
                $price
                    ->setPlan($plan)
                    ->setCurrency($currency)
                    ->setAmountMinor($amountMinor)
                    ->setBillingPeriod(SubscriptionBillingPeriod::from($billingPeriod))
                    ->setIsActive(true);

                $manager->persist($price);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaysFixtures::class,
            SubscriptionPlanFixtures::class,
        ];
    }
}
