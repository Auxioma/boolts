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
     * Devises proposées pour les forfaits : l'euro et toutes les devises du
     * taux de référence BCE (forex), avec leur nom dans la table devise et
     * leur taux face à l'euro (BCE, 23/10/2026).
     *
     * Le prix converti est arrondi au prix « psychologique » immédiatement
     * supérieur : ,99 pour les devises à centimes (22,99 $), …99 pour les
     * devises sans centimes (JPY, KRW, ISK : 3 599 ¥).
     */
    public const SUBSCRIPTION_CURRENCIES = [
        'EUR' => ['euro (EUR)', 1],
        'USD' => ['dollar des États-Unis (USD)', 1.1193],
        'JPY' => ['yen japonais (JPY)', 175.51],
        'CZK' => ['couronne tchèque (CZK)', 24.327],
        'DKK' => ['couronne danoise (DKK)', 7.4748],
        'GBP' => ['livre sterling (GBP)', 0.83728],
        'HUF' => ['forint hongrois (HUF)', 366.73],
        'PLN' => ['zloty polonais (PLN)', 4.3945],
        'RON' => ['leu roumain (RON)', 5.3492],
        'SEK' => ['couronne suédoise (SEK)', 11.1310],
        'CHF' => ['franc suisse (CHF)', 0.9227],
        'ISK' => ['couronne islandaise (ISK)', 136.20],
        'NOK' => ['couronne norvégienne (NOK)', 10.4665],
        'TRY' => ['livre turque (TRY)', 55.6715],
        'AUD' => ['dollar australien (AUD)', 1.5939],
        'BRL' => ['réal brésilien (BRL)', 5.6485],
        'CAD' => ['dollar canadien (CAD)', 1.5665],
        'CNY' => ['yuan renminbi chinois (CNY)', 7.4660],
        'HKD' => ['dollar de Hong Kong (HKD)', 8.7793],
        'IDR' => ['roupie indonésienne (IDR)', 20268.06],
        'ILS' => ['nouveau shekel israélien (ILS)', 3.4939],
        'INR' => ['roupie indienne (INR)', 106.2720],
        'KRW' => ['won sud-coréen (KRW)', 1482.46],
        'MXN' => ['peso mexicain (MXN)', 21.0226],
        'MYR' => ['ringgit malais (MYR)', 4.5729],
        'NZD' => ['dollar néo-zélandais (NZD)', 1.9682],
        'PHP' => ['peso philippin (PHP)', 71.235],
        'SGD' => ['dollar de Singapour (SGD)', 1.4310],
        'THB' => ['baht thaïlandais (THB)', 37.771],
        'ZAR' => ['rand sud-africain (ZAR)', 18.5777],
    ];

    /**
     * Les 6 forfaits : 3 mensuels (Gratuit, Débutant, Confirmé) et 3 annuels
     * (Gratuit, Started, Boss). Chaque forfait porte une périodicité
     * (billingPeriod) et un prix par devise. Un seul forfait est isDefault : le Gratuit
     * mensuel, utilisé pour l'activation automatique du forfait gratuit.
     * Nom et description sont traduits (clés de locale : fr, en).
     * Le prix est donné en EUR (amountMinorEur, en centimes) ; les prix dans
     * les autres devises sont calculés depuis SUBSCRIPTION_CURRENCIES.
     */
    public const SUBSCRIPTION_PLANS = [
        [
            'code' => 'free-monthly',
            'name' => ['fr' => 'Gratuit', 'en' => 'Free'],
            'description' => [
                'fr' => 'Publiez gratuitement vos premières annonces.',
                'en' => 'Publish your first listings for free.',
            ],
            'propertyLimit' => 3,
            'includedBoosts' => 0,
            'boostDurationDays' => 7,
            'isFree' => true,
            'isDefault' => true,
            'position' => 1,
            'billingPeriod' => 'monthly',
            'amountMinorEur' => 0,
        ],
        [
            'code' => 'debutant',
            'name' => ['fr' => 'Débutant', 'en' => 'Beginner'],
            'description' => [
                'fr' => 'Pour les agences qui démarrent leur activité en ligne.',
                'en' => 'For agencies starting their online activity.',
            ],
            'propertyLimit' => 10,
            'includedBoosts' => 1,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 2,
            'billingPeriod' => 'monthly',
            'amountMinorEur' => 1999,
        ],
        [
            'code' => 'confirme',
            'name' => ['fr' => 'Confirmé', 'en' => 'Experienced'],
            'description' => [
                'fr' => 'Pour les agences avec un catalogue d’annonces conséquent.',
                'en' => 'For agencies with a large catalogue of listings.',
            ],
            'propertyLimit' => 50,
            'includedBoosts' => 5,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 3,
            'billingPeriod' => 'monthly',
            'amountMinorEur' => 4999,
        ],
        [
            'code' => 'free-annual',
            'name' => ['fr' => 'Gratuit', 'en' => 'Free'],
            'description' => [
                'fr' => 'Publiez gratuitement vos premières annonces (facturation annuelle).',
                'en' => 'Publish your first listings for free (billed annually).',
            ],
            'propertyLimit' => 3,
            'includedBoosts' => 0,
            'boostDurationDays' => 7,
            'isFree' => true,
            'isDefault' => false,
            'position' => 4,
            'billingPeriod' => 'annual',
            'amountMinorEur' => 0,
        ],
        [
            'code' => 'started',
            'name' => ['fr' => 'Started', 'en' => 'Started'],
            'description' => [
                'fr' => 'Formule annuelle pour lancer votre présence en ligne.',
                'en' => 'Annual plan to launch your online presence.',
            ],
            'propertyLimit' => 15,
            'includedBoosts' => 2,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 5,
            'billingPeriod' => 'annual',
            'amountMinorEur' => 19999,
        ],
        [
            'code' => 'boss',
            'name' => ['fr' => 'Boss', 'en' => 'Boss'],
            'description' => [
                'fr' => 'Formule annuelle pour les agences à fort volume.',
                'en' => 'Annual plan for high-volume agencies.',
            ],
            'propertyLimit' => 100,
            'includedBoosts' => 12,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 6,
            'billingPeriod' => 'annual',
            'amountMinorEur' => 49999,
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
