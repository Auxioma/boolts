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

final class EmailRegisterConfirmationTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.register_confirmation.meta.title' => 'Bienvenue sur Boolts',
                'email.register_confirmation.title' => 'Bonjour, vous êtes bien inscrit sur <span class="mail-title-accent">%site%</span> !',
                'email.register_confirmation.cta' => 'Découvrir mon dashboard',
                'email.register_confirmation.intro' => 'Bienvenue sur Boolts, votre plateforme professionnelle.',
                'email.register_confirmation.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.register_confirmation.received' => 'Vous avez reçu ce message parce que vous vous êtes inscrit sur notre site le %date%.',
                'email.register_confirmation.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mails de notre part ?',
                'email.register_confirmation.unsubscribe_link' => 'Cliquez ici.',
                'email.common.cgu' => 'CGU',
                'email.common.contact' => 'Nous contacter',
            ],

            'en' => [
                'email.register_confirmation.meta.title' => 'Welcome to Boolts',
                'email.register_confirmation.title' => 'Hello, you are successfully registered on <span class="mail-title-accent">%site%</span>!',
                'email.register_confirmation.cta' => 'Access my dashboard',
                'email.register_confirmation.intro' => 'Welcome to Boolts, your professional platform.',
                'email.register_confirmation.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.register_confirmation.received' => 'You received this message because you registered on our website on %date%.',
                'email.register_confirmation.unsubscribe_text' => 'Do you no longer wish to receive emails from us?',
                'email.register_confirmation.unsubscribe_link' => 'Click here.',
                'email.common.cgu' => 'Terms of Use',
                'email.common.contact' => 'Contact us',
            ],
            'es' => [
                'email.register_confirmation.meta.title' => 'Bienvenido a Boolts',
                'email.register_confirmation.title' => 'Hola, ¡ya estás registrado en <span class="mail-title-accent">%site%</span>!',
                'email.register_confirmation.cta' => 'Descubrir mi panel',
                'email.register_confirmation.intro' => 'Bienvenido a Boolts, tu plataforma profesional.',
                'email.register_confirmation.legal' => 'Boolts es una marca registrada. Instagram y Facebook son marcas de Meta Inc.',
                'email.register_confirmation.received' => 'Has recibido este mensaje porque te registraste en nuestro sitio el %date%.',
                'email.register_confirmation.unsubscribe_text' => '¿Ya no quieres recibir nuestros correos?',
                'email.register_confirmation.unsubscribe_link' => 'Haz clic aquí.',
                'email.common.cgu' => 'Condiciones de uso',
                'email.common.contact' => 'Contacto',
            ],
            'de' => [
                'email.register_confirmation.meta.title' => 'Willkommen bei Boolts',
                'email.register_confirmation.title' => 'Hallo, Sie sind jetzt bei <span class="mail-title-accent">%site%</span> registriert!',
                'email.register_confirmation.cta' => 'Mein Dashboard entdecken',
                'email.register_confirmation.intro' => 'Willkommen bei Boolts, Ihrer professionellen Plattform.',
                'email.register_confirmation.legal' => 'Boolts ist eine eingetragene Marke. Instagram und Facebook sind Marken von Meta Inc.',
                'email.register_confirmation.received' => 'Sie haben diese Nachricht erhalten, weil Sie sich am %date% auf unserer Website registriert haben.',
                'email.register_confirmation.unsubscribe_text' => 'Sie möchten keine E-Mails mehr von uns erhalten?',
                'email.register_confirmation.unsubscribe_link' => 'Klicken Sie hier.',
                'email.common.cgu' => 'Nutzungsbedingungen',
                'email.common.contact' => 'Kontakt',
            ],
            'pt' => [
                'email.register_confirmation.meta.title' => 'Bem-vindo à Boolts',
                'email.register_confirmation.title' => 'Olá, já está registado na <span class="mail-title-accent">%site%</span>!',
                'email.register_confirmation.cta' => 'Descobrir o meu painel',
                'email.register_confirmation.intro' => 'Bem-vindo à Boolts, a sua plataforma profissional.',
                'email.register_confirmation.legal' => 'Boolts é uma marca registada. Instagram e Facebook são marcas da Meta Inc.',
                'email.register_confirmation.received' => 'Recebeu esta mensagem porque se registou no nosso site em %date%.',
                'email.register_confirmation.unsubscribe_text' => 'Já não pretende receber os nossos e-mails?',
                'email.register_confirmation.unsubscribe_link' => 'Clique aqui.',
                'email.common.cgu' => 'Termos de utilização',
                'email.common.contact' => 'Contacte-nos',
            ],
            'it' => [
                'email.register_confirmation.meta.title' => 'Benvenuto su Boolts',
                'email.register_confirmation.title' => 'Ciao, ora sei registrato su <span class="mail-title-accent">%site%</span>!',
                'email.register_confirmation.cta' => 'Scopri la mia dashboard',
                'email.register_confirmation.intro' => 'Benvenuto su Boolts, la tua piattaforma professionale.',
                'email.register_confirmation.legal' => 'Boolts è un marchio registrato. Instagram e Facebook sono marchi di Meta Inc.',
                'email.register_confirmation.received' => 'Hai ricevuto questo messaggio perché ti sei registrato sul nostro sito il %date%.',
                'email.register_confirmation.unsubscribe_text' => 'Non vuoi più ricevere e-mail da parte nostra?',
                'email.register_confirmation.unsubscribe_link' => 'Clicca qui.',
                'email.common.cgu' => 'Condizioni d’uso',
                'email.common.contact' => 'Contattaci',
            ],
            'nl' => [
                'email.register_confirmation.meta.title' => 'Welkom bij Boolts',
                'email.register_confirmation.title' => 'Hallo, je bent nu geregistreerd op <span class="mail-title-accent">%site%</span>!',
                'email.register_confirmation.cta' => 'Mijn dashboard ontdekken',
                'email.register_confirmation.intro' => 'Welkom bij Boolts, je professionele platform.',
                'email.register_confirmation.legal' => 'Boolts is een geregistreerd handelsmerk. Instagram en Facebook zijn handelsmerken van Meta Inc.',
                'email.register_confirmation.received' => 'Je hebt dit bericht ontvangen omdat je je op %date% op onze website hebt geregistreerd.',
                'email.register_confirmation.unsubscribe_text' => 'Wil je geen e-mails meer van ons ontvangen?',
                'email.register_confirmation.unsubscribe_link' => 'Klik hier.',
                'email.common.cgu' => 'Gebruiksvoorwaarden',
                'email.common.contact' => 'Contact',
            ],
            'hu' => [
                'email.register_confirmation.meta.title' => 'Üdvözöljük a Booltson',
                'email.register_confirmation.title' => 'Üdvözöljük! Sikeresen regisztrált a(z) <span class="mail-title-accent">%site%</span> oldalon!',
                'email.register_confirmation.cta' => 'Irányítópultom felfedezése',
                'email.register_confirmation.intro' => 'Üdvözöljük a Booltson, az Ön szakmai platformján.',
                'email.register_confirmation.legal' => 'A Boolts bejegyzett védjegy. Az Instagram és a Facebook a Meta Inc. védjegyei.',
                'email.register_confirmation.received' => 'Ezt az üzenetet azért kapta, mert %date% napján regisztrált oldalunkon.',
                'email.register_confirmation.unsubscribe_text' => 'Nem szeretne több e-mailt kapni tőlünk?',
                'email.register_confirmation.unsubscribe_link' => 'Kattintson ide.',
                'email.common.cgu' => 'Felhasználási feltételek',
                'email.common.contact' => 'Kapcsolat',
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
                $translation->setPage('email_register_confirmation');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
