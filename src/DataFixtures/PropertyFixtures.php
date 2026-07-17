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
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PropertyFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROPERTY_REFERENCE_PREFIX = 'property_';

    /**
     * Conservé pour les fixtures dépendantes.
     * Le vrai volume est dynamique :
     * 100 villes x 6 à 12 biens = environ 600 à 1200 annonces réalistes.
     */
    public const PROPERTY_COUNT = 2000;

    private const MIN_PROPERTIES_PER_LOCATION = 6;
    private const MAX_PROPERTIES_PER_LOCATION = 12;

    /**
     * Rayon GPS autour du centre de la ville.
     * 2.5 km permet de rester cohérent avec la commune/le quartier.
     */
    private const GPS_RANDOM_RADIUS_KM = 2.5;
    private const EARTH_RADIUS_KM = 6371.0088;

    /**
     * Profils réalistes, proches de ce que l’on voit sur SeLoger / PAP :
     * - beaucoup d’appartements et maisons,
     * - quelques biens pros,
     * - terrains, fermes et parkings plus rares.
     */
    private const PROPERTY_PROFILES = [
        ['typeBien' => 'appartement', 'typeTransaction' => 'vente', 'weight' => 22],
        ['typeBien' => 'appartement', 'typeTransaction' => 'location', 'weight' => 22],
        ['typeBien' => 'maison', 'typeTransaction' => 'vente', 'weight' => 18],
        ['typeBien' => 'maison', 'typeTransaction' => 'location', 'weight' => 6],
        ['typeBien' => 'villa', 'typeTransaction' => 'vente', 'weight' => 5],
        ['typeBien' => 'terrain', 'typeTransaction' => 'vente', 'weight' => 5],
        ['typeBien' => 'ferme', 'typeTransaction' => 'vente', 'weight' => 3],
        ['typeBien' => 'bureaux', 'typeTransaction' => 'location', 'weight' => 6],
        ['typeBien' => 'bureaux', 'typeTransaction' => 'vente', 'weight' => 3],
        ['typeBien' => 'local-commercial', 'typeTransaction' => 'location', 'weight' => 4],
        ['typeBien' => 'local-commercial', 'typeTransaction' => 'vente', 'weight' => 2],
        ['typeBien' => 'fond-de-commerce', 'typeTransaction' => 'vente', 'weight' => 2],
        ['typeBien' => 'parking-garage-box', 'typeTransaction' => 'location', 'weight' => 2],
    ];

    /**
     * Facteurs de marché approximatifs pour rendre les prix plus crédibles selon la ville.
     */
    private const MARKET_FACTORS = [
        'Paris' => 2.65,
        'Lyon' => 1.55,
        'Nice' => 1.65,
        'Bordeaux' => 1.45,
        'Annecy' => 1.75,
        'Biarritz' => 1.85,
        'Aix-en-Provence' => 1.55,
        'La Rochelle' => 1.45,
        'Nantes' => 1.25,
        'Montpellier' => 1.25,
        'Strasbourg' => 1.20,
        'Lille' => 1.18,
        'Rennes' => 1.15,
        'Bayonne' => 1.35,
        'Le Havre' => 0.82,
        'Saint-Valery-en-Caux' => 0.90,
        'Marrakech' => 1.10,

        'Toronto' => 1.95,
        'Vancouver' => 2.15,
        'Montréal' => 1.45,
        'Calgary' => 1.30,
        'Ottawa' => 1.32,
        'Victoria' => 1.70,
        'Mississauga' => 1.55,
        'Oakville' => 1.75,
        'Richmond' => 1.65,
        'Burnaby' => 1.55,
        'Surrey' => 1.35,
        'Banff' => 1.85,
    ];

    private const FRANCE_LOCATIONS = [
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '75015',
            'ville' => 'Paris',
            'region' => 'Île-de-France',
            'department' => 'Paris',
            'neighborhood' => 'Vaugirard',
            'latitude' => '48.8414',
            'longitude' => '2.3003',
            'mapboxId' => 'fixture-fr-paris-75015',
            'streets' => ['Rue de Vaugirard', 'Rue Lecourbe', 'Boulevard de Grenelle'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '13006',
            'ville' => 'Marseille',
            'region' => 'Provence-Alpes-Côte d’Azur',
            'department' => 'Bouches-du-Rhône',
            'neighborhood' => 'Castellane',
            'latitude' => '43.2854',
            'longitude' => '5.3820',
            'mapboxId' => 'fixture-fr-marseille-13006',
            'streets' => ['Rue Paradis', 'Avenue du Prado', 'Rue Breteuil'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '69002',
            'ville' => 'Lyon',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Rhône',
            'neighborhood' => 'Presqu’île',
            'latitude' => '45.7640',
            'longitude' => '4.8357',
            'mapboxId' => 'fixture-fr-lyon-69002',
            'streets' => ['Rue de la République', 'Rue Victor Hugo', 'Quai Saint-Antoine'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '31000',
            'ville' => 'Toulouse',
            'region' => 'Occitanie',
            'department' => 'Haute-Garonne',
            'neighborhood' => 'Capitole',
            'latitude' => '43.6047',
            'longitude' => '1.4442',
            'mapboxId' => 'fixture-fr-toulouse-31000',
            'streets' => ['Rue d’Alsace-Lorraine', 'Rue de Metz', 'Boulevard de Strasbourg'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '06000',
            'ville' => 'Nice',
            'region' => 'Provence-Alpes-Côte d’Azur',
            'department' => 'Alpes-Maritimes',
            'neighborhood' => 'Jean-Médecin',
            'latitude' => '43.7102',
            'longitude' => '7.2620',
            'mapboxId' => 'fixture-fr-nice-06000',
            'streets' => ['Avenue Jean Médecin', 'Rue Masséna', 'Boulevard Victor Hugo'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '44000',
            'ville' => 'Nantes',
            'region' => 'Pays de la Loire',
            'department' => 'Loire-Atlantique',
            'neighborhood' => 'Centre-ville',
            'latitude' => '47.2184',
            'longitude' => '-1.5536',
            'mapboxId' => 'fixture-fr-nantes-44000',
            'streets' => ['Rue Crébillon', 'Cours des 50 Otages', 'Rue de Strasbourg'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '34000',
            'ville' => 'Montpellier',
            'region' => 'Occitanie',
            'department' => 'Hérault',
            'neighborhood' => 'Écusson',
            'latitude' => '43.6108',
            'longitude' => '3.8767',
            'mapboxId' => 'fixture-fr-montpellier-34000',
            'streets' => ['Rue de la Loge', 'Boulevard du Jeu de Paume', 'Rue Foch'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '67000',
            'ville' => 'Strasbourg',
            'region' => 'Grand Est',
            'department' => 'Bas-Rhin',
            'neighborhood' => 'Grande Île',
            'latitude' => '48.5734',
            'longitude' => '7.7521',
            'mapboxId' => 'fixture-fr-strasbourg-67000',
            'streets' => ['Grand Rue', 'Rue des Grandes Arcades', 'Quai des Bateliers'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '33000',
            'ville' => 'Bordeaux',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Gironde',
            'neighborhood' => 'Chartrons',
            'latitude' => '44.8378',
            'longitude' => '-0.5792',
            'mapboxId' => 'fixture-fr-bordeaux-33000',
            'streets' => ['Cours de l’Intendance', 'Rue Sainte-Catherine', 'Quai des Chartrons'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '59000',
            'ville' => 'Lille',
            'region' => 'Hauts-de-France',
            'department' => 'Nord',
            'neighborhood' => 'Vieux-Lille',
            'latitude' => '50.6292',
            'longitude' => '3.0573',
            'mapboxId' => 'fixture-fr-lille-59000',
            'streets' => ['Rue Nationale', 'Rue de la Monnaie', 'Boulevard de la Liberté'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '35000',
            'ville' => 'Rennes',
            'region' => 'Bretagne',
            'department' => 'Ille-et-Vilaine',
            'neighborhood' => 'Centre',
            'latitude' => '48.1173',
            'longitude' => '-1.6778',
            'mapboxId' => 'fixture-fr-rennes-35000',
            'streets' => ['Rue de la Monnaie', 'Rue Le Bastard', 'Quai Duguay-Trouin'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '51100',
            'ville' => 'Reims',
            'region' => 'Grand Est',
            'department' => 'Marne',
            'neighborhood' => 'Cathédrale',
            'latitude' => '49.2583',
            'longitude' => '4.0317',
            'mapboxId' => 'fixture-fr-reims-51100',
            'streets' => ['Rue de Vesle', 'Cours Jean-Baptiste Langlet', 'Boulevard Lundy'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '83000',
            'ville' => 'Toulon',
            'region' => 'Provence-Alpes-Côte d’Azur',
            'department' => 'Var',
            'neighborhood' => 'Centre',
            'latitude' => '43.1242',
            'longitude' => '5.9280',
            'mapboxId' => 'fixture-fr-toulon-83000',
            'streets' => ['Avenue de la République', 'Rue d’Alger', 'Boulevard de Strasbourg'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '38000',
            'ville' => 'Grenoble',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Isère',
            'neighborhood' => 'Hyper-centre',
            'latitude' => '45.1885',
            'longitude' => '5.7245',
            'mapboxId' => 'fixture-fr-grenoble-38000',
            'streets' => ['Cours Jean Jaurès', 'Rue Lesdiguières', 'Boulevard Gambetta'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '21000',
            'ville' => 'Dijon',
            'region' => 'Bourgogne-Franche-Comté',
            'department' => 'Côte-d’Or',
            'neighborhood' => 'Centre historique',
            'latitude' => '47.3220',
            'longitude' => '5.0415',
            'mapboxId' => 'fixture-fr-dijon-21000',
            'streets' => ['Rue de la Liberté', 'Rue Monge', 'Place Darcy'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '49000',
            'ville' => 'Angers',
            'region' => 'Pays de la Loire',
            'department' => 'Maine-et-Loire',
            'neighborhood' => 'Centre-ville',
            'latitude' => '47.4784',
            'longitude' => '-0.5632',
            'mapboxId' => 'fixture-fr-angers-49000',
            'streets' => ['Rue Saint-Aubin', 'Boulevard Foch', 'Rue Plantagenêt'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '30000',
            'ville' => 'Nîmes',
            'region' => 'Occitanie',
            'department' => 'Gard',
            'neighborhood' => 'Écusson',
            'latitude' => '43.8367',
            'longitude' => '4.3601',
            'mapboxId' => 'fixture-fr-nimes-30000',
            'streets' => ['Boulevard Victor Hugo', 'Rue de la Madeleine', 'Rue Général Perrier'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '69100',
            'ville' => 'Villeurbanne',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Rhône',
            'neighborhood' => 'Gratte-Ciel',
            'latitude' => '45.7719',
            'longitude' => '4.8902',
            'mapboxId' => 'fixture-fr-villeurbanne-69100',
            'streets' => ['Cours Émile Zola', 'Avenue Henri Barbusse', 'Rue Anatole France'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '63000',
            'ville' => 'Clermont-Ferrand',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Puy-de-Dôme',
            'neighborhood' => 'Jaude',
            'latitude' => '45.7772',
            'longitude' => '3.0870',
            'mapboxId' => 'fixture-fr-clermont-ferrand-63000',
            'streets' => ['Avenue des États-Unis', 'Rue Blatin', 'Boulevard Desaix'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '72000',
            'ville' => 'Le Mans',
            'region' => 'Pays de la Loire',
            'department' => 'Sarthe',
            'neighborhood' => 'Centre',
            'latitude' => '48.0061',
            'longitude' => '0.1996',
            'mapboxId' => 'fixture-fr-le-mans-72000',
            'streets' => ['Rue Nationale', 'Avenue du Général Leclerc', 'Rue des Minimes'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '13100',
            'ville' => 'Aix-en-Provence',
            'region' => 'Provence-Alpes-Côte d’Azur',
            'department' => 'Bouches-du-Rhône',
            'neighborhood' => 'Mazarin',
            'latitude' => '43.5297',
            'longitude' => '5.4474',
            'mapboxId' => 'fixture-fr-aix-en-provence-13100',
            'streets' => ['Cours Mirabeau', 'Rue d’Italie', 'Rue Espariat'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '29200',
            'ville' => 'Brest',
            'region' => 'Bretagne',
            'department' => 'Finistère',
            'neighborhood' => 'Recouvrance',
            'latitude' => '48.3904',
            'longitude' => '-4.4861',
            'mapboxId' => 'fixture-fr-brest-29200',
            'streets' => ['Rue de Siam', 'Rue Jean Jaurès', 'Avenue Georges Clemenceau'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '37000',
            'ville' => 'Tours',
            'region' => 'Centre-Val de Loire',
            'department' => 'Indre-et-Loire',
            'neighborhood' => 'Vieux Tours',
            'latitude' => '47.3941',
            'longitude' => '0.6848',
            'mapboxId' => 'fixture-fr-tours-37000',
            'streets' => ['Rue Nationale', 'Boulevard Béranger', 'Rue Colbert'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '80000',
            'ville' => 'Amiens',
            'region' => 'Hauts-de-France',
            'department' => 'Somme',
            'neighborhood' => 'Saint-Leu',
            'latitude' => '49.8941',
            'longitude' => '2.2958',
            'mapboxId' => 'fixture-fr-amiens-80000',
            'streets' => ['Rue des Trois Cailloux', 'Boulevard de Belfort', 'Rue Saint-Leu'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '87000',
            'ville' => 'Limoges',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Haute-Vienne',
            'neighborhood' => 'Centre',
            'latitude' => '45.8336',
            'longitude' => '1.2611',
            'mapboxId' => 'fixture-fr-limoges-87000',
            'streets' => ['Rue Jean Jaurès', 'Boulevard Louis Blanc', 'Rue du Clocher'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '74000',
            'ville' => 'Annecy',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Haute-Savoie',
            'neighborhood' => 'Vieille Ville',
            'latitude' => '45.8992',
            'longitude' => '6.1294',
            'mapboxId' => 'fixture-fr-annecy-74000',
            'streets' => ['Rue Carnot', 'Rue Royale', 'Quai de l’Évêché'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '66000',
            'ville' => 'Perpignan',
            'region' => 'Occitanie',
            'department' => 'Pyrénées-Orientales',
            'neighborhood' => 'Centre historique',
            'latitude' => '42.6887',
            'longitude' => '2.8948',
            'mapboxId' => 'fixture-fr-perpignan-66000',
            'streets' => ['Quai Vauban', 'Rue de l’Ange', 'Avenue du Général Leclerc'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '57000',
            'ville' => 'Metz',
            'region' => 'Grand Est',
            'department' => 'Moselle',
            'neighborhood' => 'Centre-ville',
            'latitude' => '49.1193',
            'longitude' => '6.1757',
            'mapboxId' => 'fixture-fr-metz-57000',
            'streets' => ['Rue Serpenoise', 'Rue des Clercs', 'Avenue Foch'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '25000',
            'ville' => 'Besançon',
            'region' => 'Bourgogne-Franche-Comté',
            'department' => 'Doubs',
            'neighborhood' => 'Boucle',
            'latitude' => '47.2378',
            'longitude' => '6.0241',
            'mapboxId' => 'fixture-fr-besancon-25000',
            'streets' => ['Grande Rue', 'Rue des Granges', 'Quai Vauban'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '45000',
            'ville' => 'Orléans',
            'region' => 'Centre-Val de Loire',
            'department' => 'Loiret',
            'neighborhood' => 'Centre-ville',
            'latitude' => '47.9029',
            'longitude' => '1.9093',
            'mapboxId' => 'fixture-fr-orleans-45000',
            'streets' => ['Rue Jeanne d’Arc', 'Rue Royale', 'Boulevard Alexandre Martin'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '68100',
            'ville' => 'Mulhouse',
            'region' => 'Grand Est',
            'department' => 'Haut-Rhin',
            'neighborhood' => 'Centre historique',
            'latitude' => '47.7508',
            'longitude' => '7.3359',
            'mapboxId' => 'fixture-fr-mulhouse-68100',
            'streets' => ['Rue du Sauvage', 'Avenue de Colmar', 'Rue de la Sinne'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '76000',
            'ville' => 'Rouen',
            'region' => 'Normandie',
            'department' => 'Seine-Maritime',
            'neighborhood' => 'Vieux-Marché',
            'latitude' => '49.4432',
            'longitude' => '1.0993',
            'mapboxId' => 'fixture-fr-rouen-76000',
            'streets' => ['Rue du Gros-Horloge', 'Rue Jeanne d’Arc', 'Quai de Paris'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '14000',
            'ville' => 'Caen',
            'region' => 'Normandie',
            'department' => 'Calvados',
            'neighborhood' => 'Centre-ville',
            'latitude' => '49.1829',
            'longitude' => '-0.3707',
            'mapboxId' => 'fixture-fr-caen-14000',
            'streets' => ['Rue Saint-Pierre', 'Avenue du Six Juin', 'Boulevard Maréchal Leclerc'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '54000',
            'ville' => 'Nancy',
            'region' => 'Grand Est',
            'department' => 'Meurthe-et-Moselle',
            'neighborhood' => 'Centre',
            'latitude' => '48.6921',
            'longitude' => '6.1844',
            'mapboxId' => 'fixture-fr-nancy-54000',
            'streets' => ['Rue Saint-Jean', 'Rue Stanislas', 'Boulevard Albert 1er'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '84000',
            'ville' => 'Avignon',
            'region' => 'Provence-Alpes-Côte d’Azur',
            'department' => 'Vaucluse',
            'neighborhood' => 'Intra-muros',
            'latitude' => '43.9493',
            'longitude' => '4.8055',
            'mapboxId' => 'fixture-fr-avignon-84000',
            'streets' => ['Rue de la République', 'Rue Joseph Vernet', 'Cours Jean Jaurès'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '86000',
            'ville' => 'Poitiers',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Vienne',
            'neighborhood' => 'Centre-ville',
            'latitude' => '46.5802',
            'longitude' => '0.3404',
            'mapboxId' => 'fixture-fr-poitiers-86000',
            'streets' => ['Rue de la Marne', 'Grand Rue', 'Boulevard de Verdun'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '64000',
            'ville' => 'Pau',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Pyrénées-Atlantiques',
            'neighborhood' => 'Centre',
            'latitude' => '43.2951',
            'longitude' => '-0.3708',
            'mapboxId' => 'fixture-fr-pau-64000',
            'streets' => ['Boulevard des Pyrénées', 'Rue Maréchal Joffre', 'Rue Serviez'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '17000',
            'ville' => 'La Rochelle',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Charente-Maritime',
            'neighborhood' => 'Vieux-Port',
            'latitude' => '46.1603',
            'longitude' => '-1.1511',
            'mapboxId' => 'fixture-fr-la-rochelle-17000',
            'streets' => ['Rue Saint-Yon', 'Rue du Palais', 'Quai Duperré'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '56100',
            'ville' => 'Lorient',
            'region' => 'Bretagne',
            'department' => 'Morbihan',
            'neighborhood' => 'Centre',
            'latitude' => '47.7483',
            'longitude' => '-3.3702',
            'mapboxId' => 'fixture-fr-lorient-56100',
            'streets' => ['Rue de Liège', 'Cours de Chazelles', 'Rue du Port'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '56000',
            'ville' => 'Vannes',
            'region' => 'Bretagne',
            'department' => 'Morbihan',
            'neighborhood' => 'Intra-muros',
            'latitude' => '47.6582',
            'longitude' => '-2.7608',
            'mapboxId' => 'fixture-fr-vannes-56000',
            'streets' => ['Rue Saint-Vincent', 'Rue Thiers', 'Place des Lices'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '64100',
            'ville' => 'Bayonne',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Pyrénées-Atlantiques',
            'neighborhood' => 'Grand Bayonne',
            'latitude' => '43.4929',
            'longitude' => '-1.4748',
            'mapboxId' => 'fixture-fr-bayonne-64100',
            'streets' => ['Rue d’Espagne', 'Rue Victor Hugo', 'Quai Amiral Dubourdieu'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '64200',
            'ville' => 'Biarritz',
            'region' => 'Nouvelle-Aquitaine',
            'department' => 'Pyrénées-Atlantiques',
            'neighborhood' => 'Centre',
            'latitude' => '43.4832',
            'longitude' => '-1.5586',
            'mapboxId' => 'fixture-fr-biarritz-64200',
            'streets' => ['Avenue Édouard VII', 'Rue Gambetta', 'Avenue de Verdun'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '73000',
            'ville' => 'Chambéry',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Savoie',
            'neighborhood' => 'Centre historique',
            'latitude' => '45.5646',
            'longitude' => '5.9178',
            'mapboxId' => 'fixture-fr-chambery-73000',
            'streets' => ['Rue de Boigne', 'Rue Croix d’Or', 'Avenue des Ducs de Savoie'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '26000',
            'ville' => 'Valence',
            'region' => 'Auvergne-Rhône-Alpes',
            'department' => 'Drôme',
            'neighborhood' => 'Centre-ville',
            'latitude' => '44.9334',
            'longitude' => '4.8924',
            'mapboxId' => 'fixture-fr-valence-26000',
            'streets' => ['Boulevard Bancel', 'Rue Madier de Montjau', 'Avenue Victor Hugo'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '34500',
            'ville' => 'Béziers',
            'region' => 'Occitanie',
            'department' => 'Hérault',
            'neighborhood' => 'Centre',
            'latitude' => '43.3442',
            'longitude' => '3.2158',
            'mapboxId' => 'fixture-fr-beziers-34500',
            'streets' => ['Allées Paul Riquet', 'Rue de la République', 'Avenue Saint-Saëns'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '11100',
            'ville' => 'Narbonne',
            'region' => 'Occitanie',
            'department' => 'Aude',
            'neighborhood' => 'Centre',
            'latitude' => '43.1843',
            'longitude' => '3.0031',
            'mapboxId' => 'fixture-fr-narbonne-11100',
            'streets' => ['Cours de la République', 'Rue Droite', 'Boulevard Gambetta'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '11000',
            'ville' => 'Carcassonne',
            'region' => 'Occitanie',
            'department' => 'Aude',
            'neighborhood' => 'Bastide',
            'latitude' => '43.2130',
            'longitude' => '2.3491',
            'mapboxId' => 'fixture-fr-carcassonne-11000',
            'streets' => ['Rue de Verdun', 'Boulevard Omer Sarraut', 'Rue Courtejaire'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '81000',
            'ville' => 'Albi',
            'region' => 'Occitanie',
            'department' => 'Tarn',
            'neighborhood' => 'Centre historique',
            'latitude' => '43.9298',
            'longitude' => '2.1480',
            'mapboxId' => 'fixture-fr-albi-81000',
            'streets' => ['Rue Mariès', 'Lices Georges Pompidou', 'Rue Croix Verte'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '82000',
            'ville' => 'Montauban',
            'region' => 'Occitanie',
            'department' => 'Tarn-et-Garonne',
            'neighborhood' => 'Centre',
            'latitude' => '44.0221',
            'longitude' => '1.3528',
            'mapboxId' => 'fixture-fr-montauban-82000',
            'streets' => ['Rue de la République', 'Faubourg Lacapelle', 'Boulevard Gustave Garrisson'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '76600',
            'ville' => 'Le Havre',
            'region' => 'Normandie',
            'department' => 'Seine-Maritime',
            'neighborhood' => 'Centre reconstruit',
            'latitude' => '49.4944',
            'longitude' => '0.1079',
            'mapboxId' => 'fixture-fr-le-havre-76600',
            'streets' => ['Avenue Foch', 'Rue de Paris', 'Boulevard François 1er'],
        ],
        [
            'countryCode' => 'FR',
            'country' => 'France',
            'codePostal' => '76460',
            'ville' => 'Saint-Valery-en-Caux',
            'region' => 'Normandie',
            'department' => 'Seine-Maritime',
            'neighborhood' => 'Centre-ville',
            'latitude' => '49.8728',
            'longitude' => '0.7098',
            'mapboxId' => 'fixture-fr-saint-valery-en-caux-76460',
            'streets' => ['Rue des Remparts', 'Rue du Havre', 'Quai d’Amont'],
        ],
    ];

    private const CANADA_LOCATIONS = [
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'M5V 2T6',
            'ville' => 'Toronto',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'King West',
            'latitude' => '43.6532',
            'longitude' => '-79.3832',
            'mapboxId' => 'fixture-ca-toronto-m5v-2t6',
            'streets' => ['King Street West', 'Queen Street West', 'Front Street West'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'H2Y 1C6',
            'ville' => 'Montréal',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Vieux-Montréal',
            'latitude' => '45.5017',
            'longitude' => '-73.5673',
            'mapboxId' => 'fixture-ca-montreal-h2y-1c6',
            'streets' => ['Rue Saint-Paul', 'Rue Notre-Dame', 'Boulevard Saint-Laurent'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V6B 1A1',
            'ville' => 'Vancouver',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'Yaletown',
            'latitude' => '49.2827',
            'longitude' => '-123.1207',
            'mapboxId' => 'fixture-ca-vancouver-v6b-1a1',
            'streets' => ['Robson Street', 'Pacific Boulevard', 'West Georgia Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'T2P 1J9',
            'ville' => 'Calgary',
            'region' => 'Alberta',
            'department' => 'Alberta',
            'neighborhood' => 'Downtown',
            'latitude' => '51.0447',
            'longitude' => '-114.0719',
            'mapboxId' => 'fixture-ca-calgary-t2p-1j9',
            'streets' => ['Stephen Avenue', '17 Avenue SW', 'Centre Street S'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'T5J 0N3',
            'ville' => 'Edmonton',
            'region' => 'Alberta',
            'department' => 'Alberta',
            'neighborhood' => 'Downtown',
            'latitude' => '53.5461',
            'longitude' => '-113.4938',
            'mapboxId' => 'fixture-ca-edmonton-t5j-0n3',
            'streets' => ['Jasper Avenue', '104 Street NW', 'Whyte Avenue'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'K1P 5J6',
            'ville' => 'Ottawa',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Centretown',
            'latitude' => '45.4215',
            'longitude' => '-75.6972',
            'mapboxId' => 'fixture-ca-ottawa-k1p-5j6',
            'streets' => ['Bank Street', 'Elgin Street', 'Queen Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'R3C 0A5',
            'ville' => 'Winnipeg',
            'region' => 'Manitoba',
            'department' => 'Manitoba',
            'neighborhood' => 'Exchange District',
            'latitude' => '49.8951',
            'longitude' => '-97.1384',
            'mapboxId' => 'fixture-ca-winnipeg-r3c-0a5',
            'streets' => ['Portage Avenue', 'Main Street', 'Broadway'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'G1R 4P5',
            'ville' => 'Québec',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Vieux-Québec',
            'latitude' => '46.8139',
            'longitude' => '-71.2080',
            'mapboxId' => 'fixture-ca-quebec-g1r-4p5',
            'streets' => ['Rue Saint-Jean', 'Grande Allée', 'Rue Saint-Louis'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L8P 1A1',
            'ville' => 'Hamilton',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Durand',
            'latitude' => '43.2557',
            'longitude' => '-79.8711',
            'mapboxId' => 'fixture-ca-hamilton-l8p-1a1',
            'streets' => ['James Street North', 'King Street East', 'Main Street West'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'N2G 1A1',
            'ville' => 'Kitchener',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Downtown',
            'latitude' => '43.4516',
            'longitude' => '-80.4925',
            'mapboxId' => 'fixture-ca-kitchener-n2g-1a1',
            'streets' => ['King Street West', 'Victoria Street North', 'Duke Street West'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'N6A 1A1',
            'ville' => 'London',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Downtown',
            'latitude' => '42.9849',
            'longitude' => '-81.2453',
            'mapboxId' => 'fixture-ca-london-n6a-1a1',
            'streets' => ['Richmond Street', 'Dundas Street', 'Oxford Street East'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V8W 1A1',
            'ville' => 'Victoria',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'James Bay',
            'latitude' => '48.4284',
            'longitude' => '-123.3656',
            'mapboxId' => 'fixture-ca-victoria-v8w-1a1',
            'streets' => ['Government Street', 'Douglas Street', 'Belleville Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'B3J 1A1',
            'ville' => 'Halifax',
            'region' => 'Nouvelle-Écosse',
            'department' => 'Nova Scotia',
            'neighborhood' => 'Downtown Halifax',
            'latitude' => '44.6488',
            'longitude' => '-63.5752',
            'mapboxId' => 'fixture-ca-halifax-b3j-1a1',
            'streets' => ['Barrington Street', 'Spring Garden Road', 'Gottingen Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L1H 1A1',
            'ville' => 'Oshawa',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Central Oshawa',
            'latitude' => '43.8971',
            'longitude' => '-78.8658',
            'mapboxId' => 'fixture-ca-oshawa-l1h-1a1',
            'streets' => ['King Street West', 'Simcoe Street North', 'Bond Street East'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'N9A 1A1',
            'ville' => 'Windsor',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Downtown',
            'latitude' => '42.3149',
            'longitude' => '-83.0364',
            'mapboxId' => 'fixture-ca-windsor-n9a-1a1',
            'streets' => ['Ouellette Avenue', 'Riverside Drive', 'Wyandotte Street East'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'S7K 1A1',
            'ville' => 'Saskatoon',
            'region' => 'Saskatchewan',
            'department' => 'Saskatchewan',
            'neighborhood' => 'Nutana',
            'latitude' => '52.1332',
            'longitude' => '-106.6700',
            'mapboxId' => 'fixture-ca-saskatoon-s7k-1a1',
            'streets' => ['Broadway Avenue', 'Idylwyld Drive', '20th Street West'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'S4P 1A1',
            'ville' => 'Regina',
            'region' => 'Saskatchewan',
            'department' => 'Saskatchewan',
            'neighborhood' => 'Cathedral',
            'latitude' => '50.4452',
            'longitude' => '-104.6189',
            'mapboxId' => 'fixture-ca-regina-s4p-1a1',
            'streets' => ['Albert Street', 'Victoria Avenue', 'Broad Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'A1C 1A1',
            'ville' => 'St. John’s',
            'region' => 'Terre-Neuve-et-Labrador',
            'department' => 'Newfoundland and Labrador',
            'neighborhood' => 'Downtown',
            'latitude' => '47.5615',
            'longitude' => '-52.7126',
            'mapboxId' => 'fixture-ca-st-john-s-a1c-1a1',
            'streets' => ['Water Street', 'Duckworth Street', 'Harbour Drive'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V1Y 1A1',
            'ville' => 'Kelowna',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'Downtown',
            'latitude' => '49.8880',
            'longitude' => '-119.4960',
            'mapboxId' => 'fixture-ca-kelowna-v1y-1a1',
            'streets' => ['Bernard Avenue', 'Water Street', 'Pandosy Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L4M 1A1',
            'ville' => 'Barrie',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'City Centre',
            'latitude' => '44.3894',
            'longitude' => '-79.6903',
            'mapboxId' => 'fixture-ca-barrie-l4m-1a1',
            'streets' => ['Dunlop Street East', 'Bayfield Street', 'Collier Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'J1H 1A1',
            'ville' => 'Sherbrooke',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Centre-ville',
            'latitude' => '45.4042',
            'longitude' => '-71.8929',
            'mapboxId' => 'fixture-ca-sherbrooke-j1h-1a1',
            'streets' => ['Rue King Ouest', 'Rue Wellington Nord', 'Boulevard Jacques-Cartier'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'G9A 1A1',
            'ville' => 'Trois-Rivières',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Centre-ville',
            'latitude' => '46.3432',
            'longitude' => '-72.5430',
            'mapboxId' => 'fixture-ca-trois-rivieres-g9a-1a1',
            'streets' => ['Rue des Forges', 'Boulevard du Saint-Maurice', 'Rue Notre-Dame Centre'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'J8X 1A1',
            'ville' => 'Gatineau',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Hull',
            'latitude' => '45.4765',
            'longitude' => '-75.7013',
            'mapboxId' => 'fixture-ca-gatineau-j8x-1a1',
            'streets' => ['Boulevard Maisonneuve', 'Rue Eddy', 'Boulevard Saint-Joseph'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'H7N 1A1',
            'ville' => 'Laval',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Centropolis',
            'latitude' => '45.6066',
            'longitude' => '-73.7124',
            'mapboxId' => 'fixture-ca-laval-h7n-1a1',
            'streets' => ['Boulevard Saint-Martin', 'Boulevard Daniel-Johnson', 'Boulevard de l’Avenir'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'J4K 1A1',
            'ville' => 'Longueuil',
            'region' => 'Québec',
            'department' => 'Québec',
            'neighborhood' => 'Vieux-Longueuil',
            'latitude' => '45.5312',
            'longitude' => '-73.5181',
            'mapboxId' => 'fixture-ca-longueuil-j4k-1a1',
            'streets' => ['Rue Saint-Charles Ouest', 'Chemin de Chambly', 'Boulevard Roland-Therrien'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V3T 1A1',
            'ville' => 'Surrey',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'City Centre',
            'latitude' => '49.1913',
            'longitude' => '-122.8490',
            'mapboxId' => 'fixture-ca-surrey-v3t-1a1',
            'streets' => ['King George Boulevard', '104 Avenue', 'University Drive'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V5H 1A1',
            'ville' => 'Burnaby',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'Metrotown',
            'latitude' => '49.2488',
            'longitude' => '-122.9805',
            'mapboxId' => 'fixture-ca-burnaby-v5h-1a1',
            'streets' => ['Kingsway', 'Willingdon Avenue', 'Nelson Avenue'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V6Y 1A1',
            'ville' => 'Richmond',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'Brighouse',
            'latitude' => '49.1666',
            'longitude' => '-123.1336',
            'mapboxId' => 'fixture-ca-richmond-v6y-1a1',
            'streets' => ['No. 3 Road', 'Granville Avenue', 'Westminster Highway'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L3R 1A1',
            'ville' => 'Markham',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Unionville',
            'latitude' => '43.8561',
            'longitude' => '-79.3370',
            'mapboxId' => 'fixture-ca-markham-l3r-1a1',
            'streets' => ['Main Street Unionville', 'Highway 7', 'Kennedy Road'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L5B 1A1',
            'ville' => 'Mississauga',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'City Centre',
            'latitude' => '43.5890',
            'longitude' => '-79.6441',
            'mapboxId' => 'fixture-ca-mississauga-l5b-1a1',
            'streets' => ['Hurontario Street', 'Burnhamthorpe Road', 'Duke of York Boulevard'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L6Y 1A1',
            'ville' => 'Brampton',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Downtown',
            'latitude' => '43.7315',
            'longitude' => '-79.7624',
            'mapboxId' => 'fixture-ca-brampton-l6y-1a1',
            'streets' => ['Main Street North', 'Queen Street West', 'Kennedy Road South'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L6J 1A1',
            'ville' => 'Oakville',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Old Oakville',
            'latitude' => '43.4675',
            'longitude' => '-79.6877',
            'mapboxId' => 'fixture-ca-oakville-l6j-1a1',
            'streets' => ['Lakeshore Road East', 'Trafalgar Road', 'Navy Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L7R 1A1',
            'ville' => 'Burlington',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Downtown Waterfront',
            'latitude' => '43.3255',
            'longitude' => '-79.7990',
            'mapboxId' => 'fixture-ca-burlington-l7r-1a1',
            'streets' => ['Brant Street', 'Lakeshore Road', 'New Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'K7L 1A1',
            'ville' => 'Kingston',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Sydenham',
            'latitude' => '44.2312',
            'longitude' => '-76.4860',
            'mapboxId' => 'fixture-ca-kingston-k7l-1a1',
            'streets' => ['Princess Street', 'King Street East', 'Ontario Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'N1H 1A1',
            'ville' => 'Guelph',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Downtown',
            'latitude' => '43.5448',
            'longitude' => '-80.2482',
            'mapboxId' => 'fixture-ca-guelph-n1h-1a1',
            'streets' => ['Wyndham Street North', 'Woolwich Street', 'Carden Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'N1R 1A1',
            'ville' => 'Cambridge',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Galt',
            'latitude' => '43.3616',
            'longitude' => '-80.3144',
            'mapboxId' => 'fixture-ca-cambridge-n1r-1a1',
            'streets' => ['Main Street', 'Ainslie Street North', 'Water Street North'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'L2E 1A1',
            'ville' => 'Niagara Falls',
            'region' => 'Ontario',
            'department' => 'Ontario',
            'neighborhood' => 'Fallsview',
            'latitude' => '43.0896',
            'longitude' => '-79.0849',
            'mapboxId' => 'fixture-ca-niagara-falls-l2e-1a1',
            'streets' => ['Clifton Hill', 'Fallsview Boulevard', 'Queen Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'E1C 1A1',
            'ville' => 'Moncton',
            'region' => 'Nouveau-Brunswick',
            'department' => 'New Brunswick',
            'neighborhood' => 'Downtown',
            'latitude' => '46.0878',
            'longitude' => '-64.7782',
            'mapboxId' => 'fixture-ca-moncton-e1c-1a1',
            'streets' => ['Main Street', 'St. George Street', 'Assomption Boulevard'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'E3B 1A1',
            'ville' => 'Fredericton',
            'region' => 'Nouveau-Brunswick',
            'department' => 'New Brunswick',
            'neighborhood' => 'Downtown',
            'latitude' => '45.9636',
            'longitude' => '-66.6431',
            'mapboxId' => 'fixture-ca-fredericton-e3b-1a1',
            'streets' => ['Queen Street', 'King Street', 'Regent Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'C1A 1A1',
            'ville' => 'Charlottetown',
            'region' => 'Île-du-Prince-Édouard',
            'department' => 'Prince Edward Island',
            'neighborhood' => 'Downtown',
            'latitude' => '46.2382',
            'longitude' => '-63.1311',
            'mapboxId' => 'fixture-ca-charlottetown-c1a-1a1',
            'streets' => ['Queen Street', 'Great George Street', 'Water Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'T4N 1A1',
            'ville' => 'Red Deer',
            'region' => 'Alberta',
            'department' => 'Alberta',
            'neighborhood' => 'Downtown',
            'latitude' => '52.2681',
            'longitude' => '-113.8112',
            'mapboxId' => 'fixture-ca-red-deer-t4n-1a1',
            'streets' => ['Ross Street', 'Gaetz Avenue', 'Taylor Drive'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'T1J 1A1',
            'ville' => 'Lethbridge',
            'region' => 'Alberta',
            'department' => 'Alberta',
            'neighborhood' => 'London Road',
            'latitude' => '49.6956',
            'longitude' => '-112.8451',
            'mapboxId' => 'fixture-ca-lethbridge-t1j-1a1',
            'streets' => ['3 Avenue South', '13 Street North', 'Mayor Magrath Drive'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'T1A 1A1',
            'ville' => 'Medicine Hat',
            'region' => 'Alberta',
            'department' => 'Alberta',
            'neighborhood' => 'Riverside',
            'latitude' => '50.0405',
            'longitude' => '-110.6765',
            'mapboxId' => 'fixture-ca-medicine-hat-t1a-1a1',
            'streets' => ['2 Street SE', 'South Railway Street', 'Kingsway Avenue SE'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V9R 1A1',
            'ville' => 'Nanaimo',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'Old City',
            'latitude' => '49.1659',
            'longitude' => '-123.9401',
            'mapboxId' => 'fixture-ca-nanaimo-v9r-1a1',
            'streets' => ['Commercial Street', 'Front Street', 'Terminal Avenue'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V2C 1A1',
            'ville' => 'Kamloops',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'City Centre',
            'latitude' => '50.6745',
            'longitude' => '-120.3273',
            'mapboxId' => 'fixture-ca-kamloops-v2c-1a1',
            'streets' => ['Victoria Street', 'Lansdowne Street', 'Tranquille Road'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'V2L 1A1',
            'ville' => 'Prince George',
            'region' => 'Colombie-Britannique',
            'department' => 'British Columbia',
            'neighborhood' => 'Downtown',
            'latitude' => '53.9171',
            'longitude' => '-122.7497',
            'mapboxId' => 'fixture-ca-prince-george-v2l-1a1',
            'streets' => ['George Street', '3rd Avenue', 'Victoria Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'Y1A 1A1',
            'ville' => 'Whitehorse',
            'region' => 'Yukon',
            'department' => 'Yukon',
            'neighborhood' => 'Downtown',
            'latitude' => '60.7212',
            'longitude' => '-135.0568',
            'mapboxId' => 'fixture-ca-whitehorse-y1a-1a1',
            'streets' => ['Main Street', '2nd Avenue', 'Front Street'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'X1A 1A1',
            'ville' => 'Yellowknife',
            'region' => 'Territoires du Nord-Ouest',
            'department' => 'Northwest Territories',
            'neighborhood' => 'Downtown',
            'latitude' => '62.4540',
            'longitude' => '-114.3718',
            'mapboxId' => 'fixture-ca-yellowknife-x1a-1a1',
            'streets' => ['Franklin Avenue', '49 Street', 'Range Lake Road'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'X0A 0H0',
            'ville' => 'Iqaluit',
            'region' => 'Nunavut',
            'department' => 'Nunavut',
            'neighborhood' => 'Centre-ville',
            'latitude' => '63.7467',
            'longitude' => '-68.5170',
            'mapboxId' => 'fixture-ca-iqaluit-x0a-0h0',
            'streets' => ['Federal Road', 'Queen Elizabeth Way', 'Niaqunngusiariaq Road'],
        ],
        [
            'countryCode' => 'CA',
            'country' => 'Canada',
            'codePostal' => 'T1L 1A1',
            'ville' => 'Banff',
            'region' => 'Alberta',
            'department' => 'Alberta',
            'neighborhood' => 'Town Centre',
            'latitude' => '51.1784',
            'longitude' => '-115.5708',
            'mapboxId' => 'fixture-ca-banff-t1l-1a1',
            'streets' => ['Banff Avenue', 'Bear Street', 'Buffalo Street'],
        ],
    ];


    private const MOROCCO_LOCATIONS = [
        [
            'countryCode' => 'MA',
            'country' => 'Maroc',
            'codePostal' => '40000',
            'ville' => 'Marrakech',
            'region' => 'Marrakech-Safi',
            'department' => 'Préfecture de Marrakech',
            'neighborhood' => 'Guéliz',
            'latitude' => '31.6295',
            'longitude' => '-7.9811',
            'mapboxId' => 'fixture-ma-marrakech-40000',
            'streets' => ['Avenue Mohammed VI', 'Boulevard Mohamed Zerktouni', 'Rue de la Liberté'],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        if (\function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        if (!gc_enabled()) {
            gc_enable();
        }

        $faker = Factory::create('fr_FR');

        $agenceReferences = [
            UserFixtures::USER_AGENCE_REFERENCE,
            UserFixtures::USER_MOHCINE_REFERENCE,
        ];

        for ($i = 1; $i <= 50; ++$i) {
            $agenceReferences[] = UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$i;
        }

        $caracteristiques = $manager
            ->getRepository(Caracteristique::class)
            ->findAll();

        $usedSlugs = [];
        $usedGps = [];
        $usedFullAddresses = [];
        $usedReferences = [];

        $propertyReferenceIndex = 1;

        foreach ($this->getLocations() as $location) {
            $numberOfPropertiesForLocation = $faker->numberBetween(
                self::MIN_PROPERTIES_PER_LOCATION,
                self::MAX_PROPERTIES_PER_LOCATION
            );

            for ($i = 1; $i <= $numberOfPropertiesForLocation; ++$i) {
                $propertyData = $this->pickPropertyProfile($faker);

                $randomGps = $this->generateUniqueRandomGpsAround(
                    (float) $location['latitude'],
                    (float) $location['longitude'],
                    self::GPS_RANDOM_RADIUS_KM,
                    $faker,
                    $usedGps
                );

                $localizedAddress = $this->buildUniqueLocalizedAddress(
                    $faker,
                    $location,
                    $usedFullAddresses
                );

                $metrics = $this->buildRealisticMetrics($faker, $propertyData, $location);
                $referenceInterne = $this->generateUniqueReference($location, $propertyReferenceIndex, $usedReferences);
                $slug = $this->generateUniqueNumericSlug($faker, $usedSlugs);

                /** @var User $user */
                $user = $this->getReference(
                    $faker->randomElement($agenceReferences),
                    User::class
                );

                /** @var CategoryBien $categoryBien */
                $categoryBien = $this->getReference(
                    CategoryBienFixtures::CATEGORY_BIEN_REFERENCE_PREFIX.$propertyData['typeBien'],
                    CategoryBien::class
                );

                /** @var CategoryBienTransaction $categoryBienTransaction */
                $categoryBienTransaction = $this->getReference(
                    CategoryBienTransactionFixtures::CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX.$propertyData['typeTransaction'],
                    CategoryBienTransaction::class
                );

                $property = new Property();

                $property
                    ->setUser($user)
                    ->setTypeBien($categoryBien)
                    ->setTypeTransaction($categoryBienTransaction)
                    ->setCodePostal($location['codePostal'])
                    ->setLatitude($randomGps['latitude'])
                    ->setLongitude($randomGps['longitude'])
                    ->setMapboxId(\sprintf('%s-%06d', $location['mapboxId'], $propertyReferenceIndex))
                    ->setFeatureType('address')
                    ->setShowAdresse($faker->boolean(78))
                    ->setAnneeConstruction((string) $metrics['anneeConstruction'])
                    ->setChambres((string) $metrics['chambres'])
                    ->setSalleDeBains((string) $metrics['salleDeBains'])
                    ->setSurfaceTotal((string) $metrics['surfaceTotal'])
                    ->setDpe((string) $metrics['dpe'])
                    ->setDpeLettre($metrics['dpeLettre'])
                    ->setGes((string) $metrics['ges'])
                    ->setGesLettre($metrics['gesLettre'])
                    ->setDpeMin((string) $metrics['dpeMin'])
                    ->setDpeMax((string) $metrics['dpeMax'])
                    ->setDateIndexationEnergie(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years', 'now')))
                    ->setReferenceInterne($referenceInterne)
                    ->setStatut(StatutAnnonceImmobiliere::PUBLIEE)
                    ->setSlug($slug);

                if ('vente' === $propertyData['typeTransaction']) {
                    $property->setPrix((string) $metrics['prix']);
                }

                if ('location' === $propertyData['typeTransaction']) {
                    $property
                        ->setMontantLoyerHorsCharge((string) $metrics['loyerHorsCharges'])
                        ->setMontantDepotDeGarantie((string) $metrics['depotGarantie'])
                        ->setMontantDesCharges((string) $metrics['charges']);
                }

                $this->fillTranslation($property, 'fr', $localizedAddress['fr'], $propertyData, $metrics, $faker);
                $this->fillTranslation($property, 'en', $localizedAddress['en'], $propertyData, $metrics, $faker);

                $createdAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-10 months', '-2 days'));

                if (method_exists($property, 'setCreatedAt')) {
                    $property->setCreatedAt($createdAt);
                }

                if (method_exists($property, 'setUpdatedAt')) {
                    $property->setUpdatedAt(
                        $createdAt->modify('+'.$faker->numberBetween(1, 45).' days')
                    );
                }

                if ([] !== $caracteristiques) {
                    $maxCaracteristiques = min(9, \count($caracteristiques));
                    $numberOfCaracteristiques = $faker->numberBetween(2, $maxCaracteristiques);

                    foreach ($faker->randomElements($caracteristiques, $numberOfCaracteristiques) as $caracteristique) {
                        $property->addCaracteristique($caracteristique);
                    }
                }

                $property->mergeNewTranslations();

                $manager->persist($property);

                $this->addReference(
                    self::PROPERTY_REFERENCE_PREFIX.$propertyReferenceIndex,
                    $property
                );

                ++$propertyReferenceIndex;
            }
        }

        $manager->flush();
    }

    private function getLocations(): array
    {
        return array_merge(
            self::FRANCE_LOCATIONS,
            self::CANADA_LOCATIONS,
            self::MOROCCO_LOCATIONS,
        );
    }

    private function pickPropertyProfile(\Faker\Generator $faker): array
    {
        $totalWeight = 0;

        foreach (self::PROPERTY_PROFILES as $profile) {
            $totalWeight += $profile['weight'];
        }

        $pick = $faker->numberBetween(1, $totalWeight);
        $current = 0;

        foreach (self::PROPERTY_PROFILES as $profile) {
            $current += $profile['weight'];

            if ($pick <= $current) {
                return $profile;
            }
        }

        return self::PROPERTY_PROFILES[0];
    }

    private function buildRealisticMetrics(\Faker\Generator $faker, array $propertyData, array $location): array
    {
        $typeBien = $propertyData['typeBien'];
        $typeTransaction = $propertyData['typeTransaction'];

        $surface = 0;
        $pieces = 0;
        $chambres = 0;
        $salleDeBains = 0;

        switch ($typeBien) {
            case 'appartement':
                $surface = $faker->numberBetween(24, 125);
                $pieces = max(1, min(6, (int) round($surface / $faker->numberBetween(22, 32))));
                $chambres = max(0, $pieces - 1);
                $salleDeBains = $surface > 85 ? $faker->numberBetween(1, 2) : 1;
                break;

            case 'maison':
                $surface = $faker->numberBetween(75, 230);
                $pieces = $faker->numberBetween(4, 8);
                $chambres = $faker->numberBetween(2, min(6, $pieces - 1));
                $salleDeBains = $faker->numberBetween(1, 3);
                break;

            case 'villa':
                $surface = $faker->numberBetween(130, 380);
                $pieces = $faker->numberBetween(5, 10);
                $chambres = $faker->numberBetween(3, min(7, $pieces - 1));
                $salleDeBains = $faker->numberBetween(2, 5);
                break;

            case 'terrain':
                $surface = $faker->numberBetween(350, 2500);
                $pieces = 0;
                $chambres = 0;
                $salleDeBains = 0;
                break;

            case 'ferme':
                $surface = $faker->numberBetween(140, 520);
                $pieces = $faker->numberBetween(5, 12);
                $chambres = $faker->numberBetween(3, min(8, $pieces - 1));
                $salleDeBains = $faker->numberBetween(1, 4);
                break;

            case 'bureaux':
                $surface = $faker->numberBetween(35, 650);
                $pieces = $faker->numberBetween(1, 12);
                $chambres = 0;
                $salleDeBains = $faker->numberBetween(1, 3);
                break;

            case 'local-commercial':
                $surface = $faker->numberBetween(35, 360);
                $pieces = $faker->numberBetween(1, 6);
                $chambres = 0;
                $salleDeBains = $faker->numberBetween(1, 2);
                break;

            case 'fond-de-commerce':
                $surface = $faker->numberBetween(45, 260);
                $pieces = $faker->numberBetween(1, 5);
                $chambres = 0;
                $salleDeBains = $faker->numberBetween(1, 2);
                break;

            case 'parking-garage-box':
                $surface = $faker->numberBetween(12, 28);
                $pieces = 0;
                $chambres = 0;
                $salleDeBains = 0;
                break;
        }

        $anneeConstruction = $this->buildConstructionYear($faker, $typeBien);
        $energy = $this->buildEnergyData($faker, $anneeConstruction, $typeBien, $location);
        $marketFactor = $this->getMarketFactor($location);

        $prix = null;
        $loyerHorsCharges = null;
        $charges = null;
        $depotGarantie = null;

        if ('vente' === $typeTransaction) {
            $prix = $this->buildSalePrice($faker, $typeBien, $surface, $marketFactor, $location);
        }

        if ('location' === $typeTransaction) {
            $loyerHorsCharges = $this->buildRentPrice($faker, $typeBien, $surface, $marketFactor, $location);
            $charges = $this->buildCharges($faker, $typeBien, $surface, $loyerHorsCharges);
            $depotGarantie = $this->roundToNearest($loyerHorsCharges + $charges, 10);
        }

        return [
            'surfaceTotal' => $surface,
            'pieces' => $pieces,
            'chambres' => $chambres,
            'salleDeBains' => $salleDeBains,
            'anneeConstruction' => $anneeConstruction,
            'dpe' => $energy['dpe'],
            'dpeLettre' => $energy['dpeLettre'],
            'ges' => $energy['ges'],
            'gesLettre' => $energy['gesLettre'],
            'dpeMin' => $energy['dpeMin'],
            'dpeMax' => $energy['dpeMax'],
            'prix' => $prix,
            'loyerHorsCharges' => $loyerHorsCharges,
            'charges' => $charges,
            'depotGarantie' => $depotGarantie,
        ];
    }

    private function buildConstructionYear(\Faker\Generator $faker, string $typeBien): int
    {
        if (\in_array($typeBien, ['terrain', 'parking-garage-box', 'fond-de-commerce'], true)) {
            return $faker->numberBetween(1980, 2025);
        }

        return $faker->randomElement([
            $faker->numberBetween(1900, 1949),
            $faker->numberBetween(1950, 1979),
            $faker->numberBetween(1980, 2005),
            $faker->numberBetween(2006, 2025),
        ]);
    }

    private function buildEnergyData(\Faker\Generator $faker, int $anneeConstruction, string $typeBien, array $location): array
    {
        if (\in_array($typeBien, ['terrain', 'parking-garage-box'], true)) {
            return [
                'dpe' => 0,
                'dpeLettre' => 'A',
                'ges' => 0,
                'gesLettre' => 'A',
                'dpeMin' => 0,
                'dpeMax' => 0,
            ];
        }

        if ($anneeConstruction >= 2013) {
            $letter = $faker->randomElement(['A', 'B', 'B', 'C']);
            $dpe = $faker->numberBetween(45, 145);
            $ges = $faker->numberBetween(3, 18);
        } elseif ($anneeConstruction >= 1990) {
            $letter = $faker->randomElement(['C', 'C', 'D', 'D']);
            $dpe = $faker->numberBetween(120, 230);
            $ges = $faker->numberBetween(12, 38);
        } elseif ($anneeConstruction >= 1970) {
            $letter = $faker->randomElement(['D', 'E', 'E']);
            $dpe = $faker->numberBetween(190, 330);
            $ges = $faker->numberBetween(25, 58);
        } else {
            $letter = $faker->randomElement(['D', 'E', 'F', 'G']);
            $dpe = $faker->numberBetween(230, 420);
            $ges = $faker->numberBetween(35, 82);
        }

        if ('CA' === $location['countryCode']) {
            $letter = $faker->randomElement(['B', 'C', 'C', 'D', 'D', 'E']);
            $dpe = $faker->numberBetween(95, 285);
            $ges = $faker->numberBetween(8, 55);
        }

        return [
            'dpe' => $dpe,
            'dpeLettre' => $letter,
            'ges' => $ges,
            'gesLettre' => $this->buildGesLetter($ges),
            'dpeMin' => $faker->numberBetween(520, 980),
            'dpeMax' => $faker->numberBetween(990, 2450),
        ];
    }

    private function buildGesLetter(int $ges): string
    {
        return match (true) {
            $ges <= 5 => 'A',
            $ges <= 10 => 'B',
            $ges <= 20 => 'C',
            $ges <= 35 => 'D',
            $ges <= 55 => 'E',
            $ges <= 80 => 'F',
            default => 'G',
        };
    }

    private function buildSalePrice(
        \Faker\Generator $faker,
        string $typeBien,
        int $surface,
        float $marketFactor,
        array $location,
    ): int {
        $basePriceM2 = [
            'appartement' => 4300,
            'maison' => 3150,
            'villa' => 5200,
            'terrain' => 260,
            'ferme' => 1850,
            'bureaux' => 3600,
            'local-commercial' => 3400,
            'fond-de-commerce' => 2100,
            'parking-garage-box' => 1450,
        ];

        if ('CA' === $location['countryCode']) {
            $basePriceM2 = [
                'appartement' => 5600,
                'maison' => 3900,
                'villa' => 6200,
                'terrain' => 330,
                'ferme' => 2300,
                'bureaux' => 4100,
                'local-commercial' => 3800,
                'fond-de-commerce' => 2500,
                'parking-garage-box' => 1900,
            ];
        }

        if ('parking-garage-box' === $typeBien) {
            return $this->roundToNearest($faker->numberBetween(12000, 55000) * $marketFactor, 1000);
        }

        if ('fond-de-commerce' === $typeBien) {
            return $this->roundToNearest(($surface * $basePriceM2[$typeBien] * $marketFactor) + $faker->numberBetween(25000, 220000), 1000);
        }

        $variation = $faker->randomFloat(2, 0.88, 1.16);

        return $this->roundToNearest($surface * $basePriceM2[$typeBien] * $marketFactor * $variation, 1000);
    }

    private function buildRentPrice(
        \Faker\Generator $faker,
        string $typeBien,
        int $surface,
        float $marketFactor,
        array $location,
    ): int {
        $baseRentM2 = [
            'appartement' => 17,
            'maison' => 13,
            'villa' => 19,
            'bureaux' => 21,
            'local-commercial' => 24,
            'parking-garage-box' => 8,
        ];

        if ('CA' === $location['countryCode']) {
            $baseRentM2 = [
                'appartement' => 23,
                'maison' => 18,
                'villa' => 26,
                'bureaux' => 28,
                'local-commercial' => 30,
                'parking-garage-box' => 10,
            ];
        }

        if ('parking-garage-box' === $typeBien) {
            return $this->roundToNearest($faker->numberBetween(60, 260) * $marketFactor, 10);
        }

        $base = $baseRentM2[$typeBien] ?? 18;
        $variation = $faker->randomFloat(2, 0.88, 1.20);

        return max(380, $this->roundToNearest($surface * $base * $marketFactor * $variation, 10));
    }

    private function buildCharges(\Faker\Generator $faker, string $typeBien, int $surface, int $loyerHorsCharges): int
    {
        if ('parking-garage-box' === $typeBien) {
            return $faker->numberBetween(5, 35);
        }

        if (\in_array($typeBien, ['bureaux', 'local-commercial'], true)) {
            return $this->roundToNearest(max(80, $surface * $faker->numberBetween(3, 7)), 10);
        }

        return $this->roundToNearest(max(35, $loyerHorsCharges * $faker->randomFloat(2, 0.08, 0.18)), 10);
    }

    private function getMarketFactor(array $location): float
    {
        $cityFactor = self::MARKET_FACTORS[$location['ville']] ?? ('CA' === $location['countryCode'] ? 1.12 : 1.00);

        return (float) $cityFactor;
    }

    private function roundToNearest(float|int $value, int $nearest): int
    {
        return (int) (round($value / $nearest) * $nearest);
    }

    private function buildUniqueLocalizedAddress(
        \Faker\Generator $faker,
        array $location,
        array &$usedFullAddresses,
    ): array {
        $attempts = 0;

        do {
            ++$attempts;

            $street = $faker->randomElement($location['streets']);
            $streetNumber = $faker->numberBetween(1, 220);
            $adresse = $streetNumber.' '.$street;

            if ('CA' === $location['countryCode']) {
                $fullAddress = \sprintf(
                    '%s, %s, %s %s, Canada',
                    $adresse,
                    $location['ville'],
                    $location['region'],
                    $location['codePostal']
                );
            } else {
                $fullAddress = \sprintf(
                    '%s, %s %s, France',
                    $adresse,
                    $location['codePostal'],
                    $location['ville']
                );
            }

            $key = mb_strtolower($location['countryCode'].'|'.$fullAddress);
        } while (isset($usedFullAddresses[$key]) && $attempts < 250);

        if (isset($usedFullAddresses[$key])) {
            throw new \RuntimeException(\sprintf('Impossible de générer une adresse unique pour %s %s.', $location['codePostal'], $location['ville']));
        }

        $usedFullAddresses[$key] = true;

        $base = [
            'adresse' => $adresse,
            'ville' => $location['ville'],
            'pays' => $location['country'],
            'region' => $location['region'],
            'district' => $location['department'],
            'locality' => $location['ville'],
            'neighborhood' => $location['neighborhood'],
            'poi' => null,
            'fullAddress' => $fullAddress,
            'countryCode' => $location['countryCode'],
        ];

        return [
            'fr' => $base,
            'en' => $base,
        ];
    }

    private function fillTranslation(
        Property $property,
        string $locale,
        array $address,
        array $propertyData,
        array $metrics,
        \Faker\Generator $faker,
    ): void {
        $translation = $property->translate($locale);

        $translation->setAdresse($address['adresse']);
        $translation->setVille($address['ville']);
        $translation->setPays($address['pays']);
        $translation->setFullAddress($address['fullAddress']);
        $translation->setRegion($address['region']);
        $translation->setDistrict($address['district']);
        $translation->setLocality($address['locality']);
        $translation->setNeighborhood($address['neighborhood']);
        $translation->setPoi($address['poi']);

        if ('fr' === $locale) {
            $translation->setTitreDuLogement(
                $this->buildTitleFr($address, $propertyData, $metrics, $faker)
            );

            $translation->setDescriptionLogement(
                $this->buildDescriptionFr($address, $propertyData, $metrics, $faker)
            );

            return;
        }

        $translation->setTitreDuLogement(
            $this->buildTitleEn($address, $propertyData, $metrics, $faker)
        );

        $translation->setDescriptionLogement(
            $this->buildDescriptionEn($address, $propertyData, $metrics, $faker)
        );
    }

    private function buildTitleFr(array $address, array $propertyData, array $metrics, \Faker\Generator $faker): string
    {
        $type = $this->getTypeLabelFr($propertyData['typeBien']);

        if ('terrain' === $propertyData['typeBien']) {
            return \sprintf('Terrain constructible %d m² - %s %s', $metrics['surfaceTotal'], $address['ville'], $address['neighborhood']);
        }

        if ('parking-garage-box' === $propertyData['typeBien']) {
            return \sprintf('Parking / box sécurisé - %s %s', $address['ville'], $address['neighborhood']);
        }

        if (\in_array($propertyData['typeBien'], ['bureaux', 'local-commercial', 'fond-de-commerce'], true)) {
            return \sprintf('%s %d m² - %s %s', ucfirst($type), $metrics['surfaceTotal'], $address['ville'], $address['neighborhood']);
        }

        $extra = $faker->randomElement([
            'lumineux',
            'rénové',
            'avec extérieur',
            'au calme',
            'proche centre',
            'familial',
        ]);

        return \sprintf(
            '%s %d pièces %d m² %s - %s %s',
            ucfirst($type),
            $metrics['pieces'],
            $metrics['surfaceTotal'],
            $extra,
            $address['ville'],
            $address['neighborhood']
        );
    }

    private function buildTitleEn(array $address, array $propertyData, array $metrics, \Faker\Generator $faker): string
    {
        $type = $this->getTypeLabelEn($propertyData['typeBien']);

        if ('terrain' === $propertyData['typeBien']) {
            return \sprintf('Building land %d sqm - %s %s', $metrics['surfaceTotal'], $address['ville'], $address['neighborhood']);
        }

        if ('parking-garage-box' === $propertyData['typeBien']) {
            return \sprintf('Secure parking / garage - %s %s', $address['ville'], $address['neighborhood']);
        }

        if (\in_array($propertyData['typeBien'], ['bureaux', 'local-commercial', 'fond-de-commerce'], true)) {
            return \sprintf('%s %d sqm - %s %s', ucfirst($type), $metrics['surfaceTotal'], $address['ville'], $address['neighborhood']);
        }

        $extra = $faker->randomElement([
            'bright',
            'renovated',
            'with outdoor space',
            'quiet',
            'near the centre',
            'family-friendly',
        ]);

        return \sprintf(
            '%s %d rooms %d sqm, %s - %s %s',
            ucfirst($type),
            $metrics['pieces'],
            $metrics['surfaceTotal'],
            $extra,
            $address['ville'],
            $address['neighborhood']
        );
    }

    private function buildDescriptionFr(array $address, array $propertyData, array $metrics, \Faker\Generator $faker): string
    {
        $type = $this->getTypeLabelFr($propertyData['typeBien']);
        $transaction = 'vente' === $propertyData['typeTransaction'] ? 'à la vente' : 'à la location';

        $qualities = $faker->randomElements([
            'belle luminosité',
            'plan optimisé',
            'séjour agréable',
            'quartier recherché',
            'accès rapide aux transports',
            'commerces à proximité',
            'environnement calme',
            'bonne performance énergétique',
            'adresse pratique au quotidien',
        ], 3);

        if ('terrain' === $propertyData['typeBien']) {
            return \sprintf(
                'À %s, secteur %s, terrain de %d m² proposé à la vente. La parcelle offre un beau potentiel pour un projet de construction, avec un environnement résidentiel et un accès simple aux commodités. Emplacement cohérent pour une résidence principale, une maison familiale ou un investissement patrimonial.',
                $address['ville'],
                $address['neighborhood'],
                $metrics['surfaceTotal']
            );
        }

        if ('parking-garage-box' === $propertyData['typeBien']) {
            return \sprintf(
                'À louer à %s, secteur %s, emplacement de stationnement sécurisé et facile d’accès. Solution idéale pour un résident du quartier, un professionnel ou un second véhicule. Accès simple, secteur pratique et forte demande locale.',
                $address['ville'],
                $address['neighborhood']
            );
        }

        if (\in_array($propertyData['typeBien'], ['bureaux', 'local-commercial', 'fond-de-commerce'], true)) {
            return \sprintf(
                '%s de %d m² situé à %s, dans le secteur %s. Ce bien professionnel bénéficie d’une adresse visible, d’un agencement exploitable et d’un environnement dynamique. Points forts : %s. Idéal pour développer une activité, installer une équipe ou sécuriser un emplacement commercial.',
                ucfirst($type),
                $metrics['surfaceTotal'],
                $address['ville'],
                $address['neighborhood'],
                implode(', ', $qualities)
            );
        }

        return \sprintf(
            'Situé à %s, dans le secteur %s, ce %s %s développe %d m² avec %d pièces, dont %d chambre(s), et %d salle(s) de bain. Le bien se distingue par sa %s, son %s et son %s. L’adresse permet de profiter des commerces, écoles, services et transports du quartier. Une annonce réaliste, pensée comme une vraie fiche immobilière avec informations essentielles, localisation cohérente et descriptif exploitable.',
            $address['ville'],
            $address['neighborhood'],
            $type,
            $transaction,
            $metrics['surfaceTotal'],
            $metrics['pieces'],
            $metrics['chambres'],
            $metrics['salleDeBains'],
            $qualities[0],
            $qualities[1],
            $qualities[2]
        );
    }

    private function buildDescriptionEn(array $address, array $propertyData, array $metrics, \Faker\Generator $faker): string
    {
        $type = $this->getTypeLabelEn($propertyData['typeBien']);
        $transaction = 'vente' === $propertyData['typeTransaction'] ? 'for sale' : 'for rent';

        $qualities = $faker->randomElements([
            'good natural light',
            'efficient layout',
            'comfortable living area',
            'sought-after neighbourhood',
            'easy access to transport',
            'nearby shops and services',
            'quiet surroundings',
            'solid energy profile',
            'practical everyday address',
        ], 3);

        if ('terrain' === $propertyData['typeBien']) {
            return \sprintf(
                'Located in %s, in the %s area, this %d sqm building plot is offered for sale. The land provides strong potential for a construction project, with a residential environment and easy access to local amenities.',
                $address['ville'],
                $address['neighborhood'],
                $metrics['surfaceTotal']
            );
        }

        if ('parking-garage-box' === $propertyData['typeBien']) {
            return \sprintf(
                'Secure parking space for rent in %s, %s area. A practical solution for a local resident, a professional or a second vehicle. Easy access and convenient neighbourhood location.',
                $address['ville'],
                $address['neighborhood']
            );
        }

        if (\in_array($propertyData['typeBien'], ['bureaux', 'local-commercial', 'fond-de-commerce'], true)) {
            return \sprintf(
                '%s of %d sqm located in %s, in the %s area. This professional property benefits from a visible address, usable layout and dynamic surroundings. Highlights include %s. Suitable for a business, team office or commercial location.',
                ucfirst($type),
                $metrics['surfaceTotal'],
                $address['ville'],
                $address['neighborhood'],
                implode(', ', $qualities)
            );
        }

        return \sprintf(
            'Located in %s, in the %s area, this %s %s offers %d sqm with %d rooms, including %d bedroom(s), and %d bathroom(s). The property stands out for its %s, %s and %s. The address gives easy access to shops, schools, services and transport. A realistic listing designed like a professional real estate advert.',
            $address['ville'],
            $address['neighborhood'],
            $type,
            $transaction,
            $metrics['surfaceTotal'],
            $metrics['pieces'],
            $metrics['chambres'],
            $metrics['salleDeBains'],
            $qualities[0],
            $qualities[1],
            $qualities[2]
        );
    }

    private function getTypeLabelFr(string $typeBien): string
    {
        return [
            'maison' => 'maison',
            'appartement' => 'appartement',
            'villa' => 'villa',
            'fond-de-commerce' => 'fonds de commerce',
            'bureaux' => 'bureaux',
            'local-commercial' => 'local commercial',
            'terrain' => 'terrain',
            'ferme' => 'ferme',
            'parking-garage-box' => 'parking, garage ou box',
        ][$typeBien] ?? 'bien immobilier';
    }

    private function getTypeLabelEn(string $typeBien): string
    {
        return [
            'maison' => 'house',
            'appartement' => 'apartment',
            'villa' => 'villa',
            'fond-de-commerce' => 'business assets',
            'bureaux' => 'office space',
            'local-commercial' => 'commercial premises',
            'terrain' => 'land',
            'ferme' => 'farm',
            'parking-garage-box' => 'parking space, garage or box',
        ][$typeBien] ?? 'property';
    }

    private function generateUniqueReference(array $location, int $propertyReferenceIndex, array &$usedReferences): string
    {
        do {
            $reference = \sprintf(
                'TM-%s-%s-%06d',
                $location['countryCode'],
                preg_replace('/[^A-Z0-9]/', '', mb_strtoupper(mb_substr($location['ville'], 0, 4))),
                $propertyReferenceIndex
            );
        } while (isset($usedReferences[$reference]));

        $usedReferences[$reference] = true;

        return $reference;
    }

    private function generateUniqueRandomGpsAround(
        float $latitude,
        float $longitude,
        float $radiusKm,
        \Faker\Generator $faker,
        array &$usedGps,
    ): array {
        $attempts = 0;

        do {
            ++$attempts;

            $gps = $this->generateRandomGpsAround(
                $latitude,
                $longitude,
                $radiusKm,
                $faker
            );

            $key = $gps['latitude'].'|'.$gps['longitude'];
        } while (isset($usedGps[$key]) && $attempts < 250);

        if (isset($usedGps[$key])) {
            throw new \RuntimeException('Impossible de générer des coordonnées GPS uniques.');
        }

        $usedGps[$key] = true;

        return $gps;
    }

    private function generateRandomGpsAround(
        float $latitude,
        float $longitude,
        float $radiusKm,
        \Faker\Generator $faker,
    ): array {
        $distanceKm = $radiusKm * sqrt($faker->randomFloat(6, 0, 1));
        $bearing = deg2rad($faker->randomFloat(6, 0, 360));

        $lat1 = deg2rad($latitude);
        $lon1 = deg2rad($longitude);

        $angularDistance = $distanceKm / self::EARTH_RADIUS_KM;

        $lat2 = asin(
            sin($lat1) * cos($angularDistance)
            + cos($lat1) * sin($angularDistance) * cos($bearing)
        );

        $lon2 = $lon1 + atan2(
            sin($bearing) * sin($angularDistance) * cos($lat1),
            cos($angularDistance) - sin($lat1) * sin($lat2)
        );

        $lon2 = fmod($lon2 + 3 * \M_PI, 2 * \M_PI) - \M_PI;

        return [
            'latitude' => number_format(rad2deg($lat2), 7, '.', ''),
            'longitude' => number_format(rad2deg($lon2), 7, '.', ''),
        ];
    }

    private function generateUniqueNumericSlug(\Faker\Generator $faker, array &$usedSlugs): string
    {
        do {
            $slug = (string) $faker->numberBetween(100000000, 999999999);
        } while (isset($usedSlugs[$slug]));

        $usedSlugs[$slug] = true;

        return $slug;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryBienFixtures::class,
            CategoryBienTransactionFixtures::class,
            CaracteristiqueFixtures::class,
        ];
    }
}
