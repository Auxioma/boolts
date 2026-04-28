<?php

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ProfessionnelRegisterVerifyCodeTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.register.verify_code.meta.title' => 'Vérification du code',
                'professionnel.register.verify_code.title' => 'Vous avez reçu un code de validation par e-mail',
                'professionnel.register.verify_code.submit' => 'Se connecter',
                'professionnel.register.verify_code.or' => 'OU',
                'professionnel.register.verify_code.resend_code' => 'Renvoyer le code',
                'professionnel.register.verify_code.change_email' => 'Changer d’adresse e-mail',
            ],
            'en' => [
                'professionnel.register.verify_code.meta.title' => 'Code verification',
                'professionnel.register.verify_code.title' => 'You have received a validation code by email',
                'professionnel.register.verify_code.submit' => 'Sign in',
                'professionnel.register.verify_code.or' => 'OR',
                'professionnel.register.verify_code.resend_code' => 'Resend code',
                'professionnel.register.verify_code.change_email' => 'Change email address',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('professionnel_register_verify_code');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}