<?php

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

            'en' => [
                'visiteur.register.profile.meta.title' => 'Login',
                'visiteur.register.profile.title' => 'Login',
                'visiteur.register.profile.lastname.label' => 'Last name*',
                'visiteur.register.profile.firstname.label' => 'First name*',
                'visiteur.register.profile.password.label' => 'Password*',
                'visiteur.register.profile.password.help' => 'Minimum 12 characters. We recommend combining letters, numbers and special characters for optimal security.',
                'visiteur.register.profile.password_confirm.label' => 'Confirm password*',
                'visiteur.register.profile.terms.label' => 'I accept Boolts terms of use.*',
                'visiteur.register.profile.submit' => 'Sign in',
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
