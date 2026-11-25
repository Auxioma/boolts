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

final class ProfessionnelRegisterEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.register.email.meta.title' => 'Inscription',
                'professionnel.register.email.title' => 'Inscription à votre espace pro',
                'professionnel.register.email.apple' => 'Connexion via Apple',
                'professionnel.register.email.or' => 'OU',
                'professionnel.register.email.email.label' => 'Inscription via adresse e-mail*',
                'professionnel.register.email.submit' => 'Continuer',
                'professionnel.register.email.link' => 'Vous êtes un professionnel ?',
            ],

            'en' => [
                'professionnel.register.email.meta.title' => 'Registration',
                'professionnel.register.email.title' => 'Register for your professional area',
                'professionnel.register.email.apple' => 'Sign in with Apple',
                'professionnel.register.email.or' => 'OR',
                'professionnel.register.email.email.label' => 'Register with an email address*',
                'professionnel.register.email.submit' => 'Continue',
                'professionnel.register.email.link' => 'Are you a professional?',
            ],
            'es' => [
                'professionnel.register.email.meta.title' => 'Registro',
                'professionnel.register.email.title' => 'Registro en tu espacio profesional',
                'professionnel.register.email.apple' => 'Iniciar sesión con Apple',
                'professionnel.register.email.or' => 'O',
                'professionnel.register.email.email.label' => 'Registro con correo electrónico*',
                'professionnel.register.email.submit' => 'Continuar',
                'professionnel.register.email.link' => '¿Eres profesional?',
            ],
            'de' => [
                'professionnel.register.email.meta.title' => 'Registrierung',
                'professionnel.register.email.title' => 'Registrierung für Ihren Profi-Bereich',
                'professionnel.register.email.apple' => 'Mit Apple anmelden',
                'professionnel.register.email.or' => 'ODER',
                'professionnel.register.email.email.label' => 'Registrierung per E-Mail-Adresse*',
                'professionnel.register.email.submit' => 'Weiter',
                'professionnel.register.email.link' => 'Sind Sie ein Profi?',
            ],
            'pt' => [
                'professionnel.register.email.meta.title' => 'Registo',
                'professionnel.register.email.title' => 'Registo na sua área profissional',
                'professionnel.register.email.apple' => 'Iniciar sessão com a Apple',
                'professionnel.register.email.or' => 'OU',
                'professionnel.register.email.email.label' => 'Registo com endereço de e-mail*',
                'professionnel.register.email.submit' => 'Continuar',
                'professionnel.register.email.link' => 'É profissional?',
            ],
            'it' => [
                'professionnel.register.email.meta.title' => 'Registrazione',
                'professionnel.register.email.title' => 'Registrazione alla tua area professionale',
                'professionnel.register.email.apple' => 'Accedi con Apple',
                'professionnel.register.email.or' => 'OPPURE',
                'professionnel.register.email.email.label' => 'Registrazione con indirizzo e-mail*',
                'professionnel.register.email.submit' => 'Continua',
                'professionnel.register.email.link' => 'Sei un professionista?',
            ],
            'nl' => [
                'professionnel.register.email.meta.title' => 'Registratie',
                'professionnel.register.email.title' => 'Registratie voor je professionele omgeving',
                'professionnel.register.email.apple' => 'Inloggen met Apple',
                'professionnel.register.email.or' => 'OF',
                'professionnel.register.email.email.label' => 'Registreren met e-mailadres*',
                'professionnel.register.email.submit' => 'Doorgaan',
                'professionnel.register.email.link' => 'Ben je een professional?',
            ],
            'hu' => [
                'professionnel.register.email.meta.title' => 'Regisztráció',
                'professionnel.register.email.title' => 'Regisztráció a szakmai fiókhoz',
                'professionnel.register.email.apple' => 'Bejelentkezés Apple-fiókkal',
                'professionnel.register.email.or' => 'VAGY',
                'professionnel.register.email.email.label' => 'Regisztráció e-mail-címmel*',
                'professionnel.register.email.submit' => 'Tovább',
                'professionnel.register.email.link' => 'Ön szakember?',
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
                $translation->setPage('professionnel_register_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
