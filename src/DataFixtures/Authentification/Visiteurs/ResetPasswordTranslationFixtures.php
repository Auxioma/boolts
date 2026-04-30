<?php

namespace App\DataFixtures\Authentification\Visiteurs;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ResetPasswordTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.reset_password.reset.meta.title' => 'Nouveau mot de passe',
                'visiteur.reset_password.reset.title' => 'Modifiez votre mot de passe',
                'visiteur.reset_password.reset.password.label' => 'Nouveau mot de passe',
                'visiteur.reset_password.reset.password.placeholder' => 'Veuillez entrer votre mot de passe',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirmer le mot de passe',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Veuillez confirmer votre mot de passe',
                'visiteur.reset_password.reset.submit' => 'Modifier mon mot de passe',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('reset_password');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
