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
            'es' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Hola, aquí tienes tu código de verificación de 6 dígitos',
                'email.otp.validity' => 'Ten en cuenta que este código solo es válido hasta el %expiration%',
                'email.otp.support.text' => '¿Tienes problemas para iniciar sesión?',
                'email.otp.support.contact' => 'Contacta con nuestro soporte:',
                'email.otp.privacy' => 'Política de privacidad',
                'email.otp.terms' => 'Condiciones generales',
                'email.otp.contact' => 'Contacto',
                'email.otp.legal' => 'Boolts es una marca registrada. Instagram y Facebook son marcas de Meta Inc.',
                'email.otp.received' => 'Has recibido este mensaje porque te registraste en nuestro sitio el %date%.',
                'email.otp.security_text' => '¿No reconoces esta actividad?',
                'email.otp.security_link' => 'Haz clic aquí.',
                'email.otp.security_end' => 'Te ayudaremos a proteger tu cuenta. De lo contrario, no tienes que hacer nada.',
            ],
            'de' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Hallo, hier ist Ihr 6-stelliger Bestätigungscode',
                'email.otp.validity' => 'Bitte beachten Sie, dass dieser Code nur bis %expiration% gültig ist',
                'email.otp.support.text' => 'Probleme bei der Anmeldung?',
                'email.otp.support.contact' => 'Kontaktieren Sie unseren Support:',
                'email.otp.privacy' => 'Datenschutzerklärung',
                'email.otp.terms' => 'Allgemeine Geschäftsbedingungen',
                'email.otp.contact' => 'Kontakt',
                'email.otp.legal' => 'Boolts ist eine eingetragene Marke. Instagram und Facebook sind Marken von Meta Inc.',
                'email.otp.received' => 'Sie haben diese Nachricht erhalten, weil Sie sich am %date% auf unserer Website registriert haben.',
                'email.otp.security_text' => 'Sie erkennen diese Aktivität nicht?',
                'email.otp.security_link' => 'Klicken Sie hier.',
                'email.otp.security_end' => 'Wir helfen Ihnen, Ihr Konto zu sichern. Andernfalls ist keine Aktion Ihrerseits erforderlich.',
            ],
            'pt' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Olá, aqui está o seu código de verificação de 6 dígitos',
                'email.otp.validity' => 'Tenha em atenção que este código só é válido até %expiration%',
                'email.otp.support.text' => 'Tem dificuldades em iniciar sessão?',
                'email.otp.support.contact' => 'Contacte o nosso suporte:',
                'email.otp.privacy' => 'Política de privacidade',
                'email.otp.terms' => 'Termos e condições',
                'email.otp.contact' => 'Contacte-nos',
                'email.otp.legal' => 'Boolts é uma marca registada. Instagram e Facebook são marcas da Meta Inc.',
                'email.otp.received' => 'Recebeu esta mensagem porque se registou no nosso site em %date%.',
                'email.otp.security_text' => 'Não reconhece esta atividade?',
                'email.otp.security_link' => 'Clique aqui.',
                'email.otp.security_end' => 'Vamos ajudá-lo a proteger a sua conta. Caso contrário, não é necessária nenhuma ação da sua parte.',
            ],
            'it' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Ciao, ecco il tuo codice di verifica a 6 cifre',
                'email.otp.validity' => 'Tieni presente che questo codice è valido solo fino al %expiration%',
                'email.otp.support.text' => 'Hai problemi ad accedere?',
                'email.otp.support.contact' => 'Contatta la nostra assistenza:',
                'email.otp.privacy' => 'Informativa sulla privacy',
                'email.otp.terms' => 'Termini e condizioni',
                'email.otp.contact' => 'Contattaci',
                'email.otp.legal' => 'Boolts è un marchio registrato. Instagram e Facebook sono marchi di Meta Inc.',
                'email.otp.received' => 'Hai ricevuto questo messaggio perché ti sei registrato sul nostro sito il %date%.',
                'email.otp.security_text' => 'Non riconosci questa attività?',
                'email.otp.security_link' => 'Clicca qui.',
                'email.otp.security_end' => 'Ti aiuteremo a proteggere il tuo account. Altrimenti, non è richiesta alcuna azione da parte tua.',
            ],
            'nl' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Hallo, hier is je verificatiecode van 6 cijfers',
                'email.otp.validity' => 'Let op: deze code is slechts geldig tot %expiration%',
                'email.otp.support.text' => 'Problemen met inloggen?',
                'email.otp.support.contact' => 'Neem contact op met onze support:',
                'email.otp.privacy' => 'Privacybeleid',
                'email.otp.terms' => 'Algemene voorwaarden',
                'email.otp.contact' => 'Contact',
                'email.otp.legal' => 'Boolts is een geregistreerd handelsmerk. Instagram en Facebook zijn handelsmerken van Meta Inc.',
                'email.otp.received' => 'Je hebt dit bericht ontvangen omdat je je op %date% op onze website hebt geregistreerd.',
                'email.otp.security_text' => 'Herken je deze activiteit niet?',
                'email.otp.security_link' => 'Klik hier.',
                'email.otp.security_end' => 'We helpen je je account te beveiligen. Anders hoef je niets te doen.',
            ],
            'hu' => [
                'email.otp.meta.title' => 'BOOLTS',
                'email.otp.title' => 'Üdvözöljük! Itt a 6 számjegyű ellenőrző kódja',
                'email.otp.validity' => 'Felhívjuk figyelmét, hogy ez a kód csak eddig érvényes: %expiration%',
                'email.otp.support.text' => 'Problémája van a bejelentkezéssel?',
                'email.otp.support.contact' => 'Lépjen kapcsolatba ügyfélszolgálatunkkal:',
                'email.otp.privacy' => 'Adatvédelmi irányelvek',
                'email.otp.terms' => 'Általános szerződési feltételek',
                'email.otp.contact' => 'Kapcsolat',
                'email.otp.legal' => 'A Boolts bejegyzett védjegy. Az Instagram és a Facebook a Meta Inc. védjegyei.',
                'email.otp.received' => 'Ezt az üzenetet azért kapta, mert %date% napján regisztrált oldalunkon.',
                'email.otp.security_text' => 'Nem ismeri fel ezt a tevékenységet?',
                'email.otp.security_link' => 'Kattintson ide.',
                'email.otp.security_end' => 'Segítünk fiókja védelmében. Egyébként nincs teendője.',
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
                $translation->setPage('email_otp');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
