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

final class HomeTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'home.meta.title' => 'Boolts - L’immobilier réinventé',
                'home.video.unsupported' => 'Votre navigateur ne supporte pas la lecture vidéo.',
                'home.hero.title' => 'L’immobilier, partout.',
                'search.location.placeholder' => 'Rechercher une ville ou un pays',
                'search.tabs.label' => 'Type de recherche',
                'home.search.sell' => 'Vendre',
                'home.search.resume.title' => 'Continuez votre recherche pour “Appartement à louer” à',
                'home.search.resume.submit' => 'Reprendre la recherche',
                'home.search.resume.cancel' => 'Annuler',
                'home.listings.title' => 'Explorez toutes les propriétés',
                'home.listings.rent' => 'En location',
                'home.listings.sale' => 'En vente',
                'home.listings.featured.title' => 'Biens à la',
                'home.listings.featured.highlight' => 'Une',
                'home.listings.rent.popular.title' => 'Logements populaires à louer',
                'home.listings.rent.popular.empty' => 'Aucun logement populaire en location pour le moment.',
                'home.listings.rent.recent.title' => 'Logements récemment ajoutés à louer',
                'home.listings.rent.recent.empty' => 'Aucun logement récemment ajouté en location pour le moment.',
                'home.listings.sale.popular.title' => 'Biens populaires à acheter',
                'home.listings.sale.popular.empty' => 'Aucun bien populaire à acheter pour le moment.',
                'home.listings.sale.recent.title' => 'Biens récemment ajoutés à acheter',
                'home.listings.sale.recent.empty' => 'Aucun bien récemment ajouté à acheter pour le moment.',
                'home.listings.previous' => 'Précédent',
                'home.listings.next' => 'Suivant',
                'home.content.title' => 'Le monde est plein d’adresses. Trouvez la vôtre.',
                'home.content.description' => "Avec Boolts, explorez des opportunités immobilières partout dans le monde, proposées par des agences dont nous vérifions l’existence avant publication.",
                'home.partout.title' => 'Achetez. Louez. Partout.',
                'home.partout.more' => 'Voir plus',
                'home.codes.title' => 'Boolts redéfinit',
                'home.codes.title.highlight' => 'les codes',
                'home.codes.transparency.title' => 'Annonces vérifées',
                'home.codes.fluidity.title' => 'Immobilier sans frontières',
                'home.codes.responsiveness.title' => 'Expérience fluide',
                'home.codes.ecosystem.title' => 'Découverte instantannée',
                'home.codes.transparency.description' => 'Toutes nos annonces proviennent de professionnels de l’immobilier dont nous vérifions l’existence avant publication.',
                'home.codes.fluidity.description' => 'Explorez des opportunités dans votre ville ou à l’autre bout du monde, depuis une seule et même plateforme.',
                'home.codes.responsiveness.description' => 'Naviguez simplement entre achat, location et découverte, avec une expérience pensée pour aller à l’essentiel.',
                'home.codes.ecosystem.description' => 'Parcourez des biens, des villes et des destinations qui ouvrent de nouvelles perspectives de vie et d’investissement.',
                'home.codes.description' => 'Lorem ipsum dolor sit amet, consectetur.',
                'home.app.title' => 'Il y a tout un monde à découvrir sur notre application !',
                'home.app.description' => 'Découvrez des milliers d’annonces immobilières, dans votre ville comme à l’autre bout du monde. Achetez, louez et explorez de nouvelles opportunités où que vous soyez, directement depuis votre smartphone.',
                'home.app.download' => 'Télécharger l’application',
                'home.property.period' => '/mois',
                'home.property.charges_included' => 'C. Comprises',
                'home.property.room' => 'chambre',
                'home.property.bathroom' => 'salle de bains',
                'home.property.title' => '%type% à %transaction%',
                'home.property.fallback.rent' => 'Bien à',
                'home.property.fallback.default' => 'Bien immobilier',
            ],
            'en' => [
                'home.meta.title' => 'Boolts - Real estate reinvented',
                'home.video.unsupported' => 'Your browser does not support video playback.',
                'home.hero.title' => 'Real estate, everywhere.',
                'search.location.placeholder' => 'Search for a city or country',
                'search.tabs.label' => 'Search type',
                'home.search.sell' => 'Sell',
                'home.search.resume.title' => 'Continue your search for “Apartment for rent” in',
                'home.search.resume.submit' => 'Resume search',
                'home.search.resume.cancel' => 'Cancel',
                'home.listings.title' => 'Explore all properties',
                'home.listings.rent' => 'For rent',
                'home.listings.sale' => 'For sale',
                'home.listings.featured.title' => 'Featured',
                'home.listings.featured.highlight' => 'properties',
                'home.listings.rent.popular.title' => 'Popular homes for rent',
                'home.listings.rent.popular.empty' => 'No popular rental homes at the moment.',
                'home.listings.rent.recent.title' => 'Recently added homes for rent',
                'home.listings.rent.recent.empty' => 'No recently added rental homes at the moment.',
                'home.listings.sale.popular.title' => 'Popular properties for sale',
                'home.listings.sale.popular.empty' => 'No popular properties for sale at the moment.',
                'home.listings.sale.recent.title' => 'Recently added properties for sale',
                'home.listings.sale.recent.empty' => 'No recently added properties for sale at the moment.',
                'home.listings.previous' => 'Previous',
                'home.listings.next' => 'Next',
                'home.content.title' => 'The world is full of addresses. Find yours.',
                'home.content.description' => "Your next home may be just two streets away from where you live now. Or on the other side of the world.\nWith Boolts, explore new real estate opportunities, in your city and beyond, from Europe to Asia, from Africa to the Americas.\nAll our listings come from real estate agencies whose existence we verify before publication.",
                'home.partout.title' => 'Buy. Rent. Anywhere.',
                'home.partout.more' => 'See more',
                'home.codes.title' => 'Boolts redefines',
                'home.codes.title.highlight' => 'the codes',
                'home.codes.transparency.title' => 'Verified listings',
                'home.codes.fluidity.title' => 'Borderless real estate',
                'home.codes.responsiveness.title' => 'Seamless experience',
                'home.codes.ecosystem.title' => 'Instant discovery',
                'home.codes.transparency.description' => 'All our listings come from real estate professionals whose existence we verify before publication.',
                'home.codes.fluidity.description' => 'Explore opportunities in your city or on the other side of the world, all from a single platform.',
                'home.codes.responsiveness.description' => 'Move easily between buying, renting and discovering, with an experience designed to get straight to the point.',
                'home.codes.ecosystem.description' => 'Browse properties, cities and destinations that open up new perspectives for living and investing.',
                'home.codes.description' => 'Lorem ipsum dolor sit amet, consectetur.',
                'home.app.title' => 'There’s a whole world to discover on our app!',
                'home.app.description' => 'Explore thousands of property listings, whether in your own city or on the other side of the world. Buy, rent, and discover new opportunities wherever you are, right from your smartphone.',
                'home.app.download' => 'Download the app',
                'home.property.period' => '/month',
                'home.property.charges_included' => 'Charges included',
                'home.property.room' => 'room',
                'home.property.bathroom' => 'bathroom',
                'home.property.title' => '%type% for %transaction%',
                'home.property.fallback.rent' => 'Property for',
                'home.property.fallback.default' => 'Property',
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
                $translation->setPage('home');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
