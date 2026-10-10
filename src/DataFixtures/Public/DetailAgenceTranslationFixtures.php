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

final class DetailAgenceTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'detail_agence.preview.message' => 'Voici comment votre agence est présentée aux particuliers.',
                'detail_agence.preview.edit' => 'Modifier',
                'detail_agence.profile.agency_type' => 'Agence immobilière',
                'detail_agence.properties.title' => 'Biens de l’agence récemment ajoutés',
                'detail_agence.properties.empty' => 'Aucun bien n’est disponible pour le moment.',
                'detail_agence.filters.button' => 'Filtres',
                'detail_agence.sort.button' => 'Tri : %label%',
                'detail_agence.sort.aria_label' => 'Trier les annonces',
                'detail_agence.sort.current.newest' => 'Plus récentes',
                'detail_agence.sort.current.oldest' => 'Plus anciennes',
                'detail_agence.sort.current.views_desc' => 'Vues : plus → moins',
                'detail_agence.sort.current.views_asc' => 'Vues : moins → plus',
                'detail_agence.sort.current.favorites_desc' => 'Favoris : plus → moins',
                'detail_agence.sort.current.favorites_asc' => 'Favoris : moins → plus',
                'detail_agence.sort.option.date_desc' => 'Date d\'ajout (récent → ancien)',
                'detail_agence.sort.option.date_asc' => 'Date d\'ajout (ancien → récent)',
                'detail_agence.sort.option.views_desc' => 'Vues (plus → moins)',
                'detail_agence.sort.option.views_asc' => 'Vues (moins → plus)',
                'detail_agence.sort.option.favorites_desc' => 'Favoris (plus → moins)',
                'detail_agence.sort.option.favorites_asc' => 'Favoris (moins → plus)',
                'detail_agence.card.title' => '%type% à %transaction%',
                'detail_agence.card.per_month' => '/mois',
                'detail_agence.card.charges_included' => 'C. Comprises',
                'detail_agence.card.charges_excluded' => 'Hors charges',
                'detail_agence.card.sale' => 'Vente',
                'detail_agence.card.price_on_request' => 'Prix sur demande',
                'detail_agence.card.bedrooms.one' => '%count% chambre',
                'detail_agence.card.bedrooms.other' => '%count% chambres',
                'detail_agence.card.bathrooms.one' => '%count% salle de bains',
                'detail_agence.card.bathrooms.other' => '%count% salles de bains',
            ],
            'en' => [
                'detail_agence.preview.message' => 'This is how your agency is presented to private individuals.',
                'detail_agence.preview.edit' => 'Edit',
                'detail_agence.profile.agency_type' => 'Real estate agency',
                'detail_agence.properties.title' => 'Recently added agency properties',
                'detail_agence.properties.empty' => 'No properties are available at the moment.',
                'detail_agence.filters.button' => 'Filters',
                'detail_agence.sort.button' => 'Sort: %label%',
                'detail_agence.sort.aria_label' => 'Sort listings',
                'detail_agence.sort.current.newest' => 'Newest',
                'detail_agence.sort.current.oldest' => 'Oldest',
                'detail_agence.sort.current.views_desc' => 'Views: most → least',
                'detail_agence.sort.current.views_asc' => 'Views: least → most',
                'detail_agence.sort.current.favorites_desc' => 'Favorites: most → least',
                'detail_agence.sort.current.favorites_asc' => 'Favorites: least → most',
                'detail_agence.sort.option.date_desc' => 'Date added (newest → oldest)',
                'detail_agence.sort.option.date_asc' => 'Date added (oldest → newest)',
                'detail_agence.sort.option.views_desc' => 'Views (most → least)',
                'detail_agence.sort.option.views_asc' => 'Views (least → most)',
                'detail_agence.sort.option.favorites_desc' => 'Favorites (most → least)',
                'detail_agence.sort.option.favorites_asc' => 'Favorites (least → most)',
                'detail_agence.card.title' => '%type% for %transaction%',
                'detail_agence.card.per_month' => '/month',
                'detail_agence.card.charges_included' => 'Charges incl.',
                'detail_agence.card.charges_excluded' => 'Excl. charges',
                'detail_agence.card.sale' => 'Sale',
                'detail_agence.card.price_on_request' => 'Price on request',
                'detail_agence.card.bedrooms.one' => '%count% bedroom',
                'detail_agence.card.bedrooms.other' => '%count% bedrooms',
                'detail_agence.card.bathrooms.one' => '%count% bathroom',
                'detail_agence.card.bathrooms.other' => '%count% bathrooms',
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
                $translation->setPage('detail_agence');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
