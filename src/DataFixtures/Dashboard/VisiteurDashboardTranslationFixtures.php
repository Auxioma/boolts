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

final class VisiteurDashboardTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visitor.favorites.title' => 'Mes favoris',
                'visitor.favorites.empty' => 'Vous n’avez encore aucun bien en favoris.',
                'visitor.favorites.login_required' => 'Connectez-vous pour consulter vos favoris.',
                'messaging.tab.listing' => 'Annonce',
                'messaging.tab.agencies' => 'Agences',
                'dashboard.more' => 'Plus',
                'dashboard.bottom_bar.aria_label' => 'Navigation du dashboard',
            ],
            'en' => [
                'visitor.favorites.title' => 'My favorites',
                'visitor.favorites.empty' => 'You don’t have any favorite properties yet.',
                'visitor.favorites.login_required' => 'Sign in to view your favorites.',
                'messaging.tab.listing' => 'Listing',
                'messaging.tab.agencies' => 'Agencies',
                'dashboard.more' => 'More',
                'dashboard.bottom_bar.aria_label' => 'Dashboard navigation',
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
                $translation->setPage('dashboard_visiteur');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
