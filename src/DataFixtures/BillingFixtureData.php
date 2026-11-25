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
            'name' => ['fr' => 'Gratuit', 'en' => 'Free', 'es' => 'Gratis', 'de' => 'Kostenlos', 'pt' => 'Gratuito', 'it' => 'Gratuito', 'nl' => 'Gratis', 'hu' => 'Ingyenes'],
            'description' => [
                'fr' => 'Publiez gratuitement vos premières annonces.',
                'en' => 'Publish your first listings for free.',
                'es' => 'Publica gratis tus primeros anuncios.',
                'de' => 'Veröffentlichen Sie Ihre ersten Anzeigen kostenlos.',
                'pt' => 'Publique gratuitamente os seus primeiros anúncios.',
                'it' => 'Pubblica gratuitamente i tuoi primi annunci.',
                'nl' => 'Publiceer je eerste advertenties gratis.',
                'hu' => 'Tegye közzé első hirdetéseit ingyen.',
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
            'name' => ['fr' => 'Débutant', 'en' => 'Beginner', 'es' => 'Principiante', 'de' => 'Einsteiger', 'pt' => 'Iniciante', 'it' => 'Principiante', 'nl' => 'Beginner', 'hu' => 'Kezdő'],
            'description' => [
                'fr' => 'Pour les agences qui démarrent leur activité en ligne.',
                'en' => 'For agencies starting their online activity.',
                'es' => 'Para agencias que inician su actividad en línea.',
                'de' => 'Für Agenturen, die ihre Online-Aktivität starten.',
                'pt' => 'Para agências que iniciam a sua atividade online.',
                'it' => 'Per le agenzie che avviano la loro attività online.',
                'nl' => 'Voor kantoren die hun online activiteit starten.',
                'hu' => 'Online tevékenységüket most kezdő ügynökségeknek.',
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
            'name' => ['fr' => 'Confirmé', 'en' => 'Experienced', 'es' => 'Experto', 'de' => 'Erfahren', 'pt' => 'Experiente', 'it' => 'Esperto', 'nl' => 'Ervaren', 'hu' => 'Tapasztalt'],
            'description' => [
                'fr' => 'Pour les agences avec un catalogue d’annonces conséquent.',
                'en' => 'For agencies with a large catalogue of listings.',
                'es' => 'Para agencias con un amplio catálogo de anuncios.',
                'de' => 'Für Agenturen mit einem umfangreichen Anzeigenkatalog.',
                'pt' => 'Para agências com um vasto catálogo de anúncios.',
                'it' => 'Per le agenzie con un ampio catalogo di annunci.',
                'nl' => 'Voor kantoren met een grote advertentiecatalogus.',
                'hu' => 'Nagy hirdetésállománnyal rendelkező ügynökségeknek.',
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
            'name' => ['fr' => 'Gratuit', 'en' => 'Free', 'es' => 'Gratis', 'de' => 'Kostenlos', 'pt' => 'Gratuito', 'it' => 'Gratuito', 'nl' => 'Gratis', 'hu' => 'Ingyenes'],
            'description' => [
                'fr' => 'Publiez gratuitement vos premières annonces (facturation annuelle).',
                'en' => 'Publish your first listings for free (billed annually).',
                'es' => 'Publica gratis tus primeros anuncios (facturación anual).',
                'de' => 'Veröffentlichen Sie Ihre ersten Anzeigen kostenlos (jährliche Abrechnung).',
                'pt' => 'Publique gratuitamente os seus primeiros anúncios (faturação anual).',
                'it' => 'Pubblica gratuitamente i tuoi primi annunci (fatturazione annuale).',
                'nl' => 'Publiceer je eerste advertenties gratis (jaarlijkse facturering).',
                'hu' => 'Tegye közzé első hirdetéseit ingyen (éves számlázás).',
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
            'name' => ['fr' => 'Started', 'en' => 'Started', 'es' => 'Started', 'de' => 'Started', 'pt' => 'Started', 'it' => 'Started', 'nl' => 'Started', 'hu' => 'Started'],
            'description' => [
                'fr' => 'Formule annuelle pour lancer votre présence en ligne.',
                'en' => 'Annual plan to launch your online presence.',
                'es' => 'Plan anual para lanzar tu presencia en línea.',
                'de' => 'Jahresplan für den Start Ihrer Online-Präsenz.',
                'pt' => 'Plano anual para lançar a sua presença online.',
                'it' => 'Piano annuale per lanciare la tua presenza online.',
                'nl' => 'Jaarabonnement om je online aanwezigheid te lanceren.',
                'hu' => 'Éves csomag online jelenléte elindításához.',
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
            'name' => ['fr' => 'Boss', 'en' => 'Boss', 'es' => 'Boss', 'de' => 'Boss', 'pt' => 'Boss', 'it' => 'Boss', 'nl' => 'Boss', 'hu' => 'Boss'],
            'description' => [
                'fr' => 'Formule annuelle pour les agences à fort volume.',
                'en' => 'Annual plan for high-volume agencies.',
                'es' => 'Plan anual para agencias de gran volumen.',
                'de' => 'Jahresplan für Agenturen mit hohem Volumen.',
                'pt' => 'Plano anual para agências de grande volume.',
                'it' => 'Piano annuale per agenzie ad alto volume.',
                'nl' => 'Jaarabonnement voor kantoren met een groot volume.',
                'hu' => 'Éves csomag nagy forgalmú ügynökségeknek.',
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
            'name' => ['fr' => '1 boost', 'en' => '1 boost', 'es' => '1 boost', 'de' => '1 Boost', 'pt' => '1 boost', 'it' => '1 boost', 'nl' => '1 boost', 'hu' => '1 kiemelés'],
            'description' => [
                'fr' => '1 boost s’applique sur 1 annonce et dure 15 jours.',
                'en' => '1 boost applies to 1 listing and lasts 15 days.',
                'es' => '1 boost se aplica a 1 anuncio y dura 15 días.',
                'de' => '1 Boost gilt für 1 Anzeige und dauert 15 Tage.',
                'pt' => '1 boost aplica-se a 1 anúncio e dura 15 dias.',
                'it' => '1 boost si applica a 1 annuncio e dura 15 giorni.',
                'nl' => '1 boost geldt voor 1 advertentie en duurt 15 dagen.',
                'hu' => '1 kiemelés 1 hirdetésre vonatkozik, és 15 napig tart.',
            ],
            'boostQuantity' => 1,
            'boostDurationDays' => 15,
            'position' => 1,
        ],
        [
            'code' => 'boost-5',
            'name' => ['fr' => '5 boosts', 'en' => '5 boosts', 'es' => '5 boosts', 'de' => '5 Boosts', 'pt' => '5 boosts', 'it' => '5 boost', 'nl' => '5 boosts', 'hu' => '5 kiemelés'],
            'description' => [
                'fr' => '5 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
                'en' => '5 boosts to use on your listings for 15 days each.',
                'es' => '5 boosts para usar en tus anuncios durante 15 días cada uno.',
                'de' => '5 Boosts für Ihre Anzeigen, jeweils 15 Tage lang.',
                'pt' => '5 boosts para utilizar nos seus anúncios durante 15 dias cada.',
                'it' => '5 boost da usare sui tuoi annunci per 15 giorni ciascuno.',
                'nl' => '5 boosts voor je advertenties, elk 15 dagen geldig.',
                'hu' => '5 kiemelés a hirdetéseihez, egyenként 15 napra.',
            ],
            'boostQuantity' => 5,
            'boostDurationDays' => 15,
            'position' => 2,
        ],
        [
            'code' => 'boost-20',
            'name' => ['fr' => '20 boosts', 'en' => '20 boosts', 'es' => '20 boosts', 'de' => '20 Boosts', 'pt' => '20 boosts', 'it' => '20 boost', 'nl' => '20 boosts', 'hu' => '20 kiemelés'],
            'description' => [
                'fr' => '20 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
                'en' => '20 boosts to use on your listings for 15 days each.',
                'es' => '20 boosts para usar en tus anuncios durante 15 días cada uno.',
                'de' => '20 Boosts für Ihre Anzeigen, jeweils 15 Tage lang.',
                'pt' => '20 boosts para utilizar nos seus anúncios durante 15 dias cada.',
                'it' => '20 boost da usare sui tuoi annunci per 15 giorni ciascuno.',
                'nl' => '20 boosts voor je advertenties, elk 15 dagen geldig.',
                'hu' => '20 kiemelés a hirdetéseihez, egyenként 15 napra.',
            ],
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
