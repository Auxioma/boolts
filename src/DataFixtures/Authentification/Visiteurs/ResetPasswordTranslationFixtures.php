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

final class ResetPasswordTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.reset_password.reset.meta.title' => 'Nouveau mot de passe',
                'visiteur.reset_password.reset.title' => 'Modifiez votre mot de passe',
                'visiteur.reset_password.reset.password.label' => 'Nouveau mot de passe',
                'visiteur.reset_password.reset.password.placeholder' => 'Veuillez entrer votre mot de passe',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirmer le mot de passe',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Confirmez votre mot de passe',
                'visiteur.reset_password.reset.submit' => 'Modifier mon mot de passe',
            ],

            'en' => [
                'visiteur.reset_password.reset.meta.title' => 'New password',
                'visiteur.reset_password.reset.title' => 'Change your password',
                'visiteur.reset_password.reset.password.label' => 'New password',
                'visiteur.reset_password.reset.password.placeholder' => 'Enter your password',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirm password',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Confirm your password',
                'visiteur.reset_password.reset.submit' => 'Change my password',
            ],
            'es' => [
                'visiteur.reset_password.reset.meta.title' => 'Nueva contraseña',
                'visiteur.reset_password.reset.title' => 'Cambia tu contraseña',
                'visiteur.reset_password.reset.password.label' => 'Nueva contraseña',
                'visiteur.reset_password.reset.password.placeholder' => 'Introduce tu contraseña',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirmar contraseña',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Confirma tu contraseña',
                'visiteur.reset_password.reset.submit' => 'Cambiar mi contraseña',
            ],
            'de' => [
                'visiteur.reset_password.reset.meta.title' => 'Neues Passwort',
                'visiteur.reset_password.reset.title' => 'Ändern Sie Ihr Passwort',
                'visiteur.reset_password.reset.password.label' => 'Neues Passwort',
                'visiteur.reset_password.reset.password.placeholder' => 'Bitte geben Sie Ihr Passwort ein',
                'visiteur.reset_password.reset.password_confirm.label' => 'Passwort bestätigen',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Bestätigen Sie Ihr Passwort',
                'visiteur.reset_password.reset.submit' => 'Mein Passwort ändern',
            ],
            'pt' => [
                'visiteur.reset_password.reset.meta.title' => 'Nova palavra-passe',
                'visiteur.reset_password.reset.title' => 'Altere a sua palavra-passe',
                'visiteur.reset_password.reset.password.label' => 'Nova palavra-passe',
                'visiteur.reset_password.reset.password.placeholder' => 'Introduza a sua palavra-passe',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirmar palavra-passe',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Confirme a sua palavra-passe',
                'visiteur.reset_password.reset.submit' => 'Alterar a minha palavra-passe',
            ],
            'it' => [
                'visiteur.reset_password.reset.meta.title' => 'Nuova password',
                'visiteur.reset_password.reset.title' => 'Modifica la tua password',
                'visiteur.reset_password.reset.password.label' => 'Nuova password',
                'visiteur.reset_password.reset.password.placeholder' => 'Inserisci la tua password',
                'visiteur.reset_password.reset.password_confirm.label' => 'Conferma password',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Conferma la tua password',
                'visiteur.reset_password.reset.submit' => 'Modifica la mia password',
            ],
            'nl' => [
                'visiteur.reset_password.reset.meta.title' => 'Nieuw wachtwoord',
                'visiteur.reset_password.reset.title' => 'Wijzig je wachtwoord',
                'visiteur.reset_password.reset.password.label' => 'Nieuw wachtwoord',
                'visiteur.reset_password.reset.password.placeholder' => 'Voer je wachtwoord in',
                'visiteur.reset_password.reset.password_confirm.label' => 'Wachtwoord bevestigen',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Bevestig je wachtwoord',
                'visiteur.reset_password.reset.submit' => 'Mijn wachtwoord wijzigen',
            ],
            'hu' => [
                'visiteur.reset_password.reset.meta.title' => 'Új jelszó',
                'visiteur.reset_password.reset.title' => 'Módosítsa jelszavát',
                'visiteur.reset_password.reset.password.label' => 'Új jelszó',
                'visiteur.reset_password.reset.password.placeholder' => 'Adja meg jelszavát',
                'visiteur.reset_password.reset.password_confirm.label' => 'Jelszó megerősítése',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Erősítse meg jelszavát',
                'visiteur.reset_password.reset.submit' => 'Jelszavam módosítása',
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
                $translation->setPage('reset_password');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
