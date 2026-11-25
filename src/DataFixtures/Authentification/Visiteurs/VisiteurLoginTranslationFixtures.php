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

final class VisiteurLoginTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.login.meta.title' => 'Connexion',
                'visiteur.login.title' => 'Connexion',
                'visiteur.login.apple' => 'Connexion via Apple',
                'visiteur.login.google' => 'Connexion via Google',
                'visiteur.login.or' => 'OU',
                'visiteur.login.email.label' => 'Adresse e-mail',
                'visiteur.login.email.placeholder' => 'Veuillez entrer votre adresse e-mail',
                'visiteur.login.password.label' => 'Mot de passe',
                'visiteur.login.password.placeholder' => 'Veuillez entrer votre mot de passe',
                'visiteur.login.error.invalid_credentials' => 'Adresse e-mail ou mot de passe incorrect',
                'visiteur.login.submit' => 'Se connecter',
                'visiteur.login.forgot_password' => 'Mot de passe oublié ?',
                'visiteur.login.register' => 'Inscription',
                'visiteur.login.professional_link' => 'Vous êtes un professionnel ?',
            ],

            'en' => [
                'visiteur.login.meta.title' => 'Sign in',
                'visiteur.login.title' => 'Sign in',
                'visiteur.login.apple' => 'Sign in with Apple',
                'visiteur.login.google' => 'Sign in with Google',
                'visiteur.login.or' => 'OR',
                'visiteur.login.email.label' => 'Email address',
                'visiteur.login.email.placeholder' => 'Enter your email address',
                'visiteur.login.password.label' => 'Password',
                'visiteur.login.password.placeholder' => 'Enter your password',
                'visiteur.login.error.invalid_credentials' => 'Incorrect email address or password',
                'visiteur.login.submit' => 'Sign in',
                'visiteur.login.forgot_password' => 'Forgot password?',
                'visiteur.login.register' => 'Register',
                'visiteur.login.professional_link' => 'Are you a professional?',
            ],
            'es' => [
                'visiteur.login.meta.title' => 'Iniciar sesión',
                'visiteur.login.title' => 'Iniciar sesión',
                'visiteur.login.apple' => 'Iniciar sesión con Apple',
                'visiteur.login.google' => 'Iniciar sesión con Google',
                'visiteur.login.or' => 'O',
                'visiteur.login.email.label' => 'Correo electrónico',
                'visiteur.login.email.placeholder' => 'Introduce tu dirección de correo electrónico',
                'visiteur.login.password.label' => 'Contraseña',
                'visiteur.login.password.placeholder' => 'Introduce tu contraseña',
                'visiteur.login.error.invalid_credentials' => 'Correo electrónico o contraseña incorrectos',
                'visiteur.login.submit' => 'Iniciar sesión',
                'visiteur.login.forgot_password' => '¿Has olvidado tu contraseña?',
                'visiteur.login.register' => 'Registro',
                'visiteur.login.professional_link' => '¿Eres profesional?',
            ],
            'de' => [
                'visiteur.login.meta.title' => 'Anmelden',
                'visiteur.login.title' => 'Anmelden',
                'visiteur.login.apple' => 'Mit Apple anmelden',
                'visiteur.login.google' => 'Mit Google anmelden',
                'visiteur.login.or' => 'ODER',
                'visiteur.login.email.label' => 'E-Mail-Adresse',
                'visiteur.login.email.placeholder' => 'Bitte geben Sie Ihre E-Mail-Adresse ein',
                'visiteur.login.password.label' => 'Passwort',
                'visiteur.login.password.placeholder' => 'Bitte geben Sie Ihr Passwort ein',
                'visiteur.login.error.invalid_credentials' => 'E-Mail-Adresse oder Passwort falsch',
                'visiteur.login.submit' => 'Anmelden',
                'visiteur.login.forgot_password' => 'Passwort vergessen?',
                'visiteur.login.register' => 'Registrierung',
                'visiteur.login.professional_link' => 'Sind Sie ein Profi?',
            ],
            'pt' => [
                'visiteur.login.meta.title' => 'Iniciar sessão',
                'visiteur.login.title' => 'Iniciar sessão',
                'visiteur.login.apple' => 'Iniciar sessão com a Apple',
                'visiteur.login.google' => 'Iniciar sessão com a Google',
                'visiteur.login.or' => 'OU',
                'visiteur.login.email.label' => 'Endereço de e-mail',
                'visiteur.login.email.placeholder' => 'Introduza o seu endereço de e-mail',
                'visiteur.login.password.label' => 'Palavra-passe',
                'visiteur.login.password.placeholder' => 'Introduza a sua palavra-passe',
                'visiteur.login.error.invalid_credentials' => 'Endereço de e-mail ou palavra-passe incorretos',
                'visiteur.login.submit' => 'Iniciar sessão',
                'visiteur.login.forgot_password' => 'Esqueceu-se da palavra-passe?',
                'visiteur.login.register' => 'Registo',
                'visiteur.login.professional_link' => 'É profissional?',
            ],
            'it' => [
                'visiteur.login.meta.title' => 'Accedi',
                'visiteur.login.title' => 'Accedi',
                'visiteur.login.apple' => 'Accedi con Apple',
                'visiteur.login.google' => 'Accedi con Google',
                'visiteur.login.or' => 'OPPURE',
                'visiteur.login.email.label' => 'Indirizzo e-mail',
                'visiteur.login.email.placeholder' => 'Inserisci il tuo indirizzo e-mail',
                'visiteur.login.password.label' => 'Password',
                'visiteur.login.password.placeholder' => 'Inserisci la tua password',
                'visiteur.login.error.invalid_credentials' => 'Indirizzo e-mail o password errati',
                'visiteur.login.submit' => 'Accedi',
                'visiteur.login.forgot_password' => 'Password dimenticata?',
                'visiteur.login.register' => 'Registrazione',
                'visiteur.login.professional_link' => 'Sei un professionista?',
            ],
            'nl' => [
                'visiteur.login.meta.title' => 'Inloggen',
                'visiteur.login.title' => 'Inloggen',
                'visiteur.login.apple' => 'Inloggen met Apple',
                'visiteur.login.google' => 'Inloggen met Google',
                'visiteur.login.or' => 'OF',
                'visiteur.login.email.label' => 'E-mailadres',
                'visiteur.login.email.placeholder' => 'Voer je e-mailadres in',
                'visiteur.login.password.label' => 'Wachtwoord',
                'visiteur.login.password.placeholder' => 'Voer je wachtwoord in',
                'visiteur.login.error.invalid_credentials' => 'Onjuist e-mailadres of wachtwoord',
                'visiteur.login.submit' => 'Inloggen',
                'visiteur.login.forgot_password' => 'Wachtwoord vergeten?',
                'visiteur.login.register' => 'Registreren',
                'visiteur.login.professional_link' => 'Ben je een professional?',
            ],
            'hu' => [
                'visiteur.login.meta.title' => 'Bejelentkezés',
                'visiteur.login.title' => 'Bejelentkezés',
                'visiteur.login.apple' => 'Bejelentkezés Apple-fiókkal',
                'visiteur.login.google' => 'Bejelentkezés Google-fiókkal',
                'visiteur.login.or' => 'VAGY',
                'visiteur.login.email.label' => 'E-mail-cím',
                'visiteur.login.email.placeholder' => 'Adja meg e-mail-címét',
                'visiteur.login.password.label' => 'Jelszó',
                'visiteur.login.password.placeholder' => 'Adja meg jelszavát',
                'visiteur.login.error.invalid_credentials' => 'Hibás e-mail-cím vagy jelszó',
                'visiteur.login.submit' => 'Bejelentkezés',
                'visiteur.login.forgot_password' => 'Elfelejtette jelszavát?',
                'visiteur.login.register' => 'Regisztráció',
                'visiteur.login.professional_link' => 'Ön szakember?',
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
                $translation->setPage('visiteur_login');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
