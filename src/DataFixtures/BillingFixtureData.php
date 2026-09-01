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

final class BillingFixtureData
{
    public const FREE_PLAN = [
        'code' => 'free',
        'name' => 'Gratuit',
        'description' => 'Publiez gratuitement vos premières annonces.',
        'propertyLimit' => 3,
        'includedBoosts' => 0,
        'boostDurationDays' => 7,
        'isFree' => true,
        'isDefault' => true,
        'position' => 1,
    ];

    public const BOOSTER_PACKS = [
        [
            'code' => 'boost-1',
            'name' => '1 boost',
            'description' => '1 boost s’applique sur 1 annonce et dure 15 jours.',
            'boostQuantity' => 1,
            'boostDurationDays' => 15,
            'position' => 1,
        ],
        [
            'code' => 'boost-5',
            'name' => '5 boosts',
            'description' => '5 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
            'boostQuantity' => 5,
            'boostDurationDays' => 15,
            'position' => 2,
        ],
        [
            'code' => 'boost-20',
            'name' => '20 boosts',
            'description' => '20 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
            'boostQuantity' => 20,
            'boostDurationDays' => 15,
            'position' => 3,
        ],
    ];

    public const BOOSTER_PRICES = [
        'boost-1' => 2499,
        'boost-5' => 9999,
        'boost-20' => 29999,
    ];
}
