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
                'professionnel.register.verify_code.submit' => 'Continuer',
                'professionnel.register.verify_code.or' => 'OU',
                'professionnel.register.verify_code.resend_code' => 'Renvoyer le code',
                'professionnel.register.verify_code.change_email' => 'Changer d’adresse e-mail',
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
