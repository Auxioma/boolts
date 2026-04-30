<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures\Email;

use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EmailPasswordResetTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.password_reset.meta.title' => 'Modification du mot de passe',
                'email.password_reset.title' => 'Bonjour, voici le lien pour modifier votre mot de passe',
                'email.password_reset.cta' => 'Modifier mon mot de passe',
                'email.password_reset.validity' => 'Prenez en compte que ce lien n’est valable que jusqu’au %expiration%',
                'email.password_reset.support.text' => 'Vous avez des soucis pour vous connecter ?',
                'email.password_reset.support.contact' => 'Contactez notre support :',
                'email.password_reset.cgu' => 'CGU',
                'email.password_reset.contact' => 'Nous contacter',
                'email.password_reset.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.password_reset.received' => 'Vous avez reçu ce message parce que vous avez demandé la modification de votre mot de passe le %date%.',
                'email.password_reset.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mails de notre part ?',
                'email.password_reset.unsubscribe_link' => 'Cliquez ici.',
            ],
            'en' => [
                'email.password_reset.meta.title' => 'Password reset',
                'email.password_reset.title' => 'Hello, here is the link to reset your password',
                'email.password_reset.cta' => 'Reset my password',
                'email.password_reset.validity' => 'Please note that this link is only valid until %expiration%',
                'email.password_reset.support.text' => 'Having trouble signing in?',
                'email.password_reset.support.contact' => 'Contact our support team:',
                'email.password_reset.cgu' => 'Terms of Use',
                'email.password_reset.contact' => 'Contact us',
                'email.password_reset.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.password_reset.received' => 'You received this message because you requested a password reset on %date%.',
                'email.password_reset.unsubscribe_text' => 'Do you no longer wish to receive emails from us?',
                'email.password_reset.unsubscribe_link' => 'Click here.',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('email_password_reset');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
