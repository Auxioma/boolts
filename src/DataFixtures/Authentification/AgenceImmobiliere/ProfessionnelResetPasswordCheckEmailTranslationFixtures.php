<?php

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ProfessionnelResetPasswordCheckEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.reset_password.check_email.meta.title' => 'E-mail envoyé',
                'professionnel.reset_password.check_email.title' => 'Le lien de modification a été envoyé par e-mail !',
                'professionnel.reset_password.check_email.resend_link' => 'Vous n’avez rien reçu ? Recevoir un nouvel e-mail',
            ],
            'en' => [
                'professionnel.reset_password.check_email.meta.title' => 'Email sent',
                'professionnel.reset_password.check_email.title' => 'The reset link has been sent by email!',
                'professionnel.reset_password.check_email.resend_link' => 'Didn’t receive anything? Send a new email',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('professionnel_reset_password_check_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}