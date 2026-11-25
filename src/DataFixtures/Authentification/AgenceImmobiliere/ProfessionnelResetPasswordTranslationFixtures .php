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

final class ProfessionnelResetPasswordTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.reset_password.meta.title' => 'Modification du mot de passe',
                'professionnel.reset_password.title' => 'Modifiez votre mot de passe',
                'professionnel.reset_password.password.label' => 'Mot de passe',
                'professionnel.reset_password.password.placeholder' => 'Nouveau mot de passe',
                'professionnel.reset_password.password_confirm.label' => 'Confirmez votre mot de passe',
                'professionnel.reset_password.password_confirm.placeholder' => 'Confirmer le mot de passe',
                'professionnel.reset_password.submit' => 'Se connecter',
            ],

            'en' => [
                'professionnel.reset_password.meta.title' => 'Password reset',
                'professionnel.reset_password.title' => 'Change your password',
                'professionnel.reset_password.password.label' => 'Password',
                'professionnel.reset_password.password.placeholder' => 'New password',
                'professionnel.reset_password.password_confirm.label' => 'Confirm password',
                'professionnel.reset_password.password_confirm.placeholder' => 'Confirm password',
                'professionnel.reset_password.submit' => 'Sign in',
            ],
            'es' => [
                'professionnel.reset_password.meta.title' => 'Restablecimiento de contraseña',
                'professionnel.reset_password.title' => 'Cambia tu contraseña',
                'professionnel.reset_password.password.label' => 'Contraseña',
                'professionnel.reset_password.password.placeholder' => 'Nueva contraseña',
                'professionnel.reset_password.password_confirm.label' => 'Confirma tu contraseña',
                'professionnel.reset_password.password_confirm.placeholder' => 'Confirmar contraseña',
                'professionnel.reset_password.submit' => 'Iniciar sesión',
            ],
            'de' => [
                'professionnel.reset_password.meta.title' => 'Passwort zurücksetzen',
                'professionnel.reset_password.title' => 'Ändern Sie Ihr Passwort',
                'professionnel.reset_password.password.label' => 'Passwort',
                'professionnel.reset_password.password.placeholder' => 'Neues Passwort',
                'professionnel.reset_password.password_confirm.label' => 'Bestätigen Sie Ihr Passwort',
                'professionnel.reset_password.password_confirm.placeholder' => 'Passwort bestätigen',
                'professionnel.reset_password.submit' => 'Anmelden',
            ],
            'pt' => [
                'professionnel.reset_password.meta.title' => 'Redefinição da palavra-passe',
                'professionnel.reset_password.title' => 'Altere a sua palavra-passe',
                'professionnel.reset_password.password.label' => 'Palavra-passe',
                'professionnel.reset_password.password.placeholder' => 'Nova palavra-passe',
                'professionnel.reset_password.password_confirm.label' => 'Confirme a sua palavra-passe',
                'professionnel.reset_password.password_confirm.placeholder' => 'Confirmar palavra-passe',
                'professionnel.reset_password.submit' => 'Iniciar sessão',
            ],
            'it' => [
                'professionnel.reset_password.meta.title' => 'Reimpostazione della password',
                'professionnel.reset_password.title' => 'Modifica la tua password',
                'professionnel.reset_password.password.label' => 'Password',
                'professionnel.reset_password.password.placeholder' => 'Nuova password',
                'professionnel.reset_password.password_confirm.label' => 'Conferma la tua password',
                'professionnel.reset_password.password_confirm.placeholder' => 'Conferma password',
                'professionnel.reset_password.submit' => 'Accedi',
            ],
            'nl' => [
                'professionnel.reset_password.meta.title' => 'Wachtwoord opnieuw instellen',
                'professionnel.reset_password.title' => 'Wijzig je wachtwoord',
                'professionnel.reset_password.password.label' => 'Wachtwoord',
                'professionnel.reset_password.password.placeholder' => 'Nieuw wachtwoord',
                'professionnel.reset_password.password_confirm.label' => 'Bevestig je wachtwoord',
                'professionnel.reset_password.password_confirm.placeholder' => 'Wachtwoord bevestigen',
                'professionnel.reset_password.submit' => 'Inloggen',
            ],
            'hu' => [
                'professionnel.reset_password.meta.title' => 'Jelszó visszaállítása',
                'professionnel.reset_password.title' => 'Módosítsa jelszavát',
                'professionnel.reset_password.password.label' => 'Jelszó',
                'professionnel.reset_password.password.placeholder' => 'Új jelszó',
                'professionnel.reset_password.password_confirm.label' => 'Erősítse meg jelszavát',
                'professionnel.reset_password.password_confirm.placeholder' => 'Jelszó megerősítése',
                'professionnel.reset_password.submit' => 'Bejelentkezés',
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
                $translation->setPage('professionnel_reset_password');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
