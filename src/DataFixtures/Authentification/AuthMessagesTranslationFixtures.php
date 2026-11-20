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

namespace App\DataFixtures\Authentification;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AuthMessagesTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'auth.flash.session_expired' => 'Session expirée. Veuillez recommencer.',
                'auth.flash.user_not_found' => 'Utilisateur introuvable.',
                'auth.flash.too_many_attempts' => 'Trop de tentatives. Veuillez demander un nouveau code.',
                'auth.flash.code_expired' => 'Le code a expiré. Demandez un nouveau code.',
                'auth.flash.code_invalid' => 'Le code saisi est invalide.',
                'auth.flash.resume_code_sent' => 'Un code de vérification vient de vous être envoyé pour reprendre votre inscription.',
                'auth.flash.account_exists' => 'Un compte existe déjà avec cette adresse. Connectez-vous ici.',
                'auth.flash.no_account' => 'Aucun compte trouvé avec cette adresse e-mail.',
                'auth.flash.no_registration' => 'Aucune inscription en cours.',
                'auth.flash.session_expired_long' => 'Votre session a expiré. Veuillez recommencer.',
                'auth.flash.wait_new_code' => 'Veuillez patienter 60 secondes avant de demander un nouveau code.',
                'auth.flash.new_code_sent' => 'Un nouveau code vous a été envoyé.',
                'contact.flash.sent' => 'Votre message a été envoyé avec succès !',
                'messaging.flash.individuals_only' => 'La messagerie avec les agences est réservée aux comptes particuliers.',
                'search.flash.select_type_country' => 'Veuillez sélectionner un type de transaction et un pays.',
                'search.error.not_found' => 'Cette recherche est introuvable ou expirée.',
                'search.error.invalid_bounds' => 'Coordonnées de carte invalides.',
                'home.error.invalid_city' => 'Ville invalide.',
            ],
            'en' => [
                'auth.flash.session_expired' => 'Session expired. Please start again.',
                'auth.flash.user_not_found' => 'User not found.',
                'auth.flash.too_many_attempts' => 'Too many attempts. Please request a new code.',
                'auth.flash.code_expired' => 'The code has expired. Request a new code.',
                'auth.flash.code_invalid' => 'The code entered is invalid.',
                'auth.flash.resume_code_sent' => 'A verification code has just been sent to you to resume your registration.',
                'auth.flash.account_exists' => 'An account already exists with this address. Sign in here.',
                'auth.flash.no_account' => 'No account found with this email address.',
                'auth.flash.no_registration' => 'No registration in progress.',
                'auth.flash.session_expired_long' => 'Your session has expired. Please start again.',
                'auth.flash.wait_new_code' => 'Please wait 60 seconds before requesting a new code.',
                'auth.flash.new_code_sent' => 'A new code has been sent to you.',
                'contact.flash.sent' => 'Your message has been sent successfully!',
                'messaging.flash.individuals_only' => 'Messaging with agencies is reserved for individual accounts.',
                'search.flash.select_type_country' => 'Please select a transaction type and a country.',
                'search.error.not_found' => 'This search could not be found or has expired.',
                'search.error.invalid_bounds' => 'Invalid map coordinates.',
                'home.error.invalid_city' => 'Invalid city.',
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
                $translation->setPage('auth_messages');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
