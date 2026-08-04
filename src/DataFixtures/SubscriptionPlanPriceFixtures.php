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
    public const SUBSCRIPTION_PLAN_PRICE_REFERENCE_PREFIX = 'subscription_plan_price_';

    public function load(ObjectManager $manager): void
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les prix des abonnements.');
        }

        foreach (BillingFixtureData::SUBSCRIPTION_PRICES as $billingPeriod => $prices) {
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
                $this->addReference(
                    self::SUBSCRIPTION_PLAN_PRICE_REFERENCE_PREFIX.$planCode.'_'.$billingPeriod,
                    $price,
                );
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
