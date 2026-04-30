<?php

namespace App\DataFixtures\Authentification\Visiteurs;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ResetPasswordCheckEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-mail envoyé',
                'visiteur.reset_password.check_email.title' => 'Le lien de réinitialisation a été envoyé par e-mail !',
                'visiteur.reset_password.check_email.resend_link' => 'Vous n’avez rien reçu ? Recevoir un nouvel e-mail',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {

                // 🔒 Optionnel mais PRO : éviter les doublons si tu relances les fixtures
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('reset_password_check_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
