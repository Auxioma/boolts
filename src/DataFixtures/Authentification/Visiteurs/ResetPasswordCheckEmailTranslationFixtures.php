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

final class ResetPasswordCheckEmailTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-mail envoyé',
                'visiteur.reset_password.check_email.title' => 'Le lien de réinitialisation a été envoyé par e-mail !',
                'visiteur.reset_password.check_email.resend_link' => 'Vous n’avez rien reçu ? Recevoir un nouvel e-mail',
            ],

            'en' => [
                'visiteur.reset_password.check_email.meta.title' => 'Email sent',
                'visiteur.reset_password.check_email.title' => 'The reset link has been sent by email!',
                'visiteur.reset_password.check_email.resend_link' => 'Did not receive anything? Get a new email',
            ],
            'es' => [
                'visiteur.reset_password.check_email.meta.title' => 'Correo enviado',
                'visiteur.reset_password.check_email.title' => '¡El enlace de restablecimiento se ha enviado por correo electrónico!',
                'visiteur.reset_password.check_email.resend_link' => '¿No has recibido nada? Recibir un nuevo correo',
            ],
            'de' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-Mail gesendet',
                'visiteur.reset_password.check_email.title' => 'Der Link zum Zurücksetzen wurde per E-Mail gesendet!',
                'visiteur.reset_password.check_email.resend_link' => 'Nichts erhalten? Neue E-Mail anfordern',
            ],
            'pt' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-mail enviado',
                'visiteur.reset_password.check_email.title' => 'O link de redefinição foi enviado por e-mail!',
                'visiteur.reset_password.check_email.resend_link' => 'Não recebeu nada? Receber um novo e-mail',
            ],
            'it' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-mail inviata',
                'visiteur.reset_password.check_email.title' => 'Il link di reimpostazione è stato inviato via e-mail!',
                'visiteur.reset_password.check_email.resend_link' => 'Non hai ricevuto nulla? Ricevi una nuova e-mail',
            ],
            'nl' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-mail verzonden',
                'visiteur.reset_password.check_email.title' => 'De resetlink is per e-mail verzonden!',
                'visiteur.reset_password.check_email.resend_link' => 'Niets ontvangen? Nieuwe e-mail ontvangen',
            ],
            'hu' => [
                'visiteur.reset_password.check_email.meta.title' => 'E-mail elküldve',
                'visiteur.reset_password.check_email.title' => 'A visszaállítási linket elküldtük e-mailben!',
                'visiteur.reset_password.check_email.resend_link' => 'Nem kapott semmit? Új e-mail kérése',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                // 🔒 Optionnel mais PRO : éviter les doublons si tu relances les fixtures
                $translation = FixtureEntityHelper::findOrCreate($manager, Translation::class, [
                    'translationKey' => $key,
                    'locale' => $locale,
                ]);
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('reset_password_check_email');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
