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
            'es' => [
                'common.google_login' => 'Iniciar sesión con Google',
                'common.meta.default_title' => '¡Bienvenido!',
                'common.pagination.aria_label' => 'Paginación',
                'common.pagination.listings_aria_label' => 'Paginación de los anuncios',
                'common.pagination.previous_page' => 'Página anterior',
                'common.pagination.next_page' => 'Página siguiente',
                'maintenance.meta.title' => 'Boolts — Mantenimiento',
                'maintenance.logo_aria_label' => 'Boolts — El sector inmobiliario. Reinventado.',
                'maintenance.title' => 'El sitio está en mantenimiento.',
                'maintenance.text' => 'Estamos trabajando para mejorarlo y ofrecerte una experiencia cada vez mejor.',
                'maintenance.thanks' => 'Gracias por tu paciencia.',
            ],
            'de' => [
                'common.google_login' => 'Mit Google anmelden',
                'common.meta.default_title' => 'Willkommen!',
                'common.pagination.aria_label' => 'Seitennummerierung',
                'common.pagination.listings_aria_label' => 'Seitennummerierung der Anzeigen',
                'common.pagination.previous_page' => 'Vorherige Seite',
                'common.pagination.next_page' => 'Nächste Seite',
                'maintenance.meta.title' => 'Boolts — Wartung',
                'maintenance.logo_aria_label' => 'Boolts — Immobilien. Neu gedacht.',
                'maintenance.title' => 'Die Website wird gerade gewartet.',
                'maintenance.text' => 'Wir arbeiten daran, sie zu verbessern, um Ihnen ein noch besseres Erlebnis zu bieten.',
                'maintenance.thanks' => 'Vielen Dank für Ihre Geduld.',
            ],
            'pt' => [
                'common.google_login' => 'Iniciar sessão com a Google',
                'common.meta.default_title' => 'Bem-vindo!',
                'common.pagination.aria_label' => 'Paginação',
                'common.pagination.listings_aria_label' => 'Paginação dos anúncios',
                'common.pagination.previous_page' => 'Página anterior',
                'common.pagination.next_page' => 'Página seguinte',
                'maintenance.meta.title' => 'Boolts — Manutenção',
                'maintenance.logo_aria_label' => 'Boolts — O imobiliário. Reinventado.',
                'maintenance.title' => 'O site está em manutenção.',
                'maintenance.text' => 'Estamos a trabalhar para o melhorar e oferecer-lhe uma experiência cada vez melhor.',
                'maintenance.thanks' => 'Obrigado pela sua paciência.',
            ],
            'it' => [
                'common.google_login' => 'Accedi con Google',
                'common.meta.default_title' => 'Benvenuto!',
                'common.pagination.aria_label' => 'Paginazione',
                'common.pagination.listings_aria_label' => 'Paginazione degli annunci',
                'common.pagination.previous_page' => 'Pagina precedente',
                'common.pagination.next_page' => 'Pagina successiva',
                'maintenance.meta.title' => 'Boolts — Manutenzione',
                'maintenance.logo_aria_label' => 'Boolts — Il settore immobiliare. Reinventato.',
                'maintenance.title' => 'Il sito è in manutenzione.',
                'maintenance.text' => 'Stiamo lavorando per migliorarlo e offrirti un’esperienza sempre più performante.',
                'maintenance.thanks' => 'Grazie per la pazienza.',
            ],
            'nl' => [
                'common.google_login' => 'Inloggen met Google',
                'common.meta.default_title' => 'Welkom!',
                'common.pagination.aria_label' => 'Paginering',
                'common.pagination.listings_aria_label' => 'Paginering van advertenties',
                'common.pagination.previous_page' => 'Vorige pagina',
                'common.pagination.next_page' => 'Volgende pagina',
                'maintenance.meta.title' => 'Boolts — Onderhoud',
                'maintenance.logo_aria_label' => 'Boolts — Vastgoed. Opnieuw uitgevonden.',
                'maintenance.title' => 'De website is in onderhoud.',
                'maintenance.text' => 'We werken eraan om hem te verbeteren en je een steeds betere ervaring te bieden.',
                'maintenance.thanks' => 'Bedankt voor je geduld.',
            ],
            'hu' => [
                'common.google_login' => 'Bejelentkezés Google-fiókkal',
                'common.meta.default_title' => 'Üdvözöljük!',
                'common.pagination.aria_label' => 'Lapozás',
                'common.pagination.listings_aria_label' => 'Hirdetések lapozása',
                'common.pagination.previous_page' => 'Előző oldal',
                'common.pagination.next_page' => 'Következő oldal',
                'maintenance.meta.title' => 'Boolts — Karbantartás',
                'maintenance.logo_aria_label' => 'Boolts — Az ingatlanpiac. Újragondolva.',
                'maintenance.title' => 'Az oldal karbantartás alatt áll.',
                'maintenance.text' => 'Dolgozunk a fejlesztésén, hogy egyre jobb élményt nyújthassunk.',
                'maintenance.thanks' => 'Köszönjük türelmét.',
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
