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

final class NavbarTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'navbar.mobile.aria_label' => 'Ouvrir le menu',
                'navbar.buy' => 'Acheter',
                'navbar.rent' => 'Louer',
                'navbar.sell' => 'Vendre',
                'navbar.find_professional' => 'Trouver un pro',
                'navbar.exclusives' => 'Exclusivités',
                'navbar.submit_listing' => 'Déposer une annonce',
                'navbar.login.aria_label' => 'Se connecter',
                'navbar.account.aria_label' => 'Accéder à mon compte',
                'navbar.profile.alt' => 'Profil utilisateur',
                'navbar.mobile.navigation.aria_label' => 'Navigation mobile',
                'navbar.mobile.explore' => 'Explorer',
                'navbar.mobile.favorites' => 'Favoris',
                'navbar.mobile.messaging' => 'Messagerie',
                'navbar.mobile.login' => 'Connexion',
            ],
            'en' => [
                'navbar.mobile.aria_label' => 'Open menu',
                'navbar.buy' => 'Buy',
                'navbar.rent' => 'Rent',
                'navbar.sell' => 'Sell',
                'navbar.find_professional' => 'Find a professional',
                'navbar.exclusives' => 'Exclusives',
                'navbar.submit_listing' => 'Post a listing',
                'navbar.login.aria_label' => 'Sign in',
                'navbar.account.aria_label' => 'Access my account',
                'navbar.profile.alt' => 'User profile',
                'navbar.mobile.navigation.aria_label' => 'Mobile navigation',
                'navbar.mobile.explore' => 'Explore',
                'navbar.mobile.favorites' => 'Favorites',
                'navbar.mobile.messaging' => 'Messages',
                'navbar.mobile.login' => 'Sign in',
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
                $translation->setPage('navbar');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
