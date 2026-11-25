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
            'es' => [
                'email.password_reset.meta.title' => 'Restablecimiento de contraseña',
                'email.password_reset.title' => 'Hola, aquí tienes el enlace para cambiar tu contraseña',
                'email.password_reset.cta' => 'Cambiar mi contraseña',
                'email.password_reset.validity' => 'Ten en cuenta que este enlace solo es válido hasta el %expiration%',
                'email.password_reset.support.text' => '¿Tienes problemas para iniciar sesión?',
                'email.password_reset.support.contact' => 'Contacta con nuestro equipo de soporte:',
                'email.password_reset.cgu' => 'Condiciones de uso',
                'email.password_reset.contact' => 'Contacto',
                'email.password_reset.legal' => 'Boolts es una marca registrada. Instagram y Facebook son marcas de Meta Inc.',
                'email.password_reset.received' => 'Has recibido este mensaje porque solicitaste cambiar tu contraseña el %date%.',
                'email.password_reset.unsubscribe_text' => '¿Ya no quieres recibir nuestros correos?',
                'email.password_reset.unsubscribe_link' => 'Haz clic aquí.',
            ],
            'de' => [
                'email.password_reset.meta.title' => 'Passwort zurücksetzen',
                'email.password_reset.title' => 'Hallo, hier ist der Link zum Zurücksetzen Ihres Passworts',
                'email.password_reset.cta' => 'Mein Passwort zurücksetzen',
                'email.password_reset.validity' => 'Bitte beachten Sie, dass dieser Link nur bis %expiration% gültig ist',
                'email.password_reset.support.text' => 'Probleme bei der Anmeldung?',
                'email.password_reset.support.contact' => 'Kontaktieren Sie unser Support-Team:',
                'email.password_reset.cgu' => 'Nutzungsbedingungen',
                'email.password_reset.contact' => 'Kontakt',
                'email.password_reset.legal' => 'Boolts ist eine eingetragene Marke. Instagram und Facebook sind Marken von Meta Inc.',
                'email.password_reset.received' => 'Sie haben diese Nachricht erhalten, weil Sie am %date% das Zurücksetzen Ihres Passworts angefordert haben.',
                'email.password_reset.unsubscribe_text' => 'Sie möchten keine E-Mails mehr von uns erhalten?',
                'email.password_reset.unsubscribe_link' => 'Klicken Sie hier.',
            ],
            'pt' => [
                'email.password_reset.meta.title' => 'Redefinição da palavra-passe',
                'email.password_reset.title' => 'Olá, aqui está o link para alterar a sua palavra-passe',
                'email.password_reset.cta' => 'Alterar a minha palavra-passe',
                'email.password_reset.validity' => 'Tenha em atenção que este link só é válido até %expiration%',
                'email.password_reset.support.text' => 'Tem dificuldades em iniciar sessão?',
                'email.password_reset.support.contact' => 'Contacte a nossa equipa de suporte:',
                'email.password_reset.cgu' => 'Termos de utilização',
                'email.password_reset.contact' => 'Contacte-nos',
                'email.password_reset.legal' => 'Boolts é uma marca registada. Instagram e Facebook são marcas da Meta Inc.',
                'email.password_reset.received' => 'Recebeu esta mensagem porque pediu a alteração da sua palavra-passe em %date%.',
                'email.password_reset.unsubscribe_text' => 'Já não pretende receber os nossos e-mails?',
                'email.password_reset.unsubscribe_link' => 'Clique aqui.',
            ],
            'it' => [
                'email.password_reset.meta.title' => 'Reimpostazione della password',
                'email.password_reset.title' => 'Ciao, ecco il link per modificare la tua password',
                'email.password_reset.cta' => 'Modifica la mia password',
                'email.password_reset.validity' => 'Tieni presente che questo link è valido solo fino al %expiration%',
                'email.password_reset.support.text' => 'Hai problemi ad accedere?',
                'email.password_reset.support.contact' => 'Contatta il nostro team di assistenza:',
                'email.password_reset.cgu' => 'Condizioni d’uso',
                'email.password_reset.contact' => 'Contattaci',
                'email.password_reset.legal' => 'Boolts è un marchio registrato. Instagram e Facebook sono marchi di Meta Inc.',
                'email.password_reset.received' => 'Hai ricevuto questo messaggio perché hai richiesto la modifica della password il %date%.',
                'email.password_reset.unsubscribe_text' => 'Non vuoi più ricevere e-mail da parte nostra?',
                'email.password_reset.unsubscribe_link' => 'Clicca qui.',
            ],
            'nl' => [
                'email.password_reset.meta.title' => 'Wachtwoord opnieuw instellen',
                'email.password_reset.title' => 'Hallo, hier is de link om je wachtwoord te wijzigen',
                'email.password_reset.cta' => 'Mijn wachtwoord wijzigen',
                'email.password_reset.validity' => 'Let op: deze link is slechts geldig tot %expiration%',
                'email.password_reset.support.text' => 'Problemen met inloggen?',
                'email.password_reset.support.contact' => 'Neem contact op met ons supportteam:',
                'email.password_reset.cgu' => 'Gebruiksvoorwaarden',
                'email.password_reset.contact' => 'Contact',
                'email.password_reset.legal' => 'Boolts is een geregistreerd handelsmerk. Instagram en Facebook zijn handelsmerken van Meta Inc.',
                'email.password_reset.received' => 'Je hebt dit bericht ontvangen omdat je op %date% hebt gevraagd je wachtwoord te wijzigen.',
                'email.password_reset.unsubscribe_text' => 'Wil je geen e-mails meer van ons ontvangen?',
                'email.password_reset.unsubscribe_link' => 'Klik hier.',
            ],
            'hu' => [
                'email.password_reset.meta.title' => 'Jelszó visszaállítása',
                'email.password_reset.title' => 'Üdvözöljük! Itt a link jelszava módosításához',
                'email.password_reset.cta' => 'Jelszavam módosítása',
                'email.password_reset.validity' => 'Felhívjuk figyelmét, hogy ez a link csak eddig érvényes: %expiration%',
                'email.password_reset.support.text' => 'Problémája van a bejelentkezéssel?',
                'email.password_reset.support.contact' => 'Lépjen kapcsolatba ügyfélszolgálati csapatunkkal:',
                'email.password_reset.cgu' => 'Felhasználási feltételek',
                'email.password_reset.contact' => 'Kapcsolat',
                'email.password_reset.legal' => 'A Boolts bejegyzett védjegy. Az Instagram és a Facebook a Meta Inc. védjegyei.',
                'email.password_reset.received' => 'Ezt az üzenetet azért kapta, mert %date% napján jelszómódosítást kért.',
                'email.password_reset.unsubscribe_text' => 'Nem szeretne több e-mailt kapni tőlünk?',
                'email.password_reset.unsubscribe_link' => 'Kattintson ide.',
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
                $translation->setPage('email_password_reset');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
