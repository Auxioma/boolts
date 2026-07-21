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

    private const PLANS = [
        [
            'code' => 'free',
            'name' => 'Gratuit',
            'description' => 'Publiez gratuitement vos premières annonces.',
            'propertyLimit' => 3,
            'includedBoosts' => 0,
            'boostDurationDays' => 7,
            'isFree' => true,
            'isDefault' => true,
            'position' => 1,
        ],
        [
            'code' => 'starter',
            'name' => 'Starter',
            'description' => 'Une offre adaptée aux professionnels qui démarrent.',
            'propertyLimit' => 10,
            'includedBoosts' => 1,
            'boostDurationDays' => 7,
            'isFree' => false,
            'isDefault' => false,
            'position' => 2,
        ],
        [
            'code' => 'pro',
            'name' => 'Pro',
            'description' => 'Gérez davantage de biens et améliorez leur visibilité.',
            'propertyLimit' => 50,
            'includedBoosts' => 5,
            'boostDurationDays' => 14,
            'isFree' => false,
            'isDefault' => false,
            'position' => 3,
        ],
        [
            'code' => 'premium',
            'name' => 'Premium',
            'description' => 'Une offre complète avec un nombre de biens illimité.',
            'propertyLimit' => null,
            'includedBoosts' => 15,
            'boostDurationDays' => 30,
            'isFree' => false,
            'isDefault' => false,
            'position' => 4,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PLANS as $data) {
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
