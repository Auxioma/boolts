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

final class SettingsTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'settings.menu.account' => 'Gestion du compte',
                'settings.menu.agency_profile' => 'Fiche agence',
                'settings.menu.personal_info' => 'Informations personnelles',
                'settings.menu.security_payment' => 'Sécurité et paiement',
                'settings.menu.login_security' => 'Connexion et sécurité',
                'settings.menu.payment_method' => 'Moyen de paiement',
                'settings.menu.payment_history' => 'Historique des paiements',
                'settings.menu.language_currency' => 'Langue et devise',
                'settings.meta.title' => 'Paramètres',
                'settings.meta.agency_title' => 'Paramètres agence immobilière',
                'settings.back_to_menu' => 'Retour au menu Paramètres',
                'settings.edit' => 'Modifier',
                'settings.define' => 'Définir',
                'settings.security.login' => 'Connexion',
                'settings.security.password' => 'Mot de passe',
                'settings.security.two_factor' => 'Authentification à deux facteurs',
                'settings.security.email' => 'Adresse e-mail',
                'settings.security.delete_account' => 'Supprimer le compte',
                'settings.security.delete_account_confirm' => 'Voulez-vous vraiment supprimer votre compte ? Cette action est définitive.',
                'settings.personal.phone' => 'Numéro de téléphone',
                'settings.personal.avatar_alt' => 'avatar',
                'settings.personal.firstname_required' => 'Prénom*',
                'settings.personal.lastname_required' => 'Nom*',
                'settings.personal.email_required' => 'E-mail*',
                'settings.personal.firstname' => 'Prénom',
                'settings.personal.lastname' => 'Nom',
                'settings.personal.email' => 'E-mail',
                'settings.personal.address' => 'Adresse',
                'settings.personal.address_define' => 'Définissez une adresse',
                'settings.personal.address2' => 'Adresse 2 (optionnel)',
                'settings.personal.postal_code' => 'Code postal',
                'settings.personal.city' => 'Ville',
                'settings.personal.country_required' => 'Pays*',
                'settings.address.street_placeholder' => 'Entrez un numéro et un nom de rue',
                'settings.address.complement_placeholder' => 'Entrez un complément d’adresse',
                'settings.address.postal_code_placeholder' => 'Entrez un code postal',
                'settings.address.city_placeholder' => 'Choisissez une ville',
                'settings.address.country_placeholder' => 'France',
                'settings.locale.language' => 'Langue préférée',
                'settings.locale.language_search' => 'Rechercher une langue...',
                'settings.locale.currency' => 'Devise préférée',
                'settings.locale.currency_search' => 'Rechercher une devise...',
                'settings.locale.timezone' => 'Fuseau horaire',
                'settings.payments.view' => 'Voir',
                'settings.payments.download' => 'Télécharger',
                'settings.payments.empty' => 'Aucun paiement d’abonnement. Les renouvellements Stripe apparaîtront ici après synchronisation.',
                'settings.payments.card' => 'Carte bancaire',
            ],
            'en' => [
                'settings.menu.account' => 'Account management',
                'settings.menu.agency_profile' => 'Agency profile',
                'settings.menu.personal_info' => 'Personal information',
                'settings.menu.security_payment' => 'Security and payment',
                'settings.menu.login_security' => 'Login and security',
                'settings.menu.payment_method' => 'Payment method',
                'settings.menu.payment_history' => 'Payment history',
                'settings.menu.language_currency' => 'Language and currency',
                'settings.meta.title' => 'Settings',
                'settings.meta.agency_title' => 'Real estate agency settings',
                'settings.back_to_menu' => 'Back to Settings menu',
                'settings.edit' => 'Edit',
                'settings.define' => 'Set',
                'settings.security.login' => 'Login',
                'settings.security.password' => 'Password',
                'settings.security.two_factor' => 'Two-factor authentication',
                'settings.security.email' => 'Email address',
                'settings.security.delete_account' => 'Delete account',
                'settings.security.delete_account_confirm' => 'Do you really want to delete your account? This action is permanent.',
                'settings.personal.phone' => 'Phone number',
                'settings.personal.avatar_alt' => 'avatar',
                'settings.personal.firstname_required' => 'First name*',
                'settings.personal.lastname_required' => 'Last name*',
                'settings.personal.email_required' => 'Email*',
                'settings.personal.firstname' => 'First name',
                'settings.personal.lastname' => 'Last name',
                'settings.personal.email' => 'Email',
                'settings.personal.address' => 'Address',
                'settings.personal.address_define' => 'Set an address',
                'settings.personal.address2' => 'Address line 2 (optional)',
                'settings.personal.postal_code' => 'Postal code',
                'settings.personal.city' => 'City',
                'settings.personal.country_required' => 'Country*',
                'settings.address.street_placeholder' => 'Enter a street number and name',
                'settings.address.complement_placeholder' => 'Enter additional address details',
                'settings.address.postal_code_placeholder' => 'Enter a postal code',
                'settings.address.city_placeholder' => 'Choose a city',
                'settings.address.country_placeholder' => 'France',
                'settings.locale.language' => 'Preferred language',
                'settings.locale.language_search' => 'Search for a language...',
                'settings.locale.currency' => 'Preferred currency',
                'settings.locale.currency_search' => 'Search for a currency...',
                'settings.locale.timezone' => 'Time zone',
                'settings.payments.view' => 'View',
                'settings.payments.download' => 'Download',
                'settings.payments.empty' => 'No subscription payments. Stripe renewals will appear here after synchronization.',
                'settings.payments.card' => 'Bank card',
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
                $translation->setPage('settings');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
