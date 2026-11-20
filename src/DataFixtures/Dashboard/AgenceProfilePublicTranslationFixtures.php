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

final class AgenceProfilePublicTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency_profile.todo.photo' => 'Ajouter une photo de profil',
                'agency_profile.todo.description' => 'Ajouter une description',
                'agency_profile.todo.email' => 'Ajouter une adresse mail de contact',
                'agency_profile.todo.hours' => 'Ajouter vos horaires',
                'agency_profile.todo.phone' => 'Ajouter un numéro de contact',
                'agency_profile.todo.languages' => 'Ajouter vos langues parlées',
                'agency_profile.view_public' => 'Voir la fiche publique',
                'agency_profile.complete' => 'Complétez votre profil pour pouvoir publier des annonces',
                'agency_profile.optional' => '(optionnel)',
                'agency_profile.name_placeholder' => 'Définissez le nom de votre agence',
                'agency_profile.description_placeholder' => 'Rédigez une courte description ici.',
                'agency_profile.address' => 'Adresse de l’agence',
                'agency_profile.address_placeholder' => 'Saisissez votre adresse postale',
                'agency_profile.phone_placeholder' => 'Définissez un numéro de contact',
                'agency_profile.whatsapp' => 'Whatsapp',
                'agency_profile.same_number' => 'Utiliser le même numéro',
                'agency_profile.email_placeholder' => 'Définissez une adresse e-mail de contact',
                'agency_profile.languages' => 'Langues parlées par l’agence',
                'agency_profile.languages_placeholder' => 'Définissez les langues parlées par l’agence',
                'agency_profile.hours' => 'Horaires',
                'common.hours.closed_lower' => 'fermé',
                'common.hours.open_lower' => 'ouvert',
                'common.and' => 'et',
                'agency_profile.hours_default' => 'Tous les jours sont fermés par défaut',
            ],
            'en' => [
                'agency_profile.todo.photo' => 'Add a profile picture',
                'agency_profile.todo.description' => 'Add a description',
                'agency_profile.todo.email' => 'Add a contact email address',
                'agency_profile.todo.hours' => 'Add your opening hours',
                'agency_profile.todo.phone' => 'Add a contact number',
                'agency_profile.todo.languages' => 'Add the languages you speak',
                'agency_profile.view_public' => 'View public profile',
                'agency_profile.complete' => 'Complete your profile to be able to publish listings',
                'agency_profile.optional' => '(optional)',
                'agency_profile.name_placeholder' => 'Set your agency name',
                'agency_profile.description_placeholder' => 'Write a short description here.',
                'agency_profile.address' => 'Agency address',
                'agency_profile.address_placeholder' => 'Enter your postal address',
                'agency_profile.phone_placeholder' => 'Set a contact number',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Use the same number',
                'agency_profile.email_placeholder' => 'Set a contact email address',
                'agency_profile.languages' => 'Languages spoken by the agency',
                'agency_profile.languages_placeholder' => 'Set the languages spoken by the agency',
                'agency_profile.hours' => 'Opening hours',
                'common.hours.closed_lower' => 'closed',
                'common.hours.open_lower' => 'open',
                'common.and' => 'and',
                'agency_profile.hours_default' => 'All days are closed by default',
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
                $translation->setPage('profile_public');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
