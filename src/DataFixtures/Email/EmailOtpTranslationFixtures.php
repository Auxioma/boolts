<?php

namespace App\DataFixtures\Email;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EmailOtpTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Bonjour, voici votre code de validation à 6 chiffres',
                'email.otp.validity' => 'Prenez en compte que ce code n’est valable que jusqu’au %expiration%',
                'email.otp.support.text' => 'Vous avez des soucis pour vous connecter ?',
                'email.otp.support.contact' => 'Essayez de contacter notre support :',
                'email.otp.privacy' => 'Politique de confidentialité',
                'email.otp.terms' => 'Conditions générales',
                'email.otp.contact' => 'Nous contacter',
                'email.otp.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.otp.received' => 'Vous avez reçu ce message parce que vous vous êtes inscrit sur notre site le %date%.',
                'email.otp.security_text' => 'Vous ne reconnaissez pas cette activité ?',
                'email.otp.security_link' => 'Cliquez ici.',
                'email.otp.security_end' => 'Nous allons vous aider à sécuriser votre compte. Sinon, aucune action n’est requise de votre part.',
            ],
            'en' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Hello, here is your 6-digit verification code',
                'email.otp.validity' => 'Please note that this code is only valid until %expiration%',
                'email.otp.support.text' => 'Having trouble signing in?',
                'email.otp.support.contact' => 'Contact our support:',
                'email.otp.privacy' => 'Privacy Policy',
                'email.otp.terms' => 'Terms & Conditions',
                'email.otp.contact' => 'Contact Us',
                'email.otp.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.otp.received' => 'You received this message because you registered on our website on %date%.',
                'email.otp.security_text' => 'Don’t recognize this activity?',
                'email.otp.security_link' => 'Click here.',
                'email.otp.security_end' => 'We will help you secure your account. Otherwise, no action is required from you.',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('email_otp');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
