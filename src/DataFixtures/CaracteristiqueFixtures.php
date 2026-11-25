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

namespace App\DataFixtures;

use App\Entity\Caracteristique;
use App\Entity\CaracteristiqueTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CaracteristiqueFixtures extends Fixture
{
    public const CARACTERISTIQUE_REFERENCE_PREFIX = 'caracteristique_';

    private const CARACTERISTIQUES = [
        [
            'reference' => 'stationnement',
            'icone' => 'icon-circle-parking',
            'translations' => [
                'fr' => 'Stationnement',
                'en' => 'Parking',
                'es' => 'Aparcamiento',
                'de' => 'Parkplatz',
                'pt' => 'Estacionamento',
                'it' => 'Parcheggio',
                'nl' => 'Parkeerplaats',
                'hu' => 'Parkoló',
            ],
        ],
        [
            'reference' => 'terrasse',
            'icone' => 'icon-fence',
            'translations' => [
                'fr' => 'Terrasse',
                'en' => 'Terrace',
                'es' => 'Terraza',
                'de' => 'Terrasse',
                'pt' => 'Terraço',
                'it' => 'Terrazza',
                'nl' => 'Terras',
                'hu' => 'Terasz',
            ],
        ],
        [
            'reference' => 'balcon',
            'icone' => 'icon-house',
            'translations' => [
                'fr' => 'Balcon',
                'en' => 'Balcony',
                'es' => 'Balcón',
                'de' => 'Balkon',
                'pt' => 'Varanda',
                'it' => 'Balcone',
                'nl' => 'Balkon',
                'hu' => 'Erkély',
            ],
        ],
        [
            'reference' => 'jardin',
            'icone' => 'icon-birdhouse',
            'translations' => [
                'fr' => 'Jardin',
                'en' => 'Garden',
                'es' => 'Jardín',
                'de' => 'Garten',
                'pt' => 'Jardim',
                'it' => 'Giardino',
                'nl' => 'Tuin',
                'hu' => 'Kert',
            ],
        ],
        [
            'reference' => 'piscine',
            'icone' => 'icon-waves-ladder',
            'translations' => [
                'fr' => 'Piscine',
                'en' => 'Swimming pool',
                'es' => 'Piscina',
                'de' => 'Schwimmbad',
                'pt' => 'Piscina',
                'it' => 'Piscina',
                'nl' => 'Zwembad',
                'hu' => 'Medence',
            ],
        ],
        [
            'reference' => 'cave-debarras',
            'icone' => 'icon-brick-wall',
            'translations' => [
                'fr' => 'Cave/débarras',
                'en' => 'Cellar/storage room',
                'es' => 'Bodega/trastero',
                'de' => 'Keller/Abstellraum',
                'pt' => 'Cave/arrecadação',
                'it' => 'Cantina/ripostiglio',
                'nl' => 'Kelder/berging',
                'hu' => 'Pince/tároló',
            ],
        ],
        [
            'reference' => 'climatisation',
            'icone' => 'icon-air-vent',
            'translations' => [
                'fr' => 'Climatisation',
                'en' => 'Air conditioning',
                'es' => 'Aire acondicionado',
                'de' => 'Klimaanlage',
                'pt' => 'Ar condicionado',
                'it' => 'Aria condizionata',
                'nl' => 'Airconditioning',
                'hu' => 'Légkondicionáló',
            ],
        ],
        [
            'reference' => 'chauffage',
            'icone' => 'icon-brick-wall-fire',
            'translations' => [
                'fr' => 'Chauffage',
                'en' => 'Heating',
                'es' => 'Calefacción',
                'de' => 'Heizung',
                'pt' => 'Aquecimento',
                'it' => 'Riscaldamento',
                'nl' => 'Verwarming',
                'hu' => 'Fűtés',
            ],
        ],
        [
            'reference' => 'ascenseur',
            'icone' => 'icon-door-closed',
            'translations' => [
                'fr' => 'Ascenseur',
                'en' => 'Elevator',
                'es' => 'Ascensor',
                'de' => 'Aufzug',
                'pt' => 'Elevador',
                'it' => 'Ascensore',
                'nl' => 'Lift',
                'hu' => 'Lift',
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CARACTERISTIQUES as $data) {
            $referenceName = $data['translations']['fr'];
            $translation = $manager->getRepository(CaracteristiqueTranslation::class)->findOneBy([
                'locale' => 'fr',
                'nom' => $referenceName,
            ]);
            $caracteristique = $translation?->getTranslatable();
            if (!$caracteristique instanceof Caracteristique) {
                $caracteristique = new Caracteristique();
                $manager->persist($caracteristique);
            }
            $caracteristique->setIcone($data['icone']);

            foreach ($data['translations'] as $locale => $nom) {
                $caracteristique->translate($locale)->setNom($nom);
            }

            $caracteristique->mergeNewTranslations();

            $this->addReference(
                self::CARACTERISTIQUE_REFERENCE_PREFIX.$data['reference'],
                $caracteristique
            );
        }

        $manager->flush();
    }
}
