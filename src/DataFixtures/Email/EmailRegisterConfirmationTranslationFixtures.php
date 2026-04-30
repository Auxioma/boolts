<?php

namespace App\DataFixtures\Email;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EmailRegisterConfirmationTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.register_confirmation.meta.title' => 'Bienvenue sur Boolts',
                'email.register_confirmation.title' => 'Bonjour, vous êtes bien inscrit sur <span class="mail-title-accent">%site%</span> !',
                'email.register_confirmation.cta' => 'Découvrir mon dashboard',
                'email.register_confirmation.intro' => 'Bienvenue sur Boolts, votre plateforme professionnelle.',
                'email.register_confirmation.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.register_confirmation.received' => 'Vous avez reçu ce message parce que vous vous êtes inscrit sur notre site le %date%.',
                'email.register_confirmation.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mails de notre part ?',
                'email.register_confirmation.unsubscribe_link' => 'Cliquez ici.',
                'email.common.cgu' => 'CGU',
                'email.common.contact' => 'Nous contacter',
            ],

            'en' => [
                'email.register_confirmation.meta.title' => 'Welcome to Boolts',
                'email.register_confirmation.title' => 'Hello, you are successfully registered on <span class="mail-title-accent">%site%</span>!',
                'email.register_confirmation.cta' => 'Access my dashboard',
                'email.register_confirmation.intro' => 'Welcome to Boolts, your professional platform.',
                'email.register_confirmation.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.register_confirmation.received' => 'You received this message because you registered on our website on %date%.',
                'email.register_confirmation.unsubscribe_text' => 'Do you no longer wish to receive emails from us?',
                'email.register_confirmation.unsubscribe_link' => 'Click here.',
                'email.common.cgu' => 'Terms of Use',
                'email.common.contact' => 'Contact us',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('email_register_confirmation');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
