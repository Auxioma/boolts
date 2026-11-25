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
            'es' => [
                'email.two_factor.title' => 'Tu código de inicio de sesión seguro',
                'email.two_factor.expires' => 'Caduca en 10 minutos.',
                'email.two_factor.code' => 'Código: %code%',
                'email.two_factor.subject' => 'Tu código de inicio de sesión seguro - Boolts',
                'email.login_code.subject' => 'Tu código de inicio de sesión de Boolts',
                'email.password_reset.subject' => 'Tu solicitud de restablecimiento de contraseña',
                'email.contact.subject' => 'Nueva solicitud de contacto',
                'email.otp.date_pattern' => 'EEEE d \'de\' MMMM \'a las\' HH:mm',
            ],
            'de' => [
                'email.two_factor.title' => 'Ihr sicherer Anmeldecode',
                'email.two_factor.expires' => 'Er läuft in 10 Minuten ab.',
                'email.two_factor.code' => 'Code: %code%',
                'email.two_factor.subject' => 'Ihr sicherer Anmeldecode - Boolts',
                'email.login_code.subject' => 'Ihr Boolts-Anmeldecode',
                'email.password_reset.subject' => 'Ihre Anfrage zum Zurücksetzen des Passworts',
                'email.contact.subject' => 'Neue Kontaktanfrage',
                'email.otp.date_pattern' => 'EEEE, d. MMMM \'um\' HH:mm',
            ],
            'pt' => [
                'email.two_factor.title' => 'O seu código de início de sessão seguro',
                'email.two_factor.expires' => 'Expira dentro de 10 minutos.',
                'email.two_factor.code' => 'Código: %code%',
                'email.two_factor.subject' => 'O seu código de início de sessão seguro - Boolts',
                'email.login_code.subject' => 'O seu código de início de sessão Boolts',
                'email.password_reset.subject' => 'O seu pedido de redefinição da palavra-passe',
                'email.contact.subject' => 'Novo pedido de contacto',
                'email.otp.date_pattern' => 'EEEE, d \'de\' MMMM \'às\' HH:mm',
            ],
            'it' => [
                'email.two_factor.title' => 'Il tuo codice di accesso sicuro',
                'email.two_factor.expires' => 'Scade tra 10 minuti.',
                'email.two_factor.code' => 'Codice: %code%',
                'email.two_factor.subject' => 'Il tuo codice di accesso sicuro - Boolts',
                'email.login_code.subject' => 'Il tuo codice di accesso Boolts',
                'email.password_reset.subject' => 'La tua richiesta di reimpostazione della password',
                'email.contact.subject' => 'Nuova richiesta di contatto',
                'email.otp.date_pattern' => 'EEEE d MMMM \'alle\' HH:mm',
            ],
            'nl' => [
                'email.two_factor.title' => 'Je beveiligde inlogcode',
                'email.two_factor.expires' => 'Hij verloopt over 10 minuten.',
                'email.two_factor.code' => 'Code: %code%',
                'email.two_factor.subject' => 'Je beveiligde inlogcode - Boolts',
                'email.login_code.subject' => 'Je Boolts-inlogcode',
                'email.password_reset.subject' => 'Je verzoek om je wachtwoord opnieuw in te stellen',
                'email.contact.subject' => 'Nieuw contactverzoek',
                'email.otp.date_pattern' => 'EEEE d MMMM \'om\' HH:mm',
            ],
            'hu' => [
                'email.two_factor.title' => 'Biztonságos bejelentkezési kódja',
                'email.two_factor.expires' => '10 perc múlva lejár.',
                'email.two_factor.code' => 'Kód: %code%',
                'email.two_factor.subject' => 'Biztonságos bejelentkezési kódja - Boolts',
                'email.login_code.subject' => 'Boolts bejelentkezési kódja',
                'email.password_reset.subject' => 'Jelszó-visszaállítási kérelme',
                'email.contact.subject' => 'Új kapcsolatfelvételi kérés',
                'email.otp.date_pattern' => 'MMMM d., EEEE HH:mm',
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
