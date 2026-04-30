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
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
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
