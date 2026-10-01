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

final class SearchTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'search.meta.title' => 'Boolts - Recherche',
                'search.submit' => 'Rechercher',
                'search.toolbar.aria_label' => 'Recherche et filtres',
                'search.toolbar.back' => 'Retour',
                'search.toolbar.view_switch' => 'Choisir l’affichage',
                'search.toolbar.list' => 'Liste',
                'search.toolbar.map' => 'Carte',
                'search.toolbar.filters' => 'Filtres',
                'search.results.count.one' => '%count% logement trouvé',
                'search.results.count.other' => '%count% logements trouvés',
                'search.list.empty' => 'Aucun bien n’est disponible pour le moment.',
                'search.card.title' => '%type% à %transaction%',
                'search.card.default_title' => 'Logement',
                'search.card.available_title' => 'Logement disponible',
                'search.card.default_description' => 'Appartement très spacieux et lumineux.',
                'search.card.per_month' => '/mois',
                'search.card.charges_included' => 'C. Comprises',
                'search.card.charges_excluded' => 'Hors charges',
                'search.card.sale' => 'Vente',
                'search.card.price_on_request' => 'Prix sur demande',
                'search.card.bedrooms.one' => '%count% chambre',
                'search.card.bedrooms.other' => '%count% chambres',
                'search.card.bathrooms.one' => '%count% salle de bains',
                'search.card.bathrooms.other' => '%count% salles de bains',
                'search.card.no_address' => 'Adresse non renseignée',
                'search.card.favorite.add' => 'Ajouter aux favoris',
                'search.card.favorite.remove' => 'Retirer des favoris',
                'search.map.empty' => 'Aucun logement trouvé dans cette zone de la carte.',
                'search.map.list_aria_label' => 'Liste des logements',
                'search.map.map_aria_label' => 'Carte des logements',
                'search.map.expand' => 'Agrandir la carte',
                'search.map.close_preview' => 'Fermer l’aperçu',
                'search.map.reference' => 'Référence : %reference%',
                'search.map.type' => 'Type : %type%',
                'search.map.marker.view' => 'Voir',
                'search.map.marker.view_title' => 'Voir %title%',
                'search.map.marker.view_default' => 'Voir le logement',
                'search.map.error.load' => 'La carte ne peut pas être chargée pour le moment.',
                'search.map.error.token' => 'La carte est indisponible : clé Mapbox manquante ou invalide.',
                'search.map.error.init' => 'Erreur pendant l’initialisation de la carte : %message%',
                'search.map.error.http' => 'Erreur HTTP %status%.',
                'search.map.error.fetch' => 'Erreur pendant le chargement.',
                'search.pagination.aria_label' => 'Pagination',
                'search.pagination.map_aria_label' => 'Pagination des logements sur la carte',
                'search.pagination.previous' => 'Précédent',
                'search.pagination.next' => 'Suivant',
            ],
            'en' => [
                'search.meta.title' => 'Boolts - Search',
                'search.submit' => 'Search',
                'search.toolbar.aria_label' => 'Search and filters',
                'search.toolbar.back' => 'Back',
                'search.toolbar.view_switch' => 'Choose display',
                'search.toolbar.list' => 'List',
                'search.toolbar.map' => 'Map',
                'search.toolbar.filters' => 'Filters',
                'search.results.count.one' => '%count% property found',
                'search.results.count.other' => '%count% properties found',
                'search.list.empty' => 'No property is available at the moment.',
                'search.card.title' => '%type% for %transaction%',
                'search.card.default_title' => 'Property',
                'search.card.available_title' => 'Available property',
                'search.card.default_description' => 'Very spacious and bright apartment.',
                'search.card.per_month' => '/month',
                'search.card.charges_included' => 'Charges incl.',
                'search.card.charges_excluded' => 'Excl. charges',
                'search.card.sale' => 'Sale',
                'search.card.price_on_request' => 'Price on request',
                'search.card.bedrooms.one' => '%count% bedroom',
                'search.card.bedrooms.other' => '%count% bedrooms',
                'search.card.bathrooms.one' => '%count% bathroom',
                'search.card.bathrooms.other' => '%count% bathrooms',
                'search.card.no_address' => 'Address not provided',
                'search.card.favorite.add' => 'Add to favorites',
                'search.card.favorite.remove' => 'Remove from favorites',
                'search.map.empty' => 'No property found in this area of the map.',
                'search.map.list_aria_label' => 'Property list',
                'search.map.map_aria_label' => 'Property map',
                'search.map.expand' => 'Expand map',
                'search.map.close_preview' => 'Close preview',
                'search.map.reference' => 'Reference: %reference%',
                'search.map.type' => 'Type: %type%',
                'search.map.marker.view' => 'View',
                'search.map.marker.view_title' => 'View %title%',
                'search.map.marker.view_default' => 'View property',
                'search.map.error.load' => 'The map cannot be loaded at the moment.',
                'search.map.error.token' => 'The map is unavailable: missing or invalid Mapbox key.',
                'search.map.error.init' => 'Error while initializing the map: %message%',
                'search.map.error.http' => 'HTTP error %status%.',
                'search.map.error.fetch' => 'Error while loading.',
                'search.pagination.aria_label' => 'Pagination',
                'search.pagination.map_aria_label' => 'Map property pagination',
                'search.pagination.previous' => 'Previous',
                'search.pagination.next' => 'Next',
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
                $translation->setPage('search');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
