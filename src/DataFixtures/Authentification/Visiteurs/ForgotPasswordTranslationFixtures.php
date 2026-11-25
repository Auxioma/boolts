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

final class ForgotPasswordTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.forgot_password.meta.title' => 'Mot de passe oublié',
                'visiteur.forgot_password.title' => 'Saisissez votre adresse e-mail',
                'visiteur.forgot_password.email.label' => 'Adresse e-mail',
                'visiteur.forgot_password.email.placeholder' => 'Veuillez entrer votre adresse e-mail',
                'visiteur.forgot_password.submit' => 'Envoyer le lien de modification',
            ],

            'en' => [
                'visiteur.forgot_password.meta.title' => 'Forgot password',
                'visiteur.forgot_password.title' => 'Enter your email address',
                'visiteur.forgot_password.email.label' => 'Email address',
                'visiteur.forgot_password.email.placeholder' => 'Enter your email address',
                'visiteur.forgot_password.submit' => 'Send reset link',
            ],
            'es' => [
                'visiteur.forgot_password.meta.title' => 'Contraseña olvidada',
                'visiteur.forgot_password.title' => 'Introduce tu dirección de correo electrónico',
                'visiteur.forgot_password.email.label' => 'Correo electrónico',
                'visiteur.forgot_password.email.placeholder' => 'Introduce tu dirección de correo electrónico',
                'visiteur.forgot_password.submit' => 'Enviar el enlace de restablecimiento',
            ],
            'de' => [
                'visiteur.forgot_password.meta.title' => 'Passwort vergessen',
                'visiteur.forgot_password.title' => 'Geben Sie Ihre E-Mail-Adresse ein',
                'visiteur.forgot_password.email.label' => 'E-Mail-Adresse',
                'visiteur.forgot_password.email.placeholder' => 'Bitte geben Sie Ihre E-Mail-Adresse ein',
                'visiteur.forgot_password.submit' => 'Link zum Zurücksetzen senden',
            ],
            'pt' => [
                'visiteur.forgot_password.meta.title' => 'Palavra-passe esquecida',
                'visiteur.forgot_password.title' => 'Introduza o seu endereço de e-mail',
                'visiteur.forgot_password.email.label' => 'Endereço de e-mail',
                'visiteur.forgot_password.email.placeholder' => 'Introduza o seu endereço de e-mail',
                'visiteur.forgot_password.submit' => 'Enviar link de redefinição',
            ],
            'it' => [
                'visiteur.forgot_password.meta.title' => 'Password dimenticata',
                'visiteur.forgot_password.title' => 'Inserisci il tuo indirizzo e-mail',
                'visiteur.forgot_password.email.label' => 'Indirizzo e-mail',
                'visiteur.forgot_password.email.placeholder' => 'Inserisci il tuo indirizzo e-mail',
                'visiteur.forgot_password.submit' => 'Invia il link di reimpostazione',
            ],
            'nl' => [
                'visiteur.forgot_password.meta.title' => 'Wachtwoord vergeten',
                'visiteur.forgot_password.title' => 'Voer je e-mailadres in',
                'visiteur.forgot_password.email.label' => 'E-mailadres',
                'visiteur.forgot_password.email.placeholder' => 'Voer je e-mailadres in',
                'visiteur.forgot_password.submit' => 'Resetlink verzenden',
            ],
            'hu' => [
                'visiteur.forgot_password.meta.title' => 'Elfelejtett jelszó',
                'visiteur.forgot_password.title' => 'Adja meg e-mail-címét',
                'visiteur.forgot_password.email.label' => 'E-mail-cím',
                'visiteur.forgot_password.email.placeholder' => 'Adja meg e-mail-címét',
                'visiteur.forgot_password.submit' => 'Visszaállítási link küldése',
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
                $translation->setPage('forgot_password');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
