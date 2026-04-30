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

namespace App\DataFixtures\Authentification\Visiteurs;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VisiteurRegisterEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.register.email.meta.title' => 'Inscription',
                'visiteur.register.email.title' => 'Inscription',
                'visiteur.register.email.apple' => 'Inscription via Apple',
                'visiteur.register.email.google' => 'Inscription via Google',
                'visiteur.register.email.or' => 'OU',
                'visiteur.register.email.email.label' => 'Inscription via adresse e-mail*',
                'visiteur.register.email.email.placeholder' => 'Veuillez entrer votre adresse e-mail',
                'visiteur.register.email.submit' => 'Continuer',
                'visiteur.register.email.professional_link' => 'Vous êtes un professionnel ?',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('visiteur_register_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
