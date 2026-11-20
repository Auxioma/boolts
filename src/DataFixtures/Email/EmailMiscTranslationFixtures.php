<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures\Email;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EmailMiscTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.two_factor.title' => 'Votre code de connexion sécurisé',
                'email.two_factor.expires' => 'Il expire dans 10 minutes.',
                'email.two_factor.code' => 'Code : %code%',
                'email.two_factor.subject' => 'Votre code de connexion sécurisé - Boolts',
                'email.login_code.subject' => 'Votre code de connexion Boolts',
                'email.password_reset.subject' => 'Votre demande de réinitialisation de mot de passe',
                'email.contact.subject' => 'Nouvelle demande de contact',
                'email.otp.date_pattern' => "EEEE d MMMM 'à' HH:mm",
            ],
            'en' => [
                'email.two_factor.title' => 'Your secure sign-in code',
                'email.two_factor.expires' => 'It expires in 10 minutes.',
                'email.two_factor.code' => 'Code: %code%',
                'email.two_factor.subject' => 'Your secure sign-in code - Boolts',
                'email.login_code.subject' => 'Your Boolts sign-in code',
                'email.password_reset.subject' => 'Your password reset request',
                'email.contact.subject' => 'New contact request',
                'email.otp.date_pattern' => "EEEE, MMMM d 'at' HH:mm",
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = FixtureEntityHelper::findOrCreate($manager, Translation::class, [
                    'translationKey' => $key,
                    'locale' => $locale,
                ]);
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
