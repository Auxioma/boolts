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

final class FormTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'form.code.not_blank' => 'Veuillez saisir le code reçu par e-mail.',
                'form.code.exact_length' => 'Le code doit contenir exactement 6 chiffres.',
                'form.code.digits_only' => 'Le code doit contenir uniquement 6 chiffres.',
                'form.email.label' => 'Adresse e-mail',
                'form.email.not_blank' => 'Veuillez saisir votre adresse e-mail.',
                'form.email.invalid' => 'Adresse e-mail incorrecte.',
                'form.email.placeholder' => 'Veuillez entrer votre adresse e-mail',
                'form.password.placeholder' => 'Veuillez entrer votre mot de passe',
                'form.password.confirm_placeholder' => 'Confirmez votre mot de passe',
                'form.last_name.placeholder' => 'Veuillez entrer votre nom',
                'form.first_name.placeholder' => 'Veuillez entrer votre prénom',
                'form.phone.placeholder' => 'Veuillez entrer votre numéro de téléphone',
                'form.password.error.uppercase' => 'Le mot de passe doit contenir au moins une majuscule.',
                'form.password.error.lowercase' => 'Le mot de passe doit contenir au moins une minuscule.',
                'form.password.error.digit' => 'Le mot de passe doit contenir au moins un chiffre.',
                'form.agency_description.placeholder' => 'Veuillez saisir une description de votre agence',
                'form.contact_email.placeholder' => 'Veuillez entrer votre adresse e-mail de contact',
                'form.address.street.placeholder' => 'Entrez un numéro et un nom de rue',
                'form.address.complement.placeholder' => 'Entrez un complément d’adresse',
                'form.address.postal_code.placeholder' => 'Entrez un code postal',
                'form.address.city.placeholder' => 'Choisissez une ville',
                'form.address.country.placeholder' => 'Choisissez un pays',
                'form.terms.required' => 'Veuillez accepter les conditions d’utilisation.',
                'form.phone.label' => 'Téléphone',
                'form.profile_picture.label' => 'Photo de profil',
                'form.language.placeholder' => 'Sélectionner une langue',
                'form.currency.placeholder' => 'Sélectionner une devise',
                'form.language.search' => 'Rechercher une langue…',
                'form.country.placeholder' => 'Sélectionnez votre pays',
                'form.last_name.label' => 'Nom',
                'form.first_name.label' => 'Prénom',
                'form.email.short_label' => 'E-mail',
                'form.message.label' => 'Message',
                'form.last_name.not_blank_placeholder' => 'Veuillez saisir votre nom',
                'form.first_name.not_blank_placeholder' => 'Veuillez saisir votre prénom',
                'form.email.short_placeholder' => 'Veuillez saisir votre e-mail',
                'form.message.placeholder' => 'Veuillez saisir votre message',
                'form.password.error.min' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                'form.password.error.max' => 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.',
            ],
            'en' => [
                'form.code.not_blank' => 'Please enter the code received by email.',
                'form.code.exact_length' => 'The code must contain exactly 6 digits.',
                'form.code.digits_only' => 'The code must contain only 6 digits.',
                'form.email.label' => 'Email address',
                'form.email.not_blank' => 'Please enter your email address.',
                'form.email.invalid' => 'Invalid email address.',
                'form.email.placeholder' => 'Please enter your email address',
                'form.password.placeholder' => 'Please enter your password',
                'form.password.confirm_placeholder' => 'Confirm your password',
                'form.last_name.placeholder' => 'Please enter your last name',
                'form.first_name.placeholder' => 'Please enter your first name',
                'form.phone.placeholder' => 'Please enter your phone number',
                'form.password.error.uppercase' => 'The password must contain at least one uppercase letter.',
                'form.password.error.lowercase' => 'The password must contain at least one lowercase letter.',
                'form.password.error.digit' => 'The password must contain at least one digit.',
                'form.agency_description.placeholder' => 'Please enter a description of your agency',
                'form.contact_email.placeholder' => 'Please enter your contact email address',
                'form.address.street.placeholder' => 'Enter a street number and name',
                'form.address.complement.placeholder' => 'Enter additional address details',
                'form.address.postal_code.placeholder' => 'Enter a postal code',
                'form.address.city.placeholder' => 'Choose a city',
                'form.address.country.placeholder' => 'Choose a country',
                'form.terms.required' => 'Please accept the terms of use.',
                'form.phone.label' => 'Phone',
                'form.profile_picture.label' => 'Profile picture',
                'form.language.placeholder' => 'Select a language',
                'form.currency.placeholder' => 'Select a currency',
                'form.language.search' => 'Search for a language...',
                'form.country.placeholder' => 'Select your country',
                'form.last_name.label' => 'Last name',
                'form.first_name.label' => 'First name',
                'form.email.short_label' => 'Email',
                'form.message.label' => 'Message',
                'form.last_name.not_blank_placeholder' => 'Please enter your last name',
                'form.first_name.not_blank_placeholder' => 'Please enter your first name',
                'form.email.short_placeholder' => 'Please enter your email',
                'form.message.placeholder' => 'Please enter your message',
                'form.password.error.min' => 'The password must contain at least {{ limit }} characters.',
                'form.password.error.max' => 'The password cannot exceed {{ limit }} characters.',
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
                $translation->setPage('forms');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
