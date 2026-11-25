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

namespace App\DataFixtures\Dashboard;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AgenceProfilePublicTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency_profile.todo.photo' => 'Ajouter une photo de profil',
                'agency_profile.todo.description' => 'Ajouter une description',
                'agency_profile.todo.email' => 'Ajouter une adresse mail de contact',
                'agency_profile.todo.hours' => 'Ajouter vos horaires',
                'agency_profile.todo.phone' => 'Ajouter un numéro de contact',
                'agency_profile.todo.languages' => 'Ajouter vos langues parlées',
                'agency_profile.view_public' => 'Voir la fiche publique',
                'agency_profile.complete' => 'Complétez votre profil pour pouvoir publier des annonces',
                'agency_profile.optional' => '(optionnel)',
                'agency_profile.name_placeholder' => 'Définissez le nom de votre agence',
                'agency_profile.description_placeholder' => 'Rédigez une courte description ici.',
                'agency_profile.address' => 'Adresse de l’agence',
                'agency_profile.address_placeholder' => 'Saisissez votre adresse postale',
                'agency_profile.phone_placeholder' => 'Définissez un numéro de contact',
                'agency_profile.whatsapp' => 'Whatsapp',
                'agency_profile.same_number' => 'Utiliser le même numéro',
                'agency_profile.email_placeholder' => 'Définissez une adresse e-mail de contact',
                'agency_profile.languages' => 'Langues parlées par l’agence',
                'agency_profile.languages_placeholder' => 'Définissez les langues parlées par l’agence',
                'agency_profile.hours' => 'Horaires',
                'common.hours.closed_lower' => 'fermé',
                'common.hours.open_lower' => 'ouvert',
                'common.and' => 'et',
                'agency_profile.hours_default' => 'Tous les jours sont fermés par défaut',
            ],
            'en' => [
                'agency_profile.todo.photo' => 'Add a profile picture',
                'agency_profile.todo.description' => 'Add a description',
                'agency_profile.todo.email' => 'Add a contact email address',
                'agency_profile.todo.hours' => 'Add your opening hours',
                'agency_profile.todo.phone' => 'Add a contact number',
                'agency_profile.todo.languages' => 'Add the languages you speak',
                'agency_profile.view_public' => 'View public profile',
                'agency_profile.complete' => 'Complete your profile to be able to publish listings',
                'agency_profile.optional' => '(optional)',
                'agency_profile.name_placeholder' => 'Set your agency name',
                'agency_profile.description_placeholder' => 'Write a short description here.',
                'agency_profile.address' => 'Agency address',
                'agency_profile.address_placeholder' => 'Enter your postal address',
                'agency_profile.phone_placeholder' => 'Set a contact number',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Use the same number',
                'agency_profile.email_placeholder' => 'Set a contact email address',
                'agency_profile.languages' => 'Languages spoken by the agency',
                'agency_profile.languages_placeholder' => 'Set the languages spoken by the agency',
                'agency_profile.hours' => 'Opening hours',
                'common.hours.closed_lower' => 'closed',
                'common.hours.open_lower' => 'open',
                'common.and' => 'and',
                'agency_profile.hours_default' => 'All days are closed by default',
            ],
            'es' => [
                'agency_profile.todo.photo' => 'Añadir una foto de perfil',
                'agency_profile.todo.description' => 'Añadir una descripción',
                'agency_profile.todo.email' => 'Añadir un correo electrónico de contacto',
                'agency_profile.todo.hours' => 'Añadir tu horario',
                'agency_profile.todo.phone' => 'Añadir un número de contacto',
                'agency_profile.todo.languages' => 'Añadir los idiomas que hablas',
                'agency_profile.view_public' => 'Ver la ficha pública',
                'agency_profile.complete' => 'Completa tu perfil para poder publicar anuncios',
                'agency_profile.optional' => '(opcional)',
                'agency_profile.name_placeholder' => 'Define el nombre de tu agencia',
                'agency_profile.description_placeholder' => 'Escribe aquí una breve descripción.',
                'agency_profile.address' => 'Dirección de la agencia',
                'agency_profile.address_placeholder' => 'Introduce tu dirección postal',
                'agency_profile.phone_placeholder' => 'Define un número de contacto',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Usar el mismo número',
                'agency_profile.email_placeholder' => 'Define un correo electrónico de contacto',
                'agency_profile.languages' => 'Idiomas hablados por la agencia',
                'agency_profile.languages_placeholder' => 'Define los idiomas hablados por la agencia',
                'agency_profile.hours' => 'Horario',
                'common.hours.closed_lower' => 'cerrado',
                'common.hours.open_lower' => 'abierto',
                'common.and' => 'y',
                'agency_profile.hours_default' => 'Todos los días están cerrados por defecto',
            ],
            'de' => [
                'agency_profile.todo.photo' => 'Profilbild hinzufügen',
                'agency_profile.todo.description' => 'Beschreibung hinzufügen',
                'agency_profile.todo.email' => 'Kontakt-E-Mail-Adresse hinzufügen',
                'agency_profile.todo.hours' => 'Öffnungszeiten hinzufügen',
                'agency_profile.todo.phone' => 'Kontaktnummer hinzufügen',
                'agency_profile.todo.languages' => 'Gesprochene Sprachen hinzufügen',
                'agency_profile.view_public' => 'Öffentliches Profil ansehen',
                'agency_profile.complete' => 'Vervollständigen Sie Ihr Profil, um Anzeigen veröffentlichen zu können',
                'agency_profile.optional' => '(optional)',
                'agency_profile.name_placeholder' => 'Legen Sie den Namen Ihrer Agentur fest',
                'agency_profile.description_placeholder' => 'Schreiben Sie hier eine kurze Beschreibung.',
                'agency_profile.address' => 'Adresse der Agentur',
                'agency_profile.address_placeholder' => 'Geben Sie Ihre Postanschrift ein',
                'agency_profile.phone_placeholder' => 'Legen Sie eine Kontaktnummer fest',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Dieselbe Nummer verwenden',
                'agency_profile.email_placeholder' => 'Legen Sie eine Kontakt-E-Mail-Adresse fest',
                'agency_profile.languages' => 'Von der Agentur gesprochene Sprachen',
                'agency_profile.languages_placeholder' => 'Legen Sie die von der Agentur gesprochenen Sprachen fest',
                'agency_profile.hours' => 'Öffnungszeiten',
                'common.hours.closed_lower' => 'geschlossen',
                'common.hours.open_lower' => 'geöffnet',
                'common.and' => 'und',
                'agency_profile.hours_default' => 'Alle Tage sind standardmäßig geschlossen',
            ],
            'pt' => [
                'agency_profile.todo.photo' => 'Adicionar uma fotografia de perfil',
                'agency_profile.todo.description' => 'Adicionar uma descrição',
                'agency_profile.todo.email' => 'Adicionar um e-mail de contacto',
                'agency_profile.todo.hours' => 'Adicionar o seu horário',
                'agency_profile.todo.phone' => 'Adicionar um número de contacto',
                'agency_profile.todo.languages' => 'Adicionar as línguas que fala',
                'agency_profile.view_public' => 'Ver o perfil público',
                'agency_profile.complete' => 'Complete o seu perfil para poder publicar anúncios',
                'agency_profile.optional' => '(opcional)',
                'agency_profile.name_placeholder' => 'Defina o nome da sua agência',
                'agency_profile.description_placeholder' => 'Escreva aqui uma breve descrição.',
                'agency_profile.address' => 'Morada da agência',
                'agency_profile.address_placeholder' => 'Introduza a sua morada postal',
                'agency_profile.phone_placeholder' => 'Defina um número de contacto',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Utilizar o mesmo número',
                'agency_profile.email_placeholder' => 'Defina um e-mail de contacto',
                'agency_profile.languages' => 'Línguas faladas pela agência',
                'agency_profile.languages_placeholder' => 'Defina as línguas faladas pela agência',
                'agency_profile.hours' => 'Horário',
                'common.hours.closed_lower' => 'fechado',
                'common.hours.open_lower' => 'aberto',
                'common.and' => 'e',
                'agency_profile.hours_default' => 'Todos os dias estão fechados por predefinição',
            ],
            'it' => [
                'agency_profile.todo.photo' => 'Aggiungi una foto del profilo',
                'agency_profile.todo.description' => 'Aggiungi una descrizione',
                'agency_profile.todo.email' => 'Aggiungi un’e-mail di contatto',
                'agency_profile.todo.hours' => 'Aggiungi i tuoi orari',
                'agency_profile.todo.phone' => 'Aggiungi un numero di contatto',
                'agency_profile.todo.languages' => 'Aggiungi le lingue parlate',
                'agency_profile.view_public' => 'Vedi la scheda pubblica',
                'agency_profile.complete' => 'Completa il tuo profilo per poter pubblicare annunci',
                'agency_profile.optional' => '(facoltativo)',
                'agency_profile.name_placeholder' => 'Imposta il nome della tua agenzia',
                'agency_profile.description_placeholder' => 'Scrivi qui una breve descrizione.',
                'agency_profile.address' => 'Indirizzo dell’agenzia',
                'agency_profile.address_placeholder' => 'Inserisci il tuo indirizzo postale',
                'agency_profile.phone_placeholder' => 'Imposta un numero di contatto',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Usa lo stesso numero',
                'agency_profile.email_placeholder' => 'Imposta un’e-mail di contatto',
                'agency_profile.languages' => 'Lingue parlate dall’agenzia',
                'agency_profile.languages_placeholder' => 'Imposta le lingue parlate dall’agenzia',
                'agency_profile.hours' => 'Orari',
                'common.hours.closed_lower' => 'chiuso',
                'common.hours.open_lower' => 'aperto',
                'common.and' => 'e',
                'agency_profile.hours_default' => 'Tutti i giorni sono chiusi per impostazione predefinita',
            ],
            'nl' => [
                'agency_profile.todo.photo' => 'Profielfoto toevoegen',
                'agency_profile.todo.description' => 'Beschrijving toevoegen',
                'agency_profile.todo.email' => 'Contact-e-mailadres toevoegen',
                'agency_profile.todo.hours' => 'Openingstijden toevoegen',
                'agency_profile.todo.phone' => 'Contactnummer toevoegen',
                'agency_profile.todo.languages' => 'Gesproken talen toevoegen',
                'agency_profile.view_public' => 'Openbaar profiel bekijken',
                'agency_profile.complete' => 'Vervolledig je profiel om advertenties te kunnen publiceren',
                'agency_profile.optional' => '(optioneel)',
                'agency_profile.name_placeholder' => 'Bepaal de naam van je kantoor',
                'agency_profile.description_placeholder' => 'Schrijf hier een korte beschrijving.',
                'agency_profile.address' => 'Adres van het kantoor',
                'agency_profile.address_placeholder' => 'Voer je postadres in',
                'agency_profile.phone_placeholder' => 'Stel een contactnummer in',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Hetzelfde nummer gebruiken',
                'agency_profile.email_placeholder' => 'Stel een contact-e-mailadres in',
                'agency_profile.languages' => 'Talen gesproken door het kantoor',
                'agency_profile.languages_placeholder' => 'Stel de door het kantoor gesproken talen in',
                'agency_profile.hours' => 'Openingstijden',
                'common.hours.closed_lower' => 'gesloten',
                'common.hours.open_lower' => 'open',
                'common.and' => 'en',
                'agency_profile.hours_default' => 'Alle dagen zijn standaard gesloten',
            ],
            'hu' => [
                'agency_profile.todo.photo' => 'Profilkép hozzáadása',
                'agency_profile.todo.description' => 'Leírás hozzáadása',
                'agency_profile.todo.email' => 'Kapcsolattartási e-mail-cím hozzáadása',
                'agency_profile.todo.hours' => 'Nyitvatartás hozzáadása',
                'agency_profile.todo.phone' => 'Kapcsolattartási telefonszám hozzáadása',
                'agency_profile.todo.languages' => 'Beszélt nyelvek hozzáadása',
                'agency_profile.view_public' => 'Nyilvános profil megtekintése',
                'agency_profile.complete' => 'Egészítse ki profilját, hogy hirdetéseket tehessen közzé',
                'agency_profile.optional' => '(nem kötelező)',
                'agency_profile.name_placeholder' => 'Adja meg ügynöksége nevét',
                'agency_profile.description_placeholder' => 'Írjon ide egy rövid leírást.',
                'agency_profile.address' => 'Az ügynökség címe',
                'agency_profile.address_placeholder' => 'Adja meg postai címét',
                'agency_profile.phone_placeholder' => 'Adjon meg kapcsolattartási telefonszámot',
                'agency_profile.whatsapp' => 'WhatsApp',
                'agency_profile.same_number' => 'Ugyanazon szám használata',
                'agency_profile.email_placeholder' => 'Adjon meg kapcsolattartási e-mail-címet',
                'agency_profile.languages' => 'Az ügynökség által beszélt nyelvek',
                'agency_profile.languages_placeholder' => 'Adja meg az ügynökség által beszélt nyelveket',
                'agency_profile.hours' => 'Nyitvatartás',
                'common.hours.closed_lower' => 'zárva',
                'common.hours.open_lower' => 'nyitva',
                'common.and' => 'és',
                'agency_profile.hours_default' => 'Alapértelmezés szerint minden nap zárva',
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
                $translation->setPage('profile_public');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
