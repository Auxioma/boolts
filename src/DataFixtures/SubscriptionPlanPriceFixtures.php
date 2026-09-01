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

use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class SubscriptionPlanPriceFixtures extends Fixture implements DependentFixtureInterface
{
    public const FREE_PLAN_PRICE_REFERENCE = 'subscription_plan_price_free_monthly';

    public function load(ObjectManager $manager): void
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les prix des abonnements.');
        }

        $plan = $this->getReference(SubscriptionPlanFixtures::FREE_PLAN_REFERENCE, SubscriptionPlan::class);

        $price = new SubscriptionPlanPrice();
        $price
            ->setPlan($plan)
            ->setCurrency($currency)
            ->setAmountMinor(0)
            ->setBillingPeriod(SubscriptionBillingPeriod::MONTHLY)
            ->setIsActive(true);

        $manager->persist($price);
        $this->addReference(self::FREE_PLAN_PRICE_REFERENCE, $price);

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
