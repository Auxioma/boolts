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

final class VisiteurRegisterEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.register.email.meta.title' => 'Inscription',
                'visiteur.register.email.title' => 'Inscription',
                'visiteur.register.email.apple' => 'Inscription via Apple',
                'visiteur.register.email.google' => 'Inscription via Google',
                'visiteur.register.email.or' => 'OU',
                'visiteur.register.email.email.label' => 'Inscription via adresse e-mail*',
                'visiteur.register.email.email.placeholder' => 'Veuillez entrer votre adresse e-mail',
                'visiteur.register.email.submit' => 'Continuer',
                'visiteur.register.email.professional_link' => 'Vous êtes un professionnel ?',
            ],

            'en' => [
                'visiteur.register.email.meta.title' => 'Registration',
                'visiteur.register.email.title' => 'Registration',
                'visiteur.register.email.apple' => 'Register with Apple',
                'visiteur.register.email.google' => 'Register with Google',
                'visiteur.register.email.or' => 'OR',
                'visiteur.register.email.email.label' => 'Register with an email address*',
                'visiteur.register.email.email.placeholder' => 'Enter your email address',
                'visiteur.register.email.submit' => 'Continue',
                'visiteur.register.email.professional_link' => 'Are you a professional?',
            ],
            'es' => [
                'visiteur.register.email.meta.title' => 'Registro',
                'visiteur.register.email.title' => 'Registro',
                'visiteur.register.email.apple' => 'Registro con Apple',
                'visiteur.register.email.google' => 'Registro con Google',
                'visiteur.register.email.or' => 'O',
                'visiteur.register.email.email.label' => 'Registro con correo electrónico*',
                'visiteur.register.email.email.placeholder' => 'Introduce tu dirección de correo electrónico',
                'visiteur.register.email.submit' => 'Continuar',
                'visiteur.register.email.professional_link' => '¿Eres profesional?',
            ],
            'de' => [
                'visiteur.register.email.meta.title' => 'Registrierung',
                'visiteur.register.email.title' => 'Registrierung',
                'visiteur.register.email.apple' => 'Mit Apple registrieren',
                'visiteur.register.email.google' => 'Mit Google registrieren',
                'visiteur.register.email.or' => 'ODER',
                'visiteur.register.email.email.label' => 'Registrierung per E-Mail-Adresse*',
                'visiteur.register.email.email.placeholder' => 'Bitte geben Sie Ihre E-Mail-Adresse ein',
                'visiteur.register.email.submit' => 'Weiter',
                'visiteur.register.email.professional_link' => 'Sind Sie ein Profi?',
            ],
            'pt' => [
                'visiteur.register.email.meta.title' => 'Registo',
                'visiteur.register.email.title' => 'Registo',
                'visiteur.register.email.apple' => 'Registo com a Apple',
                'visiteur.register.email.google' => 'Registo com a Google',
                'visiteur.register.email.or' => 'OU',
                'visiteur.register.email.email.label' => 'Registo com endereço de e-mail*',
                'visiteur.register.email.email.placeholder' => 'Introduza o seu endereço de e-mail',
                'visiteur.register.email.submit' => 'Continuar',
                'visiteur.register.email.professional_link' => 'É profissional?',
            ],
            'it' => [
                'visiteur.register.email.meta.title' => 'Registrazione',
                'visiteur.register.email.title' => 'Registrazione',
                'visiteur.register.email.apple' => 'Registrazione con Apple',
                'visiteur.register.email.google' => 'Registrazione con Google',
                'visiteur.register.email.or' => 'OPPURE',
                'visiteur.register.email.email.label' => 'Registrazione con indirizzo e-mail*',
                'visiteur.register.email.email.placeholder' => 'Inserisci il tuo indirizzo e-mail',
                'visiteur.register.email.submit' => 'Continua',
                'visiteur.register.email.professional_link' => 'Sei un professionista?',
            ],
            'nl' => [
                'visiteur.register.email.meta.title' => 'Registratie',
                'visiteur.register.email.title' => 'Registratie',
                'visiteur.register.email.apple' => 'Registreren met Apple',
                'visiteur.register.email.google' => 'Registreren met Google',
                'visiteur.register.email.or' => 'OF',
                'visiteur.register.email.email.label' => 'Registreren met e-mailadres*',
                'visiteur.register.email.email.placeholder' => 'Voer je e-mailadres in',
                'visiteur.register.email.submit' => 'Doorgaan',
                'visiteur.register.email.professional_link' => 'Ben je een professional?',
            ],
            'hu' => [
                'visiteur.register.email.meta.title' => 'Regisztráció',
                'visiteur.register.email.title' => 'Regisztráció',
                'visiteur.register.email.apple' => 'Regisztráció Apple-fiókkal',
                'visiteur.register.email.google' => 'Regisztráció Google-fiókkal',
                'visiteur.register.email.or' => 'VAGY',
                'visiteur.register.email.email.label' => 'Regisztráció e-mail-címmel*',
                'visiteur.register.email.email.placeholder' => 'Adja meg e-mail-címét',
                'visiteur.register.email.submit' => 'Tovább',
                'visiteur.register.email.professional_link' => 'Ön szakember?',
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
                $translation->setPage('visiteur_register_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
