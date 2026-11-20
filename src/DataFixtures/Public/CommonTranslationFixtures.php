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

namespace App\DataFixtures\Public;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class CommonTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'common.google_login' => 'Connexion via Google',
                'common.meta.default_title' => 'Bienvenue !',
                'common.pagination.aria_label' => 'Pagination',
                'common.pagination.listings_aria_label' => 'Pagination des annonces',
                'common.pagination.previous_page' => 'Page précédente',
                'common.pagination.next_page' => 'Page suivante',
                'maintenance.meta.title' => 'Boolts — Maintenance',
                'maintenance.logo_aria_label' => 'Boolts — L’immobilier. Réinventé.',
                'maintenance.title' => 'Le site est en cours de maintenance.',
                'maintenance.text' => 'Nous travaillons à l’améliorer afin de vous offrir une expérience toujours plus performante.',
                'maintenance.thanks' => 'Merci de votre patience.',
            ],
            'en' => [
                'common.google_login' => 'Sign in with Google',
                'common.meta.default_title' => 'Welcome!',
                'common.pagination.aria_label' => 'Pagination',
                'common.pagination.listings_aria_label' => 'Listings pagination',
                'common.pagination.previous_page' => 'Previous page',
                'common.pagination.next_page' => 'Next page',
                'maintenance.meta.title' => 'Boolts — Maintenance',
                'maintenance.logo_aria_label' => 'Boolts — Real estate. Reinvented.',
                'maintenance.title' => 'The site is currently under maintenance.',
                'maintenance.text' => 'We are working to improve it in order to offer you an ever better experience.',
                'maintenance.thanks' => 'Thank you for your patience.',
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
                $translation->setPage('common');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
