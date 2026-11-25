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
            'es' => [
                'email.agence.password_reset.meta.title' => 'Restablecimiento de contraseña',
                'email.agence.password_reset.title' => 'Hola, aquí tienes el enlace para cambiar tu contraseña',
                'email.agence.password_reset.cta' => 'Cambiar mi contraseña',
                'email.agence.password_reset.validity' => 'Este enlace es válido hasta el %expiration%',
                'email.agence.password_reset.support.text' => '¿Tienes problemas para iniciar sesión?',
                'email.agence.password_reset.support.contact' => 'Contacta con nuestro soporte:',
                'email.agence.password_reset.cgu' => 'Condiciones de uso',
                'email.agence.password_reset.contact' => 'Contacto',
                'email.agence.password_reset.legal' => 'Boolts es una marca registrada. Instagram y Facebook son marcas de Meta Inc.',
                'email.agence.password_reset.received' => 'Has recibido este correo tras una solicitud de restablecimiento de contraseña el %date%.',
                'email.agence.password_reset.unsubscribe_text' => '¿Ya no quieres recibir correos?',
                'email.agence.password_reset.unsubscribe_link' => 'Haz clic aquí.',
            ],
            'de' => [
                'email.agence.password_reset.meta.title' => 'Passwort zurücksetzen',
                'email.agence.password_reset.title' => 'Hallo, hier ist der Link zum Zurücksetzen Ihres Passworts',
                'email.agence.password_reset.cta' => 'Mein Passwort zurücksetzen',
                'email.agence.password_reset.validity' => 'Dieser Link ist gültig bis %expiration%',
                'email.agence.password_reset.support.text' => 'Probleme bei der Anmeldung?',
                'email.agence.password_reset.support.contact' => 'Kontaktieren Sie unseren Support:',
                'email.agence.password_reset.cgu' => 'Nutzungsbedingungen',
                'email.agence.password_reset.contact' => 'Kontakt',
                'email.agence.password_reset.legal' => 'Boolts ist eine eingetragene Marke. Instagram und Facebook sind Marken von Meta Inc.',
                'email.agence.password_reset.received' => 'Sie haben diese E-Mail nach einer Anfrage zum Zurücksetzen des Passworts am %date% erhalten.',
                'email.agence.password_reset.unsubscribe_text' => 'Sie möchten keine E-Mails mehr erhalten?',
                'email.agence.password_reset.unsubscribe_link' => 'Klicken Sie hier.',
            ],
            'pt' => [
                'email.agence.password_reset.meta.title' => 'Redefinição da palavra-passe',
                'email.agence.password_reset.title' => 'Olá, aqui está o link para alterar a sua palavra-passe',
                'email.agence.password_reset.cta' => 'Alterar a minha palavra-passe',
                'email.agence.password_reset.validity' => 'Este link é válido até %expiration%',
                'email.agence.password_reset.support.text' => 'Tem dificuldades em iniciar sessão?',
                'email.agence.password_reset.support.contact' => 'Contacte o nosso suporte:',
                'email.agence.password_reset.cgu' => 'Termos de utilização',
                'email.agence.password_reset.contact' => 'Contacte-nos',
                'email.agence.password_reset.legal' => 'Boolts é uma marca registada. Instagram e Facebook são marcas da Meta Inc.',
                'email.agence.password_reset.received' => 'Recebeu este e-mail na sequência de um pedido de redefinição da palavra-passe em %date%.',
                'email.agence.password_reset.unsubscribe_text' => 'Já não pretende receber e-mails?',
                'email.agence.password_reset.unsubscribe_link' => 'Clique aqui.',
            ],
            'it' => [
                'email.agence.password_reset.meta.title' => 'Reimpostazione della password',
                'email.agence.password_reset.title' => 'Ciao, ecco il link per modificare la tua password',
                'email.agence.password_reset.cta' => 'Modifica la mia password',
                'email.agence.password_reset.validity' => 'Questo link è valido fino al %expiration%',
                'email.agence.password_reset.support.text' => 'Hai problemi ad accedere?',
                'email.agence.password_reset.support.contact' => 'Contatta la nostra assistenza:',
                'email.agence.password_reset.cgu' => 'Condizioni d’uso',
                'email.agence.password_reset.contact' => 'Contattaci',
                'email.agence.password_reset.legal' => 'Boolts è un marchio registrato. Instagram e Facebook sono marchi di Meta Inc.',
                'email.agence.password_reset.received' => 'Hai ricevuto questa e-mail in seguito a una richiesta di reimpostazione della password del %date%.',
                'email.agence.password_reset.unsubscribe_text' => 'Non vuoi più ricevere e-mail?',
                'email.agence.password_reset.unsubscribe_link' => 'Clicca qui.',
            ],
            'nl' => [
                'email.agence.password_reset.meta.title' => 'Wachtwoord opnieuw instellen',
                'email.agence.password_reset.title' => 'Hallo, hier is de link om je wachtwoord te wijzigen',
                'email.agence.password_reset.cta' => 'Mijn wachtwoord wijzigen',
                'email.agence.password_reset.validity' => 'Deze link is geldig tot %expiration%',
                'email.agence.password_reset.support.text' => 'Problemen met inloggen?',
                'email.agence.password_reset.support.contact' => 'Neem contact op met onze support:',
                'email.agence.password_reset.cgu' => 'Gebruiksvoorwaarden',
                'email.agence.password_reset.contact' => 'Contact',
                'email.agence.password_reset.legal' => 'Boolts is een geregistreerd handelsmerk. Instagram en Facebook zijn handelsmerken van Meta Inc.',
                'email.agence.password_reset.received' => 'Je hebt deze e-mail ontvangen na een verzoek om je wachtwoord opnieuw in te stellen op %date%.',
                'email.agence.password_reset.unsubscribe_text' => 'Wil je geen e-mails meer ontvangen?',
                'email.agence.password_reset.unsubscribe_link' => 'Klik hier.',
            ],
            'hu' => [
                'email.agence.password_reset.meta.title' => 'Jelszó visszaállítása',
                'email.agence.password_reset.title' => 'Üdvözöljük! Itt a link jelszava módosításához',
                'email.agence.password_reset.cta' => 'Jelszavam módosítása',
                'email.agence.password_reset.validity' => 'Ez a link eddig érvényes: %expiration%',
                'email.agence.password_reset.support.text' => 'Problémája van a bejelentkezéssel?',
                'email.agence.password_reset.support.contact' => 'Lépjen kapcsolatba ügyfélszolgálatunkkal:',
                'email.agence.password_reset.cgu' => 'Felhasználási feltételek',
                'email.agence.password_reset.contact' => 'Kapcsolat',
                'email.agence.password_reset.legal' => 'A Boolts bejegyzett védjegy. Az Instagram és a Facebook a Meta Inc. védjegyei.',
                'email.agence.password_reset.received' => 'Ezt az e-mailt egy %date% napján kért jelszó-visszaállítás miatt kapta.',
                'email.agence.password_reset.unsubscribe_text' => 'Nem szeretne több e-mailt kapni?',
                'email.agence.password_reset.unsubscribe_link' => 'Kattintson ide.',
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
                $translation->setPage('email_agence_password_reset');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
