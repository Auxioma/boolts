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

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ProfessionnelRegisterAgencyTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.register.agency.meta.title' => 'Inscription agence',
                'professionnel.register.agency.title' => 'Inscrivez votre agence dès maintenant',
                'professionnel.register.agency.lastname.label' => 'Nom*',
                'professionnel.register.agency.firstname.label' => 'Prénom*',
                'professionnel.register.agency.password.label' => 'Mot de passe*',
                'professionnel.register.agency.password_confirm.label' => 'Confirmer le mot de passe*',
                'professionnel.register.agency.terms.label' => 'J’accepte les conditions d’utilisation de Boolts.*',
                'professionnel.register.agency.submit' => 'Se connecter',
            ],

            'en' => [
                'professionnel.register.agency.meta.title' => 'Agency registration',
                'professionnel.register.agency.title' => 'Register your agency now',
                'professionnel.register.agency.lastname.label' => 'Last name*',
                'professionnel.register.agency.firstname.label' => 'First name*',
                'professionnel.register.agency.password.label' => 'Password*',
                'professionnel.register.agency.password_confirm.label' => 'Confirm password*',
                'professionnel.register.agency.terms.label' => 'I accept the Boolts terms of use.*',
                'professionnel.register.agency.submit' => 'Sign in',
            ],
            'es' => [
                'professionnel.register.agency.meta.title' => 'Registro de agencia',
                'professionnel.register.agency.title' => 'Registra tu agencia ahora',
                'professionnel.register.agency.lastname.label' => 'Apellido*',
                'professionnel.register.agency.firstname.label' => 'Nombre*',
                'professionnel.register.agency.password.label' => 'Contraseña*',
                'professionnel.register.agency.password_confirm.label' => 'Confirmar contraseña*',
                'professionnel.register.agency.terms.label' => 'Acepto las condiciones de uso de Boolts.*',
                'professionnel.register.agency.submit' => 'Iniciar sesión',
            ],
            'de' => [
                'professionnel.register.agency.meta.title' => 'Agenturregistrierung',
                'professionnel.register.agency.title' => 'Registrieren Sie jetzt Ihre Agentur',
                'professionnel.register.agency.lastname.label' => 'Nachname*',
                'professionnel.register.agency.firstname.label' => 'Vorname*',
                'professionnel.register.agency.password.label' => 'Passwort*',
                'professionnel.register.agency.password_confirm.label' => 'Passwort bestätigen*',
                'professionnel.register.agency.terms.label' => 'Ich akzeptiere die Nutzungsbedingungen von Boolts.*',
                'professionnel.register.agency.submit' => 'Anmelden',
            ],
            'pt' => [
                'professionnel.register.agency.meta.title' => 'Registo de agência',
                'professionnel.register.agency.title' => 'Registe já a sua agência',
                'professionnel.register.agency.lastname.label' => 'Apelido*',
                'professionnel.register.agency.firstname.label' => 'Nome próprio*',
                'professionnel.register.agency.password.label' => 'Palavra-passe*',
                'professionnel.register.agency.password_confirm.label' => 'Confirmar palavra-passe*',
                'professionnel.register.agency.terms.label' => 'Aceito os termos de utilização da Boolts.*',
                'professionnel.register.agency.submit' => 'Iniciar sessão',
            ],
            'it' => [
                'professionnel.register.agency.meta.title' => 'Registrazione agenzia',
                'professionnel.register.agency.title' => 'Registra subito la tua agenzia',
                'professionnel.register.agency.lastname.label' => 'Cognome*',
                'professionnel.register.agency.firstname.label' => 'Nome*',
                'professionnel.register.agency.password.label' => 'Password*',
                'professionnel.register.agency.password_confirm.label' => 'Conferma password*',
                'professionnel.register.agency.terms.label' => 'Accetto le condizioni d’uso di Boolts.*',
                'professionnel.register.agency.submit' => 'Accedi',
            ],
            'nl' => [
                'professionnel.register.agency.meta.title' => 'Registratie makelaarskantoor',
                'professionnel.register.agency.title' => 'Registreer nu je kantoor',
                'professionnel.register.agency.lastname.label' => 'Achternaam*',
                'professionnel.register.agency.firstname.label' => 'Voornaam*',
                'professionnel.register.agency.password.label' => 'Wachtwoord*',
                'professionnel.register.agency.password_confirm.label' => 'Wachtwoord bevestigen*',
                'professionnel.register.agency.terms.label' => 'Ik ga akkoord met de gebruiksvoorwaarden van Boolts.*',
                'professionnel.register.agency.submit' => 'Inloggen',
            ],
            'hu' => [
                'professionnel.register.agency.meta.title' => 'Ügynökség regisztrációja',
                'professionnel.register.agency.title' => 'Regisztrálja ügynökségét most',
                'professionnel.register.agency.lastname.label' => 'Vezetéknév*',
                'professionnel.register.agency.firstname.label' => 'Keresztnév*',
                'professionnel.register.agency.password.label' => 'Jelszó*',
                'professionnel.register.agency.password_confirm.label' => 'Jelszó megerősítése*',
                'professionnel.register.agency.terms.label' => 'Elfogadom a Boolts felhasználási feltételeit.*',
                'professionnel.register.agency.submit' => 'Bejelentkezés',
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
                $translation->setPage('professionnel_register_agency');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
