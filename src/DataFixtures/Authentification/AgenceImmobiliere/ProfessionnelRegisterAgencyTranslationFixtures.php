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

final class ProfessionnelRegisterAgencyTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.register.agency.meta.title' => 'Inscription agence',
                'professionnel.register.agency.title' => 'Inscrivez votre agence dès maintenant',
                'professionnel.register.agency.lastname.label' => 'Nom*',
                'professionnel.register.agency.firstname.label' => 'Prénom*',
                'professionnel.register.agency.password.label' => 'Mot de passe*',
                'professionnel.register.agency.password_confirm.label' => 'Confirmer le mot de passe*',
                'professionnel.register.agency.terms.label' => 'J’accepte les conditions d’utilisation de Boolts.*',
                'professionnel.register.agency.submit' => 'Se connecter',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('professionnel_register_agency');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
