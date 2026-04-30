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

final class ProfessionnelRegisterEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.register.email.meta.title' => 'Inscription',
                'professionnel.register.email.title' => 'Inscription à votre espace pro',
                'professionnel.register.email.apple' => 'Connexion via Apple',
                'professionnel.register.email.or' => 'OU',
                'professionnel.register.email.email.label' => 'Inscription via adresse e-mail*',
                'professionnel.register.email.submit' => 'Continuer',
                'professionnel.register.email.link' => 'Vous êtes un professionnel ?',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('professionnel_register_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
