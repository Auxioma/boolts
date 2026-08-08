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
            ],
        ],
        [
            'reference' => 'terrasse',
            'icone' => 'icon-fence',
            'translations' => [
                'fr' => 'Terrasse',
                'en' => 'Terrace',
            ],
        ],
        [
            'reference' => 'balcon',
            'icone' => 'icon-house',
            'translations' => [
                'fr' => 'Balcon',
                'en' => 'Balcony',
            ],
        ],
        [
            'reference' => 'jardin',
            'icone' => 'icon-birdhouse',
            'translations' => [
                'fr' => 'Jardin',
                'en' => 'Garden',
            ],
        ],
        [
            'reference' => 'piscine',
            'icone' => 'icon-waves-ladder',
            'translations' => [
                'fr' => 'Piscine',
                'en' => 'Swimming pool',
            ],
        ],
        [
            'reference' => 'cave-debarras',
            'icone' => 'icon-brick-wall',
            'translations' => [
                'fr' => 'Cave/débarras',
                'en' => 'Cellar/storage room',
            ],
        ],
        [
            'reference' => 'climatisation',
            'icone' => 'icon-air-vent',
            'translations' => [
                'fr' => 'Climatisation',
                'en' => 'Air conditioning',
            ],
        ],
        [
            'reference' => 'chauffage',
            'icone' => 'icon-brick-wall-fire',
            'translations' => [
                'fr' => 'Chauffage',
                'en' => 'Heating',
            ],
        ],
        [
            'reference' => 'ascenseur',
            'icone' => 'icon-door-closed',
            'translations' => [
                'fr' => 'Ascenseur',
                'en' => 'Elevator',
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CARACTERISTIQUES as $data) {
            $caracteristique = new Caracteristique();
            $caracteristique->setIcone($data['icone']);

            foreach ($data['translations'] as $locale => $nom) {
                $caracteristique->translate($locale)->setNom($nom);
            }

            $caracteristique->mergeNewTranslations();

            $manager->persist($caracteristique);

            $this->addReference(
                self::CARACTERISTIQUE_REFERENCE_PREFIX.$data['reference'],
                $caracteristique
            );
        }

        $manager->flush();
    }
}
