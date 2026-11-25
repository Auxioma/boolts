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

final class AgenceImmobiliereCommonTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency.invoices.title' => 'Mes factures',
                'dashboard.back_to_more' => 'Retour au menu Plus',
                'agency_dashboard.custom_period' => 'Période personnalisée',
                'agency_dashboard.apply_short' => 'Appliquer',
                'agency.invoices.search' => 'Recherchez un numéro de facture',
                'agency.invoices.created_at' => 'Créé le %date%',
                'agency.invoices.empty' => 'Vous n’avez aucune facture pour le moment.',
                'agency.listings.title' => 'Mes biens immobiliers',
                'agency.listings.created_pending' => 'Votre annonce a bien été créée. Elle est actuellement en cours d’analyse.',
                'agency.listings.view_all' => 'Voir mes biens',
                'mes_biens.topbar.title' => 'Gestion du bien',
                'agency_listings.list_title' => 'Liste',
            ],
            'en' => [
                'agency.invoices.title' => 'My invoices',
                'dashboard.back_to_more' => 'Back to More menu',
                'agency_dashboard.custom_period' => 'Custom period',
                'agency_dashboard.apply_short' => 'Apply',
                'agency.invoices.search' => 'Search for an invoice number',
                'agency.invoices.created_at' => 'Created on %date%',
                'agency.invoices.empty' => 'You don’t have any invoices yet.',
                'agency.listings.title' => 'My properties',
                'agency.listings.created_pending' => 'Your listing has been created. It is currently being reviewed.',
                'agency.listings.view_all' => 'View my properties',
                'mes_biens.topbar.title' => 'Property management',
                'agency_listings.list_title' => 'List',
            ],
            'es' => [
                'agency.invoices.title' => 'Mis facturas',
                'dashboard.back_to_more' => 'Volver al menú Más',
                'agency_dashboard.custom_period' => 'Período personalizado',
                'agency_dashboard.apply_short' => 'Aplicar',
                'agency.invoices.search' => 'Busca un número de factura',
                'agency.invoices.created_at' => 'Creada el %date%',
                'agency.invoices.empty' => 'Aún no tienes ninguna factura.',
                'agency.listings.title' => 'Mis inmuebles',
                'agency.listings.created_pending' => 'Tu anuncio se ha creado. Actualmente está en revisión.',
                'agency.listings.view_all' => 'Ver mis inmuebles',
                'mes_biens.topbar.title' => 'Gestión del inmueble',
                'agency_listings.list_title' => 'Lista',
            ],
            'de' => [
                'agency.invoices.title' => 'Meine Rechnungen',
                'dashboard.back_to_more' => 'Zurück zum Menü Mehr',
                'agency_dashboard.custom_period' => 'Benutzerdefinierter Zeitraum',
                'agency_dashboard.apply_short' => 'Anwenden',
                'agency.invoices.search' => 'Rechnungsnummer suchen',
                'agency.invoices.created_at' => 'Erstellt am %date%',
                'agency.invoices.empty' => 'Sie haben noch keine Rechnungen.',
                'agency.listings.title' => 'Meine Immobilien',
                'agency.listings.created_pending' => 'Ihre Anzeige wurde erstellt. Sie wird derzeit geprüft.',
                'agency.listings.view_all' => 'Meine Immobilien ansehen',
                'mes_biens.topbar.title' => 'Immobilienverwaltung',
                'agency_listings.list_title' => 'Liste',
            ],
            'pt' => [
                'agency.invoices.title' => 'As minhas faturas',
                'dashboard.back_to_more' => 'Voltar ao menu Mais',
                'agency_dashboard.custom_period' => 'Período personalizado',
                'agency_dashboard.apply_short' => 'Aplicar',
                'agency.invoices.search' => 'Pesquise um número de fatura',
                'agency.invoices.created_at' => 'Criada em %date%',
                'agency.invoices.empty' => 'Ainda não tem nenhuma fatura.',
                'agency.listings.title' => 'Os meus imóveis',
                'agency.listings.created_pending' => 'O seu anúncio foi criado. Está atualmente em análise.',
                'agency.listings.view_all' => 'Ver os meus imóveis',
                'mes_biens.topbar.title' => 'Gestão do imóvel',
                'agency_listings.list_title' => 'Lista',
            ],
            'it' => [
                'agency.invoices.title' => 'Le mie fatture',
                'dashboard.back_to_more' => 'Torna al menu Altro',
                'agency_dashboard.custom_period' => 'Periodo personalizzato',
                'agency_dashboard.apply_short' => 'Applica',
                'agency.invoices.search' => 'Cerca un numero di fattura',
                'agency.invoices.created_at' => 'Creata il %date%',
                'agency.invoices.empty' => 'Non hai ancora nessuna fattura.',
                'agency.listings.title' => 'I miei immobili',
                'agency.listings.created_pending' => 'Il tuo annuncio è stato creato. È attualmente in fase di verifica.',
                'agency.listings.view_all' => 'Vedi i miei immobili',
                'mes_biens.topbar.title' => 'Gestione dell’immobile',
                'agency_listings.list_title' => 'Elenco',
            ],
            'nl' => [
                'agency.invoices.title' => 'Mijn facturen',
                'dashboard.back_to_more' => 'Terug naar menu Meer',
                'agency_dashboard.custom_period' => 'Aangepaste periode',
                'agency_dashboard.apply_short' => 'Toepassen',
                'agency.invoices.search' => 'Zoek een factuurnummer',
                'agency.invoices.created_at' => 'Aangemaakt op %date%',
                'agency.invoices.empty' => 'Je hebt nog geen facturen.',
                'agency.listings.title' => 'Mijn woningen',
                'agency.listings.created_pending' => 'Je advertentie is aangemaakt. Ze wordt momenteel beoordeeld.',
                'agency.listings.view_all' => 'Mijn woningen bekijken',
                'mes_biens.topbar.title' => 'Beheer van de woning',
                'agency_listings.list_title' => 'Lijst',
            ],
            'hu' => [
                'agency.invoices.title' => 'Számláim',
                'dashboard.back_to_more' => 'Vissza a Továbbiak menübe',
                'agency_dashboard.custom_period' => 'Egyéni időszak',
                'agency_dashboard.apply_short' => 'Alkalmaz',
                'agency.invoices.search' => 'Számlaszám keresése',
                'agency.invoices.created_at' => 'Létrehozva: %date%',
                'agency.invoices.empty' => 'Még nincs számlája.',
                'agency.listings.title' => 'Ingatlanjaim',
                'agency.listings.created_pending' => 'Hirdetése létrejött. Jelenleg ellenőrzés alatt áll.',
                'agency.listings.view_all' => 'Ingatlanjaim megtekintése',
                'mes_biens.topbar.title' => 'Ingatlan kezelése',
                'agency_listings.list_title' => 'Lista',
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
                $translation->setPage('dashboard_agence');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
