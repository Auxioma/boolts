<?php

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ProfessionnelForgotPasswordTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.forgot_password.meta.title' => 'Mot de passe oublié',
                'professionnel.forgot_password.title' => 'Saisissez votre adresse e-mail',
                'professionnel.forgot_password.email.label' => 'Adresse e-mail',
                'professionnel.forgot_password.submit' => 'Envoyer le lien de modification',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('professionnel_forgot_password');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
