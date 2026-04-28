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
                'visiteur.reset_password.reset.password.placeholder' => 'Entrez votre nouveau mot de passe',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirmez votre mot de passe',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Confirmez votre mot de passe',
                'visiteur.reset_password.reset.submit' => 'Modifier le mot de passe',
            ],

            'en' => [
                'visiteur.reset_password.reset.meta.title' => 'New password',
                'visiteur.reset_password.reset.title' => 'Change your password',
                'visiteur.reset_password.reset.password.label' => 'New password',
                'visiteur.reset_password.reset.password.placeholder' => 'Enter your new password',
                'visiteur.reset_password.reset.password_confirm.label' => 'Confirm password',
                'visiteur.reset_password.reset.password_confirm.placeholder' => 'Confirm your password',
                'visiteur.reset_password.reset.submit' => 'Update password',
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
