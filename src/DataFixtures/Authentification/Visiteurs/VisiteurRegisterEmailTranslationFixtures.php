<?php

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

            'en' => [
                'visiteur.register.email.meta.title' => 'Registration',
                'visiteur.register.email.title' => 'Registration',
                'visiteur.register.email.apple' => 'Sign up with Apple',
                'visiteur.register.email.google' => 'Sign up with Google',
                'visiteur.register.email.or' => 'OR',
                'visiteur.register.email.email.label' => 'Sign up with email address*',
                'visiteur.register.email.email.placeholder' => 'Enter your email address',
                'visiteur.register.email.submit' => 'Continue',
                'visiteur.register.email.professional_link' => 'Are you a professional?',
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
