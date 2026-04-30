<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

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
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
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
