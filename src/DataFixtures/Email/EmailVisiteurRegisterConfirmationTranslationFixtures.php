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

final class EmailVisiteurRegisterConfirmationTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.visiteur.register_confirmation.meta.title' => 'Bienvenue sur Boolts',
                'email.visiteur.register_confirmation.title' => 'Bonjour, vous êtes bien inscrit sur <span class="mail-title-accent">%site%</span> !',
                'email.visiteur.register_confirmation.cta' => 'Découvrir mon dashboard',
                'email.visiteur.register_confirmation.intro' => 'Bienvenue sur Boolts, votre plateforme.',
                'email.visiteur.register_confirmation.cgu' => 'CGU',
                'email.visiteur.register_confirmation.contact' => 'Nous contacter',
                'email.visiteur.register_confirmation.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Vous avez reçu ce message parce que vous vous êtes inscrit sur notre site le %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mails de notre part ?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Cliquez ici.',
            ],
            'en' => [
                'email.visiteur.register_confirmation.meta.title' => 'Welcome to Boolts',
                'email.visiteur.register_confirmation.title' => 'Hello, you are successfully registered on <span class="mail-title-accent">%site%</span>!',
                'email.visiteur.register_confirmation.cta' => 'Access my dashboard',
                'email.visiteur.register_confirmation.intro' => 'Welcome to Boolts, your platform.',
                'email.visiteur.register_confirmation.cgu' => 'Terms of Use',
                'email.visiteur.register_confirmation.contact' => 'Contact us',
                'email.visiteur.register_confirmation.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'You received this message because you registered on our website on %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Do you no longer wish to receive emails from us?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Click here.',
            ],
            'es' => [
                'email.visiteur.register_confirmation.meta.title' => 'Bienvenido a Boolts',
                'email.visiteur.register_confirmation.title' => 'Hola, ¡ya estás registrado en <span class="mail-title-accent">%site%</span>!',
                'email.visiteur.register_confirmation.cta' => 'Descubrir mi panel',
                'email.visiteur.register_confirmation.intro' => 'Bienvenido a Boolts, tu plataforma.',
                'email.visiteur.register_confirmation.cgu' => 'Condiciones de uso',
                'email.visiteur.register_confirmation.contact' => 'Contacto',
                'email.visiteur.register_confirmation.legal' => 'Boolts es una marca registrada. Instagram y Facebook son marcas de Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Has recibido este mensaje porque te registraste en nuestro sitio el %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => '¿Ya no quieres recibir nuestros correos?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Haz clic aquí.',
            ],
            'de' => [
                'email.visiteur.register_confirmation.meta.title' => 'Willkommen bei Boolts',
                'email.visiteur.register_confirmation.title' => 'Hallo, Sie sind jetzt bei <span class="mail-title-accent">%site%</span> registriert!',
                'email.visiteur.register_confirmation.cta' => 'Mein Dashboard entdecken',
                'email.visiteur.register_confirmation.intro' => 'Willkommen bei Boolts, Ihrer Plattform.',
                'email.visiteur.register_confirmation.cgu' => 'Nutzungsbedingungen',
                'email.visiteur.register_confirmation.contact' => 'Kontakt',
                'email.visiteur.register_confirmation.legal' => 'Boolts ist eine eingetragene Marke. Instagram und Facebook sind Marken von Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Sie haben diese Nachricht erhalten, weil Sie sich am %date% auf unserer Website registriert haben.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Sie möchten keine E-Mails mehr von uns erhalten?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Klicken Sie hier.',
            ],
            'pt' => [
                'email.visiteur.register_confirmation.meta.title' => 'Bem-vindo à Boolts',
                'email.visiteur.register_confirmation.title' => 'Olá, já está registado na <span class="mail-title-accent">%site%</span>!',
                'email.visiteur.register_confirmation.cta' => 'Descobrir o meu painel',
                'email.visiteur.register_confirmation.intro' => 'Bem-vindo à Boolts, a sua plataforma.',
                'email.visiteur.register_confirmation.cgu' => 'Termos de utilização',
                'email.visiteur.register_confirmation.contact' => 'Contacte-nos',
                'email.visiteur.register_confirmation.legal' => 'Boolts é uma marca registada. Instagram e Facebook são marcas da Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Recebeu esta mensagem porque se registou no nosso site em %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Já não pretende receber os nossos e-mails?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Clique aqui.',
            ],
            'it' => [
                'email.visiteur.register_confirmation.meta.title' => 'Benvenuto su Boolts',
                'email.visiteur.register_confirmation.title' => 'Ciao, ora sei registrato su <span class="mail-title-accent">%site%</span>!',
                'email.visiteur.register_confirmation.cta' => 'Scopri la mia dashboard',
                'email.visiteur.register_confirmation.intro' => 'Benvenuto su Boolts, la tua piattaforma.',
                'email.visiteur.register_confirmation.cgu' => 'Condizioni d’uso',
                'email.visiteur.register_confirmation.contact' => 'Contattaci',
                'email.visiteur.register_confirmation.legal' => 'Boolts è un marchio registrato. Instagram e Facebook sono marchi di Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Hai ricevuto questo messaggio perché ti sei registrato sul nostro sito il %date%.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Non vuoi più ricevere e-mail da parte nostra?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Clicca qui.',
            ],
            'nl' => [
                'email.visiteur.register_confirmation.meta.title' => 'Welkom bij Boolts',
                'email.visiteur.register_confirmation.title' => 'Hallo, je bent nu geregistreerd op <span class="mail-title-accent">%site%</span>!',
                'email.visiteur.register_confirmation.cta' => 'Mijn dashboard ontdekken',
                'email.visiteur.register_confirmation.intro' => 'Welkom bij Boolts, jouw platform.',
                'email.visiteur.register_confirmation.cgu' => 'Gebruiksvoorwaarden',
                'email.visiteur.register_confirmation.contact' => 'Contact',
                'email.visiteur.register_confirmation.legal' => 'Boolts is een geregistreerd handelsmerk. Instagram en Facebook zijn handelsmerken van Meta Inc.',
                'email.visiteur.register_confirmation.received' => 'Je hebt dit bericht ontvangen omdat je je op %date% op onze website hebt geregistreerd.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Wil je geen e-mails meer van ons ontvangen?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Klik hier.',
            ],
            'hu' => [
                'email.visiteur.register_confirmation.meta.title' => 'Üdvözöljük a Booltson',
                'email.visiteur.register_confirmation.title' => 'Üdvözöljük! Sikeresen regisztrált a(z) <span class="mail-title-accent">%site%</span> oldalon!',
                'email.visiteur.register_confirmation.cta' => 'Irányítópultom felfedezése',
                'email.visiteur.register_confirmation.intro' => 'Üdvözöljük a Booltson, az Ön platformján.',
                'email.visiteur.register_confirmation.cgu' => 'Felhasználási feltételek',
                'email.visiteur.register_confirmation.contact' => 'Kapcsolat',
                'email.visiteur.register_confirmation.legal' => 'A Boolts bejegyzett védjegy. Az Instagram és a Facebook a Meta Inc. védjegyei.',
                'email.visiteur.register_confirmation.received' => 'Ezt az üzenetet azért kapta, mert %date% napján regisztrált oldalunkon.',
                'email.visiteur.register_confirmation.unsubscribe_text' => 'Nem szeretne több e-mailt kapni tőlünk?',
                'email.visiteur.register_confirmation.unsubscribe_link' => 'Kattintson ide.',
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
                $translation->setPage('email_visiteur_register_confirmation');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
