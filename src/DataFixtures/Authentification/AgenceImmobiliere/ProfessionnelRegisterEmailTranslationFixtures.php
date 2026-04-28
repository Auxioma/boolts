<?php

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
                'professionnel.register.email.submit' => 'Se connecter',
                'professionnel.register.email.link' => 'Vous êtes un professionnel ?',
            ],

            'en' => [
                'professionnel.register.email.meta.title' => 'Registration',
                'professionnel.register.email.title' => 'Sign up to your professional account',
                'professionnel.register.email.apple' => 'Sign in with Apple',
                'professionnel.register.email.or' => 'OR',
                'professionnel.register.email.email.label' => 'Sign up with email address*',
                'professionnel.register.email.submit' => 'Sign in',
                'professionnel.register.email.link' => 'Are you a professional?',
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