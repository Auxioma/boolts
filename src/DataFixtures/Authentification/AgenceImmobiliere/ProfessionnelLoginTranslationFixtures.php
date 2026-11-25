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

final class ProfessionnelLoginTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.login.meta.title' => 'Connexion',
                'professionnel.login.title' => 'Connexion à votre espace professionnel',
                'professionnel.login.apple' => 'Connexion via Apple',
                'professionnel.login.or' => 'OU',
                'professionnel.login.email.label' => 'Adresse e-mail',
                'professionnel.login.password.label' => 'Mot de passe',
                'professionnel.login.submit' => 'Se connecter',
                'professionnel.login.forgot_password' => 'Mot de passe oublié ?',
                'professionnel.login.register' => 'Inscription',
            ],

            'en' => [
                'professionnel.login.meta.title' => 'Sign in',
                'professionnel.login.title' => 'Sign in to your professional area',
                'professionnel.login.apple' => 'Sign in with Apple',
                'professionnel.login.or' => 'OR',
                'professionnel.login.email.label' => 'Email address',
                'professionnel.login.password.label' => 'Password',
                'professionnel.login.submit' => 'Sign in',
                'professionnel.login.forgot_password' => 'Forgot password?',
                'professionnel.login.register' => 'Register',
            ],
            'es' => [
                'professionnel.login.meta.title' => 'Iniciar sesión',
                'professionnel.login.title' => 'Inicia sesión en tu espacio profesional',
                'professionnel.login.apple' => 'Iniciar sesión con Apple',
                'professionnel.login.or' => 'O',
                'professionnel.login.email.label' => 'Correo electrónico',
                'professionnel.login.password.label' => 'Contraseña',
                'professionnel.login.submit' => 'Iniciar sesión',
                'professionnel.login.forgot_password' => '¿Has olvidado tu contraseña?',
                'professionnel.login.register' => 'Registro',
            ],
            'de' => [
                'professionnel.login.meta.title' => 'Anmelden',
                'professionnel.login.title' => 'Melden Sie sich in Ihrem Profi-Bereich an',
                'professionnel.login.apple' => 'Mit Apple anmelden',
                'professionnel.login.or' => 'ODER',
                'professionnel.login.email.label' => 'E-Mail-Adresse',
                'professionnel.login.password.label' => 'Passwort',
                'professionnel.login.submit' => 'Anmelden',
                'professionnel.login.forgot_password' => 'Passwort vergessen?',
                'professionnel.login.register' => 'Registrierung',
            ],
            'pt' => [
                'professionnel.login.meta.title' => 'Iniciar sessão',
                'professionnel.login.title' => 'Inicie sessão na sua área profissional',
                'professionnel.login.apple' => 'Iniciar sessão com a Apple',
                'professionnel.login.or' => 'OU',
                'professionnel.login.email.label' => 'Endereço de e-mail',
                'professionnel.login.password.label' => 'Palavra-passe',
                'professionnel.login.submit' => 'Iniciar sessão',
                'professionnel.login.forgot_password' => 'Esqueceu-se da palavra-passe?',
                'professionnel.login.register' => 'Registo',
            ],
            'it' => [
                'professionnel.login.meta.title' => 'Accedi',
                'professionnel.login.title' => 'Accedi alla tua area professionale',
                'professionnel.login.apple' => 'Accedi con Apple',
                'professionnel.login.or' => 'OPPURE',
                'professionnel.login.email.label' => 'Indirizzo e-mail',
                'professionnel.login.password.label' => 'Password',
                'professionnel.login.submit' => 'Accedi',
                'professionnel.login.forgot_password' => 'Password dimenticata?',
                'professionnel.login.register' => 'Registrazione',
            ],
            'nl' => [
                'professionnel.login.meta.title' => 'Inloggen',
                'professionnel.login.title' => 'Log in op je professionele omgeving',
                'professionnel.login.apple' => 'Inloggen met Apple',
                'professionnel.login.or' => 'OF',
                'professionnel.login.email.label' => 'E-mailadres',
                'professionnel.login.password.label' => 'Wachtwoord',
                'professionnel.login.submit' => 'Inloggen',
                'professionnel.login.forgot_password' => 'Wachtwoord vergeten?',
                'professionnel.login.register' => 'Registreren',
            ],
            'hu' => [
                'professionnel.login.meta.title' => 'Bejelentkezés',
                'professionnel.login.title' => 'Bejelentkezés a szakmai fiókjába',
                'professionnel.login.apple' => 'Bejelentkezés Apple-fiókkal',
                'professionnel.login.or' => 'VAGY',
                'professionnel.login.email.label' => 'E-mail-cím',
                'professionnel.login.password.label' => 'Jelszó',
                'professionnel.login.submit' => 'Bejelentkezés',
                'professionnel.login.forgot_password' => 'Elfelejtette jelszavát?',
                'professionnel.login.register' => 'Regisztráció',
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
                $translation->setPage('professionnel_login');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
