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

namespace App\DataFixtures\Authentification\Visiteurs;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VisiteurRegisterVerifyCodeTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.register.verify_code.meta.title' => 'Vérification du code',
                'visiteur.register.verify_code.title' => 'Vous avez reçu un code de validation par e-mail',
                'visiteur.register.verify_code.digit_label' => 'Chiffre %position% du code',
                'visiteur.register.verify_code.submit' => 'Continuer',
                'visiteur.register.verify_code.or' => 'OU',
                'visiteur.register.verify_code.resend_code' => 'Renvoyer le code',
                'visiteur.register.verify_code.change_email' => 'Changer d’adresse e-mail',
            ],

            'en' => [
                'visiteur.register.verify_code.meta.title' => 'Code verification',
                'visiteur.register.verify_code.title' => 'You have received a verification code by email',
                'visiteur.register.verify_code.digit_label' => 'Digit %position% of the code',
                'visiteur.register.verify_code.submit' => 'Continue',
                'visiteur.register.verify_code.or' => 'OR',
                'visiteur.register.verify_code.resend_code' => 'Resend code',
                'visiteur.register.verify_code.change_email' => 'Change email address',
            ],
            'es' => [
                'visiteur.register.verify_code.meta.title' => 'Verificación del código',
                'visiteur.register.verify_code.title' => 'Has recibido un código de verificación por correo electrónico',
                'visiteur.register.verify_code.digit_label' => 'Dígito %position% del código',
                'visiteur.register.verify_code.submit' => 'Continuar',
                'visiteur.register.verify_code.or' => 'O',
                'visiteur.register.verify_code.resend_code' => 'Reenviar el código',
                'visiteur.register.verify_code.change_email' => 'Cambiar la dirección de correo electrónico',
            ],
            'de' => [
                'visiteur.register.verify_code.meta.title' => 'Code-Überprüfung',
                'visiteur.register.verify_code.title' => 'Sie haben einen Bestätigungscode per E-Mail erhalten',
                'visiteur.register.verify_code.digit_label' => 'Ziffer %position% des Codes',
                'visiteur.register.verify_code.submit' => 'Weiter',
                'visiteur.register.verify_code.or' => 'ODER',
                'visiteur.register.verify_code.resend_code' => 'Code erneut senden',
                'visiteur.register.verify_code.change_email' => 'E-Mail-Adresse ändern',
            ],
            'pt' => [
                'visiteur.register.verify_code.meta.title' => 'Verificação do código',
                'visiteur.register.verify_code.title' => 'Recebeu um código de verificação por e-mail',
                'visiteur.register.verify_code.digit_label' => 'Algarismo %position% do código',
                'visiteur.register.verify_code.submit' => 'Continuar',
                'visiteur.register.verify_code.or' => 'OU',
                'visiteur.register.verify_code.resend_code' => 'Reenviar código',
                'visiteur.register.verify_code.change_email' => 'Alterar endereço de e-mail',
            ],
            'it' => [
                'visiteur.register.verify_code.meta.title' => 'Verifica del codice',
                'visiteur.register.verify_code.title' => 'Hai ricevuto un codice di verifica via e-mail',
                'visiteur.register.verify_code.digit_label' => 'Cifra %position% del codice',
                'visiteur.register.verify_code.submit' => 'Continua',
                'visiteur.register.verify_code.or' => 'OPPURE',
                'visiteur.register.verify_code.resend_code' => 'Invia di nuovo il codice',
                'visiteur.register.verify_code.change_email' => 'Cambia indirizzo e-mail',
            ],
            'nl' => [
                'visiteur.register.verify_code.meta.title' => 'Codeverificatie',
                'visiteur.register.verify_code.title' => 'Je hebt een verificatiecode per e-mail ontvangen',
                'visiteur.register.verify_code.digit_label' => 'Cijfer %position% van de code',
                'visiteur.register.verify_code.submit' => 'Doorgaan',
                'visiteur.register.verify_code.or' => 'OF',
                'visiteur.register.verify_code.resend_code' => 'Code opnieuw verzenden',
                'visiteur.register.verify_code.change_email' => 'E-mailadres wijzigen',
            ],
            'hu' => [
                'visiteur.register.verify_code.meta.title' => 'Kód ellenőrzése',
                'visiteur.register.verify_code.title' => 'Ellenőrző kódot kapott e-mailben',
                'visiteur.register.verify_code.digit_label' => 'A kód %position%. számjegye',
                'visiteur.register.verify_code.submit' => 'Tovább',
                'visiteur.register.verify_code.or' => 'VAGY',
                'visiteur.register.verify_code.resend_code' => 'Kód újraküldése',
                'visiteur.register.verify_code.change_email' => 'E-mail-cím módosítása',
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
                $translation->setPage('visiteur_register_verify_code');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
