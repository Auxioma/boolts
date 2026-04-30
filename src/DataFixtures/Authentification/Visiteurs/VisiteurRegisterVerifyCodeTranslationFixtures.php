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

final class VisiteurRegisterVerifyCodeTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.register.verify_code.meta.title' => 'Vérification du code',
                'visiteur.register.verify_code.title' => 'Vous avez reçu un code de validation par e-mail',
                'visiteur.register.verify_code.digit_label' => 'Chiffre %position% du code',
                'visiteur.register.verify_code.submit' => 'Continuer',
                'visiteur.register.verify_code.or' => 'OU',
                'visiteur.register.verify_code.resend_code' => 'Renvoyer le code',
                'visiteur.register.verify_code.change_email' => 'Changer d’adresse e-mail',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('visiteur_register_verify_code');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
