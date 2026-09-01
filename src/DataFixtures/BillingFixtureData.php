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
    /**
     * Les 6 forfaits : 3 mensuels (Gratuit, Débutant, Confirmé) et 3 annuels
     * (Gratuit, Started, Boss). Chaque forfait porte exactement un prix
     * (billingPeriod + amountMinor). Un seul forfait est isDefault : le Gratuit
     * mensuel, utilisé pour l'activation automatique du forfait gratuit.
     */
    public const SUBSCRIPTION_PLANS = [
        [
            'code' => 'free-monthly',
            'name' => 'Gratuit',
            'description' => 'Publiez gratuitement vos premières annonces.',
            'propertyLimit' => 3,
            'includedBoosts' => 0,
            'boostDurationDays' => 7,
            'isFree' => true,
            'isDefault' => true,
            'position' => 1,
            'billingPeriod' => 'monthly',
            'amountMinor' => 0,
        ],
        [
            'code' => 'debutant',
            'name' => 'Débutant',
            'description' => 'Pour les agences qui démarrent leur activité en ligne.',
            'propertyLimit' => 10,
            'includedBoosts' => 1,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 2,
            'billingPeriod' => 'monthly',
            'amountMinor' => 1999,
        ],
        [
            'code' => 'confirme',
            'name' => 'Confirmé',
            'description' => 'Pour les agences avec un catalogue d’annonces conséquent.',
            'propertyLimit' => 50,
            'includedBoosts' => 5,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 3,
            'billingPeriod' => 'monthly',
            'amountMinor' => 4999,
        ],
        [
            'code' => 'free-annual',
            'name' => 'Gratuit',
            'description' => 'Publiez gratuitement vos premières annonces (facturation annuelle).',
            'propertyLimit' => 3,
            'includedBoosts' => 0,
            'boostDurationDays' => 7,
            'isFree' => true,
            'isDefault' => false,
            'position' => 4,
            'billingPeriod' => 'annual',
            'amountMinor' => 0,
        ],
        [
            'code' => 'started',
            'name' => 'Started',
            'description' => 'Formule annuelle pour lancer votre présence en ligne.',
            'propertyLimit' => 15,
            'includedBoosts' => 2,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 5,
            'billingPeriod' => 'annual',
            'amountMinor' => 19999,
        ],
        [
            'code' => 'boss',
            'name' => 'Boss',
            'description' => 'Formule annuelle pour les agences à fort volume.',
            'propertyLimit' => 100,
            'includedBoosts' => 12,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 6,
            'billingPeriod' => 'annual',
            'amountMinor' => 49999,
        ],
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
