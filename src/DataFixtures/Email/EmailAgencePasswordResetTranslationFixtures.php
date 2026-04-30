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

final class EmailAgencePasswordResetTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.agence.password_reset.meta.title' => 'Modification du mot de passe',
                'email.agence.password_reset.title' => 'Bonjour, voici le lien pour modifier votre mot de passe',
                'email.agence.password_reset.cta' => 'Modifier mon mot de passe',
                'email.agence.password_reset.validity' => 'Ce lien est valable jusqu’au %expiration%',
                'email.agence.password_reset.support.text' => 'Vous avez des soucis pour vous connecter ?',
                'email.agence.password_reset.support.contact' => 'Contactez notre support :',
                'email.agence.password_reset.cgu' => 'CGU',
                'email.agence.password_reset.contact' => 'Nous contacter',
                'email.agence.password_reset.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.agence.password_reset.received' => 'Vous avez reçu ce message suite à une demande de réinitialisation le %date%.',
                'email.agence.password_reset.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mails de notre part ?',
                'email.agence.password_reset.unsubscribe_link' => 'Cliquez ici.',
            ],

            'en' => [
                'email.agence.password_reset.meta.title' => 'Password reset',
                'email.agence.password_reset.title' => 'Hello, here is the link to reset your password',
                'email.agence.password_reset.cta' => 'Reset my password',
                'email.agence.password_reset.validity' => 'This link is valid until %expiration%',
                'email.agence.password_reset.support.text' => 'Having trouble signing in?',
                'email.agence.password_reset.support.contact' => 'Contact our support:',
                'email.agence.password_reset.cgu' => 'Terms of Use',
                'email.agence.password_reset.contact' => 'Contact us',
                'email.agence.password_reset.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.agence.password_reset.received' => 'You received this email after a password reset request on %date%.',
                'email.agence.password_reset.unsubscribe_text' => 'Do you no longer wish to receive emails?',
                'email.agence.password_reset.unsubscribe_link' => 'Click here.',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = new Translation();
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('email_agence_password_reset');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
