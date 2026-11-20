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

namespace App\DataFixtures\Dashboard;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AgenceImmobiliereCommonTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency.invoices.title' => 'Mes factures',
                'dashboard.back_to_more' => 'Retour au menu Plus',
                'agency_dashboard.custom_period' => 'Période personnalisée',
                'agency_dashboard.apply_short' => 'Appliquer',
                'agency.invoices.search' => 'Recherchez un numéro de facture',
                'agency.invoices.created_at' => 'Créé le %date%',
                'agency.invoices.empty' => 'Vous n’avez aucune facture pour le moment.',
                'agency.listings.title' => 'Mes biens immobiliers',
                'agency.listings.created_pending' => 'Votre annonce a bien été créée. Elle est actuellement en cours d’analyse.',
                'agency.listings.view_all' => 'Voir mes biens',
                'mes_biens.topbar.title' => 'Gestion du bien',
                'agency_listings.list_title' => 'Liste',
            ],
            'en' => [
                'agency.invoices.title' => 'My invoices',
                'dashboard.back_to_more' => 'Back to More menu',
                'agency_dashboard.custom_period' => 'Custom period',
                'agency_dashboard.apply_short' => 'Apply',
                'agency.invoices.search' => 'Search for an invoice number',
                'agency.invoices.created_at' => 'Created on %date%',
                'agency.invoices.empty' => 'You don’t have any invoices yet.',
                'agency.listings.title' => 'My properties',
                'agency.listings.created_pending' => 'Your listing has been created. It is currently being reviewed.',
                'agency.listings.view_all' => 'View my properties',
                'mes_biens.topbar.title' => 'Property management',
                'agency_listings.list_title' => 'List',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = FixtureEntityHelper::findOrCreate($manager, Translation::class, [
                    'translationKey' => $key,
                    'locale' => $locale,
                ]);
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('dashboard_agence');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
