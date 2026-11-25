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

namespace App\DataFixtures\Authentification\Visiteurs;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VisiteurRegisterProfileTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.register.profile.meta.title' => 'Bienvenue sur Boolts !',
                'visiteur.register.profile.title' => 'Bienvenue sur Boolts !',
                'visiteur.register.profile.lastname.label' => 'Nom*',
                'visiteur.register.profile.firstname.label' => 'Prénom*',
                'visiteur.register.profile.password.label' => 'Mot de passe*',
                'visiteur.register.profile.password.help' => 'Minimum 12 caractères, avec au moins une majuscule, une minuscule et un chiffre.',
                'visiteur.register.profile.password_confirm.label' => 'Confirmer le mot de passe*',
                'visiteur.register.profile.terms.label' => 'J’accepte les conditions d’utilisation de Boolts.*',
                'visiteur.register.profile.submit' => 'S’inscrire',
            ],

            'en' => [
                'visiteur.register.profile.meta.title' => 'Welcome to Boolts!',
                'visiteur.register.profile.title' => 'Welcome to Boolts!',
                'visiteur.register.profile.lastname.label' => 'Last name*',
                'visiteur.register.profile.firstname.label' => 'First name*',
                'visiteur.register.profile.password.label' => 'Password*',
                'visiteur.register.profile.password.help' => 'Minimum 12 characters, including at least one uppercase letter, one lowercase letter and one digit.',
                'visiteur.register.profile.password_confirm.label' => 'Confirm password*',
                'visiteur.register.profile.terms.label' => 'I accept the Boolts terms of use.*',
                'visiteur.register.profile.submit' => 'Register',
            ],
            'es' => [
                'visiteur.register.profile.meta.title' => '¡Bienvenido a Boolts!',
                'visiteur.register.profile.title' => '¡Bienvenido a Boolts!',
                'visiteur.register.profile.lastname.label' => 'Apellido*',
                'visiteur.register.profile.firstname.label' => 'Nombre*',
                'visiteur.register.profile.password.label' => 'Contraseña*',
                'visiteur.register.profile.password.help' => 'Mínimo 12 caracteres, con al menos una mayúscula, una minúscula y un número.',
                'visiteur.register.profile.password_confirm.label' => 'Confirmar contraseña*',
                'visiteur.register.profile.terms.label' => 'Acepto las condiciones de uso de Boolts.*',
                'visiteur.register.profile.submit' => 'Registrarse',
            ],
            'de' => [
                'visiteur.register.profile.meta.title' => 'Willkommen bei Boolts!',
                'visiteur.register.profile.title' => 'Willkommen bei Boolts!',
                'visiteur.register.profile.lastname.label' => 'Nachname*',
                'visiteur.register.profile.firstname.label' => 'Vorname*',
                'visiteur.register.profile.password.label' => 'Passwort*',
                'visiteur.register.profile.password.help' => 'Mindestens 12 Zeichen, darunter mindestens ein Großbuchstabe, ein Kleinbuchstabe und eine Ziffer.',
                'visiteur.register.profile.password_confirm.label' => 'Passwort bestätigen*',
                'visiteur.register.profile.terms.label' => 'Ich akzeptiere die Nutzungsbedingungen von Boolts.*',
                'visiteur.register.profile.submit' => 'Registrieren',
            ],
            'pt' => [
                'visiteur.register.profile.meta.title' => 'Bem-vindo à Boolts!',
                'visiteur.register.profile.title' => 'Bem-vindo à Boolts!',
                'visiteur.register.profile.lastname.label' => 'Apelido*',
                'visiteur.register.profile.firstname.label' => 'Nome próprio*',
                'visiteur.register.profile.password.label' => 'Palavra-passe*',
                'visiteur.register.profile.password.help' => 'Mínimo 12 caracteres, com pelo menos uma maiúscula, uma minúscula e um algarismo.',
                'visiteur.register.profile.password_confirm.label' => 'Confirmar palavra-passe*',
                'visiteur.register.profile.terms.label' => 'Aceito os termos de utilização da Boolts.*',
                'visiteur.register.profile.submit' => 'Registar-se',
            ],
            'it' => [
                'visiteur.register.profile.meta.title' => 'Benvenuto su Boolts!',
                'visiteur.register.profile.title' => 'Benvenuto su Boolts!',
                'visiteur.register.profile.lastname.label' => 'Cognome*',
                'visiteur.register.profile.firstname.label' => 'Nome*',
                'visiteur.register.profile.password.label' => 'Password*',
                'visiteur.register.profile.password.help' => 'Minimo 12 caratteri, con almeno una lettera maiuscola, una minuscola e una cifra.',
                'visiteur.register.profile.password_confirm.label' => 'Conferma password*',
                'visiteur.register.profile.terms.label' => 'Accetto le condizioni d’uso di Boolts.*',
                'visiteur.register.profile.submit' => 'Iscriviti',
            ],
            'nl' => [
                'visiteur.register.profile.meta.title' => 'Welkom bij Boolts!',
                'visiteur.register.profile.title' => 'Welkom bij Boolts!',
                'visiteur.register.profile.lastname.label' => 'Achternaam*',
                'visiteur.register.profile.firstname.label' => 'Voornaam*',
                'visiteur.register.profile.password.label' => 'Wachtwoord*',
                'visiteur.register.profile.password.help' => 'Minimaal 12 tekens, met ten minste één hoofdletter, één kleine letter en één cijfer.',
                'visiteur.register.profile.password_confirm.label' => 'Wachtwoord bevestigen*',
                'visiteur.register.profile.terms.label' => 'Ik ga akkoord met de gebruiksvoorwaarden van Boolts.*',
                'visiteur.register.profile.submit' => 'Registreren',
            ],
            'hu' => [
                'visiteur.register.profile.meta.title' => 'Üdvözöljük a Booltson!',
                'visiteur.register.profile.title' => 'Üdvözöljük a Booltson!',
                'visiteur.register.profile.lastname.label' => 'Vezetéknév*',
                'visiteur.register.profile.firstname.label' => 'Keresztnév*',
                'visiteur.register.profile.password.label' => 'Jelszó*',
                'visiteur.register.profile.password.help' => 'Legalább 12 karakter, köztük legalább egy nagybetű, egy kisbetű és egy számjegy.',
                'visiteur.register.profile.password_confirm.label' => 'Jelszó megerősítése*',
                'visiteur.register.profile.terms.label' => 'Elfogadom a Boolts felhasználási feltételeit.*',
                'visiteur.register.profile.submit' => 'Regisztráció',
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
                $translation->setPage('visiteur_register_profile');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
