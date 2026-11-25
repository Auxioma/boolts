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

namespace App\DataFixtures\Public;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class PublicCommonTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'search.filters.title' => 'Filtres',
                'search.filters.close' => 'Fermer',
                'search.filters.reset' => 'Réinitialiser les filtres',
                'search.filters.transaction' => 'Nature de la propriété*',
                'search.filters.all' => 'Toutes',
                'search.filters.property_type' => 'Type de propriété*',
                'search.filters.see_more' => 'Voir plus',
                'search.filters.location' => 'Localisation',
                'search.filters.country' => 'Pays',
                'search.filters.city' => 'Ville',
                'search.filters.district' => 'Quartier',
                'search.filters.minimum' => 'Minimum',
                'search.filters.maximum' => 'Maximum',
                'search.filters.energy' => 'Performance énergétique',
                'search.filters.show' => 'Afficher les',
                'search.filters.result.one' => 'logement',
                'search.filters.result.other' => 'logements',
                'search.filters.country_placeholder' => 'Rechercher un pays',
                'search.filters.city_placeholder' => 'Rechercher une ville',
                'search.filters.district_placeholder' => 'Rechercher un quartier',
                'search.filters.range.bedrooms' => 'Chambres',
                'search.filters.range.bathrooms' => 'Salle de bain',
                'search.filters.range.surface' => 'Surface',
                'search.filters.range.construction_year' => 'Année de construction',
                'search.filters.range.price_range' => 'Fourchette de prix',
                'detail_bien.contact.message' => 'Envoyer un message',
                'detail_agence.contact.message' => 'Envoyer un message à l’agence',
                'home.locale_banner.aria_label' => 'Choix du pays, de la langue et de la devise',
                'home.locale_banner.title' => 'Vous consultez le site : %country%.',
                'home.locale_banner.title_fr' => 'Vous consultez le site français.',
                'home.locale_banner.title_ma' => 'Vous consultez le site marocain.',
                'home.locale_banner.text' => 'Sélectionnez un autre pays pour changer le contenu du site.',
                'home.locale_banner.close' => 'Fermer',
                'home.locale_banner.country' => 'Pays',
                'home.locale_banner.language' => 'Langue',
                'home.locale_banner.currency' => 'Devise',
                'home.locale_banner.modal.title' => 'Choix du pays',
                'home.locale_banner.modal.suggested' => 'Pays suggéré',
                'home.locale_banner.modal.choose' => 'Choisissez un pays',
                'home.locale_banner.modal.language_title' => 'Choix de la langue',
                'home.locale_banner.modal.language_suggested' => 'Langue suggérée',
                'home.locale_banner.modal.language_choose' => 'Choisissez une langue',
                'home.locale_banner.modal.currency_title' => 'Choix de la devise',
                'home.locale_banner.modal.currency_suggested' => 'Devise suggérée',
                'home.locale_banner.modal.currency_choose' => 'Choisissez une devise',
            ],
            'en' => [
                'search.filters.title' => 'Filters',
                'search.filters.close' => 'Close',
                'search.filters.reset' => 'Reset filters',
                'search.filters.transaction' => 'Property nature*',
                'search.filters.all' => 'All',
                'search.filters.property_type' => 'Property type*',
                'search.filters.see_more' => 'See more',
                'search.filters.location' => 'Location',
                'search.filters.country' => 'Country',
                'search.filters.city' => 'City',
                'search.filters.district' => 'District',
                'search.filters.minimum' => 'Minimum',
                'search.filters.maximum' => 'Maximum',
                'search.filters.energy' => 'Energy performance',
                'search.filters.show' => 'Show',
                'search.filters.result.one' => 'property',
                'search.filters.result.other' => 'properties',
                'search.filters.country_placeholder' => 'Search for a country',
                'search.filters.city_placeholder' => 'Search for a city',
                'search.filters.district_placeholder' => 'Search for a district',
                'search.filters.range.bedrooms' => 'Bedrooms',
                'search.filters.range.bathrooms' => 'Bathrooms',
                'search.filters.range.surface' => 'Surface area',
                'search.filters.range.construction_year' => 'Year built',
                'search.filters.range.price_range' => 'Price range',
                'detail_bien.contact.message' => 'Send a message',
                'detail_agence.contact.message' => 'Send a message to the agency',
                'home.locale_banner.aria_label' => 'Country, language and currency selection',
                'home.locale_banner.title' => 'You are viewing the site for: %country%.',
                'home.locale_banner.title_fr' => 'You are viewing the French site.',
                'home.locale_banner.title_ma' => 'You are viewing the Moroccan site.',
                'home.locale_banner.text' => 'Select another country to change the site content.',
                'home.locale_banner.close' => 'Close',
                'home.locale_banner.country' => 'Country',
                'home.locale_banner.language' => 'Language',
                'home.locale_banner.currency' => 'Currency',
                'home.locale_banner.modal.title' => 'Country selection',
                'home.locale_banner.modal.suggested' => 'Suggested country',
                'home.locale_banner.modal.choose' => 'Choose a country',
                'home.locale_banner.modal.language_title' => 'Language selection',
                'home.locale_banner.modal.language_suggested' => 'Suggested language',
                'home.locale_banner.modal.language_choose' => 'Choose a language',
                'home.locale_banner.modal.currency_title' => 'Currency selection',
                'home.locale_banner.modal.currency_suggested' => 'Suggested currency',
                'home.locale_banner.modal.currency_choose' => 'Choose a currency',
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
                $translation->setPage('public');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
