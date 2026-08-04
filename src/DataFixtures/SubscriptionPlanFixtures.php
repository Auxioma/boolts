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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class SubscriptionPlanFixtures extends Fixture
{
    public const SUBSCRIPTION_PLAN_REFERENCE_PREFIX = 'subscription_plan_';

    public function load(ObjectManager $manager): void
    {
        foreach (BillingFixtureData::SUBSCRIPTION_PLANS as $data) {
            $plan = new SubscriptionPlan();
            $plan
                ->setCode($data['code'])
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setPropertyLimit($data['propertyLimit'])
                ->setIncludedBoosts($data['includedBoosts'])
                ->setBoostDurationDays($data['boostDurationDays'])
                ->setIsFree($data['isFree'])
                ->setIsDefault($data['isDefault'])
                ->setIsActive(true)
                ->setPosition($data['position']);

            $manager->persist($plan);
            $this->addReference(self::SUBSCRIPTION_PLAN_REFERENCE_PREFIX.$data['code'], $plan);
        }

        $manager->flush();
    }
}
