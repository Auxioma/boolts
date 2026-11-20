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

final class AuthCommonTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'auth.hero.aria_label' => 'Vue d\'une propriété au bord de la mer',
                'auth.password.show' => 'Afficher le mot de passe',
                'auth.password_strength.progress' => '{count} / {min} caractères',
                'auth.password_strength.valid' => 'Longueur minimale atteinte',
                'auth.password_strength.mismatch' => 'Les mots de passe ne correspondent pas.',
                'auth.social.apple' => 'Apple',
                'auth.social.google' => 'Google',
                'auth.or' => 'OU',
                'auth.login.not_registered' => 'Pas encore inscrit sur Boolts ?',
                'auth.login.register' => 'S’inscrire',
                'auth.registration_choice.meta.title' => 'Inscrivez-vous gratuitement | Boolts',
                'auth.registration_choice.title' => 'Inscrivez-vous gratuitement',
                'auth.registration_choice.subtitle' => 'Choisissez votre profil :',
                'auth.registration_choice.individual' => 'Particulier',
                'auth.registration_choice.professional' => 'Professionnel',
                'auth.registration_choice.already_registered' => 'Déjà inscrit sur Boolts ?',
                'auth.registration.meta.title' => 'Inscription | Boolts',
                'auth.registration.title.professional' => 'Créer un compte professionnel',
                'auth.registration.title.individual' => 'Créer un compte particulier',
                'auth.registration.email.label' => 'Inscription via adresse e-mail*',
                'professionnel.register.step3.title' => 'Remplissez les informations de votre compte',
                'professionnel.register.step3.phone' => 'Numéro de téléphone',
                'professionnel.register.step4.title' => 'Quelques informations à propos de votre entreprise',
                'professionnel.register.step4.company' => 'Nom légal de votre entreprise*',
                'professionnel.register.step4.address' => 'Adresse légale de votre entreprise*',
                'professionnel.register.step4.address2' => 'Adresse 2 (facultatif)',
                'professionnel.register.step4.postal_code' => 'Code postal*',
                'professionnel.register.step4.city' => 'Ville*',
                'professionnel.register.step4.country' => 'Pays*',
                'professionnel.register.step4.info' => '*Informations telles qu\'affichées sur vos documents officiels',
                'professionnel.register.next' => 'Suivant',
                'professionnel.register.step5.title' => 'Remplissez la fiche publique de votre agence',
                'professionnel.register.step5.photo' => 'Photo de profil (facultatif)',
                'professionnel.register.step5.photo_edit' => 'Modifier',
                'professionnel.register.step5.description' => 'Description (facultatif)',
                'professionnel.register.step5.phone' => 'Numéro de téléphone de contact (facultatif)',
                'professionnel.register.step5.email' => 'Adresse e-mail de contact (facultatif)',
                'professionnel.register.step5.languages' => 'Langues parlées par l\'agence (facultatif)',
                'professionnel.register.step5.languages_search' => 'Rechercher une langue...',
                'professionnel.register.step6.title' => 'Remplissez les<br>derniers détails...',
                'professionnel.register.step6.hours' => 'Horaires de l\'agence (facultatif)',
                'professionnel.register.step6.submit' => 'S’inscrire',
                'common.days.monday' => 'Lundi',
                'common.days.tuesday' => 'Mardi',
                'common.days.wednesday' => 'Mercredi',
                'common.days.thursday' => 'Jeudi',
                'common.days.friday' => 'Vendredi',
                'common.days.saturday' => 'Samedi',
                'common.days.sunday' => 'Dimanche',
                'professionnel.register.progress.general' => 'Informations générales',
                'professionnel.register.progress.legal' => 'Informations légales',
                'professionnel.register.progress.details' => 'Détails de l’agence',
                'professionnel.register.progress.hours' => 'Horaires de l’agence',
                'professionnel.register.progress.details_1' => 'Détails de l’agence 1/2',
                'professionnel.register.progress.details_2' => 'Détails de l’agence 2/2',
                'auth.two_factor.meta.title' => 'Vérification en deux étapes',
                'auth.two_factor.welcome' => 'Bon retour parmi nous',
                'auth.two_factor.intro' => 'Veuillez confirmer l’accès à votre compte en saisissant l’un de vos codes de récupération d’urgence.',
                'auth.two_factor.recovery_code' => 'Code de récupération',
                'auth.two_factor.use_auth_code' => 'Utiliser un code d’authentification',
            ],
            'en' => [
                'auth.hero.aria_label' => 'View of a seaside property',
                'auth.password.show' => 'Show password',
                'auth.password_strength.progress' => '{count} / {min} characters',
                'auth.password_strength.valid' => 'Minimum length reached',
                'auth.password_strength.mismatch' => 'Passwords do not match.',
                'auth.social.apple' => 'Apple',
                'auth.social.google' => 'Google',
                'auth.or' => 'OR',
                'auth.login.not_registered' => 'Not registered on Boolts yet?',
                'auth.login.register' => 'Sign up',
                'auth.registration_choice.meta.title' => 'Sign up for free | Boolts',
                'auth.registration_choice.title' => 'Sign up for free',
                'auth.registration_choice.subtitle' => 'Choose your profile:',
                'auth.registration_choice.individual' => 'Individual',
                'auth.registration_choice.professional' => 'Professional',
                'auth.registration_choice.already_registered' => 'Already registered on Boolts?',
                'auth.registration.meta.title' => 'Sign up | Boolts',
                'auth.registration.title.professional' => 'Create a professional account',
                'auth.registration.title.individual' => 'Create an individual account',
                'auth.registration.email.label' => 'Sign up with email address*',
                'professionnel.register.step3.title' => 'Fill in your account information',
                'professionnel.register.step3.phone' => 'Phone number',
                'professionnel.register.step4.title' => 'A few details about your company',
                'professionnel.register.step4.company' => 'Legal name of your company*',
                'professionnel.register.step4.address' => 'Legal address of your company*',
                'professionnel.register.step4.address2' => 'Address line 2 (optional)',
                'professionnel.register.step4.postal_code' => 'Postal code*',
                'professionnel.register.step4.city' => 'City*',
                'professionnel.register.step4.country' => 'Country*',
                'professionnel.register.step4.info' => '*Information as shown on your official documents',
                'professionnel.register.next' => 'Next',
                'professionnel.register.step5.title' => 'Fill in your agency’s public profile',
                'professionnel.register.step5.photo' => 'Profile picture (optional)',
                'professionnel.register.step5.photo_edit' => 'Edit',
                'professionnel.register.step5.description' => 'Description (optional)',
                'professionnel.register.step5.phone' => 'Contact phone number (optional)',
                'professionnel.register.step5.email' => 'Contact email address (optional)',
                'professionnel.register.step5.languages' => 'Languages spoken by the agency (optional)',
                'professionnel.register.step5.languages_search' => 'Search for a language...',
                'professionnel.register.step6.title' => 'Fill in the<br>final details...',
                'professionnel.register.step6.hours' => 'Agency opening hours (optional)',
                'professionnel.register.step6.submit' => 'Sign up',
                'common.days.monday' => 'Monday',
                'common.days.tuesday' => 'Tuesday',
                'common.days.wednesday' => 'Wednesday',
                'common.days.thursday' => 'Thursday',
                'common.days.friday' => 'Friday',
                'common.days.saturday' => 'Saturday',
                'common.days.sunday' => 'Sunday',
                'professionnel.register.progress.general' => 'General information',
                'professionnel.register.progress.legal' => 'Legal information',
                'professionnel.register.progress.details' => 'Agency details',
                'professionnel.register.progress.hours' => 'Agency opening hours',
                'professionnel.register.progress.details_1' => 'Agency details 1/2',
                'professionnel.register.progress.details_2' => 'Agency details 2/2',
                'auth.two_factor.meta.title' => 'Two Step Verification',
                'auth.two_factor.welcome' => 'Welcome Back',
                'auth.two_factor.intro' => 'Please confirm access to your account by entering one of your emergency recovery codes.',
                'auth.two_factor.recovery_code' => 'Recovery Code',
                'auth.two_factor.use_auth_code' => 'Use an authentication code',
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
                $translation->setPage('authentification');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
