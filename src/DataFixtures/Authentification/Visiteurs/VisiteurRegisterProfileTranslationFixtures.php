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

final class VisiteurRegisterProfileTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.register.profile.meta.title' => 'Bienvenue sur Boolts !',
                'visiteur.register.profile.title' => 'Bienvenue sur Boolts !',
                'visiteur.register.profile.lastname.label' => 'Nom*',
                'visiteur.register.profile.firstname.label' => 'Prénom*',
                'visiteur.register.profile.password.label' => 'Mot de passe*',
                'visiteur.register.profile.password.help' => 'Minimum 12 caractères. Nous recommandons de combiner lettres, chiffres et caractères spéciaux pour une sécurité optimale.',
                'visiteur.register.profile.password_confirm.label' => 'Confirmer le mot de passe*',
                'visiteur.register.profile.terms.label' => 'J’accepte les conditions d’utilisation de Boolts.*',
                'visiteur.register.profile.submit' => 'S’inscrire',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('visiteur_register_profile');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
