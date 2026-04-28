<?php

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TranslationFixtures extends Fixture
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
                'visiteur.login.meta.title' => 'Login',
                'visiteur.login.title' => 'Login',
                'visiteur.login.apple' => 'Sign in with Apple',
                'visiteur.login.google' => 'Sign in with Google',
                'visiteur.login.or' => 'OR',
                'visiteur.login.email.label' => 'Email address',
                'visiteur.login.email.placeholder' => 'Enter your email address',
                'visiteur.login.password.label' => 'Password',
                'visiteur.login.password.placeholder' => 'Enter your password',
                'visiteur.login.error.invalid_credentials' => 'Invalid email address or password',
                'visiteur.login.submit' => 'Sign in',
                'visiteur.login.forgot_password' => 'Forgot password?',
                'visiteur.login.register' => 'Create an account',
                'visiteur.login.professional_link' => 'Are you a professional?',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
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
