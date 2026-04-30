<?php

namespace App\DataFixtures\Email;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EmailVisiteurRegisterConfirmationTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.visiteur.register_confirmation.meta.title' => 'Bienvenue sur Boolts',
                'email.visiteur.register_confirmation.title' => 'Bonjour, vous êtes bien inscrit sur <span class="mail-title-accent">%site%</span> !',
                'email.visiteur.register_confirmation.cta' => 'Découvrir mon dashboard',
                'email.visiteur.register_confirmation.intro' => 'Bienvenue sur Boolts, votre plateforme.',
                'email.visiteur.register_confirmation.cgu' => 'CGU',
                'email.visiteur.register_confirmation.contact' => 'Nous contacter',
                'email.visiteur.register_confirmation.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Vous avez reçu ce message parce que vous vous êtes inscrit sur notre site le %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mails de notre part ?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Cliquez ici.',
            ],
            'en' => [
                'email.visiteur.register_confirmation.meta.title' => 'Welcome to Boolts',
                'email.visiteur.register_confirmation.title' => 'Hello, you are successfully registered on <span class="mail-title-accent">%site%</span>!',
                'email.visiteur.register_confirmation.cta' => 'Access my dashboard',
                'email.visiteur.register_confirmation.intro' => 'Welcome to Boolts, your platform.',
                'email.visiteur.register_confirmation.cgu' => 'Terms of Use',
                'email.visiteur.register_confirmation.contact' => 'Contact us',
                'email.visiteur.register_confirmation.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'You received this message because you registered on our website on %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Do you no longer wish to receive emails from us?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Click here.',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('email_visiteur_register_confirmation');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
