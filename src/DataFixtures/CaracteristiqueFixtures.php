<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
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
            'nom' => 'Stationnement',
            'icone' => 'icon-circle-parking',
        ],
        [
            'reference' => 'terrasse',
            'nom' => 'Terrasse',
            'icone' => 'icon-fence',
        ],
        [
            'reference' => 'balcon',
            'nom' => 'Balcon',
            'icone' => 'icon-house',
        ],
        [
            'reference' => 'jardin',
            'nom' => 'Jardin',
            'icone' => 'icon-birdhouse',
        ],
        [
            'reference' => 'piscine',
            'nom' => 'Piscine',
            'icone' => 'icon-waves-ladder',
        ],
        [
            'reference' => 'cave-debarras',
            'nom' => 'Cave/débarras',
            'icone' => 'icon-brick-wall',
        ],
        [
            'reference' => 'climatisation',
            'nom' => 'Climatisation',
            'icone' => 'icon-air-vent',
        ],
        [
            'reference' => 'chauffage',
            'nom' => 'Chauffage',
            'icone' => 'icon-brick-wall-fire',
        ],
        [
            'reference' => 'ascenseur',
            'nom' => 'Ascenseur',
            'icone' => 'icon-door-closed',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(Caracteristique::class);

        foreach (self::CARACTERISTIQUES as $data) {
            $caracteristique = $repository->findOneBy([
                'nom' => $data['nom'],
            ]);

            if (!$caracteristique instanceof Caracteristique) {
                $caracteristique = new Caracteristique();
            }

            $caracteristique
                ->setNom($data['nom'])
                ->setIcone($data['icone'])
            ;

            $manager->persist($caracteristique);

            $this->addReference(
                self::CARACTERISTIQUE_REFERENCE_PREFIX.$data['reference'],
                $caracteristique
            );
        }

        $manager->flush();
    }
}
