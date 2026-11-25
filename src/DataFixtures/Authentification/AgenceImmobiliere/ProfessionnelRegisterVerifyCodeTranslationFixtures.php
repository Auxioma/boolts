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

namespace App\DataFixtures\Authentification\AgenceImmobiliere;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ProfessionnelRegisterVerifyCodeTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'professionnel.register.verify_code.meta.title' => 'Vérification du code',
                'professionnel.register.verify_code.title' => 'Vous avez reçu un code de validation par e-mail',
                'professionnel.register.verify_code.submit' => 'Continuer',
                'professionnel.register.verify_code.or' => 'OU',
                'professionnel.register.verify_code.resend_code' => 'Renvoyer le code',
                'professionnel.register.verify_code.change_email' => 'Changer d’adresse e-mail',
            ],

            'en' => [
                'professionnel.register.verify_code.meta.title' => 'Code verification',
                'professionnel.register.verify_code.title' => 'You have received a verification code by email',
                'professionnel.register.verify_code.submit' => 'Continue',
                'professionnel.register.verify_code.or' => 'OR',
                'professionnel.register.verify_code.resend_code' => 'Resend code',
                'professionnel.register.verify_code.change_email' => 'Change email address',
            ],
            'es' => [
                'professionnel.register.verify_code.meta.title' => 'Verificación del código',
                'professionnel.register.verify_code.title' => 'Has recibido un código de verificación por correo electrónico',
                'professionnel.register.verify_code.submit' => 'Continuar',
                'professionnel.register.verify_code.or' => 'O',
                'professionnel.register.verify_code.resend_code' => 'Reenviar el código',
                'professionnel.register.verify_code.change_email' => 'Cambiar la dirección de correo electrónico',
            ],
            'de' => [
                'professionnel.register.verify_code.meta.title' => 'Code-Überprüfung',
                'professionnel.register.verify_code.title' => 'Sie haben einen Bestätigungscode per E-Mail erhalten',
                'professionnel.register.verify_code.submit' => 'Weiter',
                'professionnel.register.verify_code.or' => 'ODER',
                'professionnel.register.verify_code.resend_code' => 'Code erneut senden',
                'professionnel.register.verify_code.change_email' => 'E-Mail-Adresse ändern',
            ],
            'pt' => [
                'professionnel.register.verify_code.meta.title' => 'Verificação do código',
                'professionnel.register.verify_code.title' => 'Recebeu um código de verificação por e-mail',
                'professionnel.register.verify_code.submit' => 'Continuar',
                'professionnel.register.verify_code.or' => 'OU',
                'professionnel.register.verify_code.resend_code' => 'Reenviar código',
                'professionnel.register.verify_code.change_email' => 'Alterar endereço de e-mail',
            ],
            'it' => [
                'professionnel.register.verify_code.meta.title' => 'Verifica del codice',
                'professionnel.register.verify_code.title' => 'Hai ricevuto un codice di verifica via e-mail',
                'professionnel.register.verify_code.submit' => 'Continua',
                'professionnel.register.verify_code.or' => 'OPPURE',
                'professionnel.register.verify_code.resend_code' => 'Invia di nuovo il codice',
                'professionnel.register.verify_code.change_email' => 'Cambia indirizzo e-mail',
            ],
            'nl' => [
                'professionnel.register.verify_code.meta.title' => 'Codeverificatie',
                'professionnel.register.verify_code.title' => 'Je hebt een verificatiecode per e-mail ontvangen',
                'professionnel.register.verify_code.submit' => 'Doorgaan',
                'professionnel.register.verify_code.or' => 'OF',
                'professionnel.register.verify_code.resend_code' => 'Code opnieuw verzenden',
                'professionnel.register.verify_code.change_email' => 'E-mailadres wijzigen',
            ],
            'hu' => [
                'professionnel.register.verify_code.meta.title' => 'Kód ellenőrzése',
                'professionnel.register.verify_code.title' => 'Ellenőrző kódot kapott e-mailben',
                'professionnel.register.verify_code.submit' => 'Tovább',
                'professionnel.register.verify_code.or' => 'VAGY',
                'professionnel.register.verify_code.resend_code' => 'Kód újraküldése',
                'professionnel.register.verify_code.change_email' => 'E-mail-cím módosítása',
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
                $translation->setPage('professionnel_register_verify_code');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
