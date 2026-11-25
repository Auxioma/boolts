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

final class ProfessionnelForgotPasswordTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.forgot_password.meta.title' => 'Mot de passe oublié',
                'professionnel.forgot_password.title' => 'Saisissez votre adresse e-mail',
                'professionnel.forgot_password.email.label' => 'Adresse e-mail',
                'professionnel.forgot_password.submit' => 'Envoyer le lien de modification',
            ],

            'en' => [
                'professionnel.forgot_password.meta.title' => 'Forgot password',
                'professionnel.forgot_password.title' => 'Enter your email address',
                'professionnel.forgot_password.email.label' => 'Email address',
                'professionnel.forgot_password.submit' => 'Send reset link',
            ],
            'es' => [
                'professionnel.forgot_password.meta.title' => 'Contraseña olvidada',
                'professionnel.forgot_password.title' => 'Introduce tu dirección de correo electrónico',
                'professionnel.forgot_password.email.label' => 'Correo electrónico',
                'professionnel.forgot_password.submit' => 'Enviar el enlace de restablecimiento',
            ],
            'de' => [
                'professionnel.forgot_password.meta.title' => 'Passwort vergessen',
                'professionnel.forgot_password.title' => 'Geben Sie Ihre E-Mail-Adresse ein',
                'professionnel.forgot_password.email.label' => 'E-Mail-Adresse',
                'professionnel.forgot_password.submit' => 'Link zum Zurücksetzen senden',
            ],
            'pt' => [
                'professionnel.forgot_password.meta.title' => 'Palavra-passe esquecida',
                'professionnel.forgot_password.title' => 'Introduza o seu endereço de e-mail',
                'professionnel.forgot_password.email.label' => 'Endereço de e-mail',
                'professionnel.forgot_password.submit' => 'Enviar link de redefinição',
            ],
            'it' => [
                'professionnel.forgot_password.meta.title' => 'Password dimenticata',
                'professionnel.forgot_password.title' => 'Inserisci il tuo indirizzo e-mail',
                'professionnel.forgot_password.email.label' => 'Indirizzo e-mail',
                'professionnel.forgot_password.submit' => 'Invia il link di reimpostazione',
            ],
            'nl' => [
                'professionnel.forgot_password.meta.title' => 'Wachtwoord vergeten',
                'professionnel.forgot_password.title' => 'Voer je e-mailadres in',
                'professionnel.forgot_password.email.label' => 'E-mailadres',
                'professionnel.forgot_password.submit' => 'Resetlink verzenden',
            ],
            'hu' => [
                'professionnel.forgot_password.meta.title' => 'Elfelejtett jelszó',
                'professionnel.forgot_password.title' => 'Adja meg e-mail-címét',
                'professionnel.forgot_password.email.label' => 'E-mail-cím',
                'professionnel.forgot_password.submit' => 'Visszaállítási link küldése',
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
                $translation->setPage('professionnel_forgot_password');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
