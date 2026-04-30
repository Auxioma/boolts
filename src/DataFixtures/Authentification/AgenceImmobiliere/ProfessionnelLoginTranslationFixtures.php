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
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
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
