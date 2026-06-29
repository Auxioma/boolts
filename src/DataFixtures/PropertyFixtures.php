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
     * Conservé uniquement si d’autres fixtures l’utilisent encore.
     * Le nombre réel de biens est maintenant dynamique :
     * chaque ville génère entre 10 et 30 biens.
     */
    public const PROPERTY_COUNT = 100;

    private const MIN_PROPERTIES_PER_LOCATION = 10;
    private const MAX_PROPERTIES_PER_LOCATION = 30;

    /**
     * Rayon GPS autour du centre de la ville.
     *
     * Pour rester cohérent avec une ville / commune de région parisienne,
     * 2.5 km est beaucoup plus propre que 25 ou 50 km.
     */
    private const GPS_RANDOM_RADIUS_KM = 2.5;

    private const EARTH_RADIUS_KM = 6371.0088;

    private const PROPERTIES = [
        ['typeBien' => 'maison', 'typeTransaction' => 'vente'],
        ['typeBien' => 'appartement', 'typeTransaction' => 'location'],
        ['typeBien' => 'villa', 'typeTransaction' => 'vente'],
        ['typeBien' => 'fond-de-commerce', 'typeTransaction' => 'vente'],
        ['typeBien' => 'bureaux', 'typeTransaction' => 'location'],
        ['typeBien' => 'local-commercial', 'typeTransaction' => 'location'],
        ['typeBien' => 'terrain', 'typeTransaction' => 'vente'],
        ['typeBien' => 'ferme', 'typeTransaction' => 'vente'],
        ['typeBien' => 'parking-garage-box', 'typeTransaction' => 'location'],
    ];

    private const LOCATIONS = [
        [
            'codePostal' => '75001',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Louvre',
            'latitude' => '48.8647160',
            'longitude' => '2.3490140',
            'mapboxId' => 'fixture-idf-paris-75001',
            'streets' => ['Rue de Rivoli', 'Rue Saint-Honoré', 'Place Vendôme'],
        ],
        [
            'codePostal' => '75002',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Bourse',
            'latitude' => '48.8682790',
            'longitude' => '2.3428030',
            'mapboxId' => 'fixture-idf-paris-75002',
            'streets' => ['Rue Montmartre', 'Rue Réaumur', 'Rue Vivienne'],
        ],
        [
            'codePostal' => '75003',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Temple',
            'latitude' => '48.8635000',
            'longitude' => '2.3590000',
            'mapboxId' => 'fixture-idf-paris-75003',
            'streets' => ['Rue de Bretagne', 'Rue Vieille-du-Temple', 'Rue des Archives'],
        ],
        [
            'codePostal' => '75004',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Marais',
            'latitude' => '48.8566000',
            'longitude' => '2.3522000',
            'mapboxId' => 'fixture-idf-paris-75004',
            'streets' => ['Rue Saint-Antoine', 'Rue de Rivoli', 'Rue du Roi de Sicile'],
        ],
        [
            'codePostal' => '75005',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Quartier Latin',
            'latitude' => '48.8462000',
            'longitude' => '2.3445000',
            'mapboxId' => 'fixture-idf-paris-75005',
            'streets' => ['Boulevard Saint-Michel', 'Rue Monge', 'Rue Mouffetard'],
        ],
        [
            'codePostal' => '75006',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Saint-Germain-des-Prés',
            'latitude' => '48.8493000',
            'longitude' => '2.3327000',
            'mapboxId' => 'fixture-idf-paris-75006',
            'streets' => ['Boulevard Saint-Germain', 'Rue de Rennes', 'Rue Bonaparte'],
        ],
        [
            'codePostal' => '75007',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Invalides',
            'latitude' => '48.8565000',
            'longitude' => '2.3126000',
            'mapboxId' => 'fixture-idf-paris-75007',
            'streets' => ['Avenue de la Motte-Picquet', 'Rue de Grenelle', 'Rue Saint-Dominique'],
        ],
        [
            'codePostal' => '75008',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Champs-Élysées',
            'latitude' => '48.8729000',
            'longitude' => '2.3116000',
            'mapboxId' => 'fixture-idf-paris-75008',
            'streets' => ['Avenue des Champs-Élysées', 'Rue du Faubourg Saint-Honoré', 'Avenue Montaigne'],
        ],
        [
            'codePostal' => '75009',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Opéra',
            'latitude' => '48.8790000',
            'longitude' => '2.3370000',
            'mapboxId' => 'fixture-idf-paris-75009',
            'streets' => ['Rue de Maubeuge', 'Rue de Châteaudun', 'Rue Lafayette'],
        ],
        [
            'codePostal' => '75010',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Canal Saint-Martin',
            'latitude' => '48.8763000',
            'longitude' => '2.3599000',
            'mapboxId' => 'fixture-idf-paris-75010',
            'streets' => ['Rue du Faubourg Saint-Martin', 'Quai de Valmy', 'Rue de Lancry'],
        ],
        [
            'codePostal' => '75011',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Bastille',
            'latitude' => '48.8575000',
            'longitude' => '2.3795000',
            'mapboxId' => 'fixture-idf-paris-75011',
            'streets' => ['Rue Oberkampf', 'Rue de la Roquette', 'Boulevard Voltaire'],
        ],
        [
            'codePostal' => '75012',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Bercy',
            'latitude' => '48.8396000',
            'longitude' => '2.3958000',
            'mapboxId' => 'fixture-idf-paris-75012',
            'streets' => ['Rue de Charenton', 'Avenue Daumesnil', 'Cour Saint-Émilion'],
        ],
        [
            'codePostal' => '75013',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Butte-aux-Cailles',
            'latitude' => '48.8322000',
            'longitude' => '2.3556000',
            'mapboxId' => 'fixture-idf-paris-75013',
            'streets' => ['Avenue d’Italie', 'Rue de Tolbiac', 'Boulevard Vincent-Auriol'],
        ],
        [
            'codePostal' => '75014',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Montparnasse',
            'latitude' => '48.8331000',
            'longitude' => '2.3264000',
            'mapboxId' => 'fixture-idf-paris-75014',
            'streets' => ['Avenue du Maine', 'Rue d’Alésia', 'Boulevard Raspail'],
        ],
        [
            'codePostal' => '75015',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Vaugirard',
            'latitude' => '48.8414000',
            'longitude' => '2.3003000',
            'mapboxId' => 'fixture-idf-paris-75015',
            'streets' => ['Rue de Vaugirard', 'Rue Lecourbe', 'Boulevard de Grenelle'],
        ],
        [
            'codePostal' => '75016',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Passy',
            'latitude' => '48.8637000',
            'longitude' => '2.2769000',
            'mapboxId' => 'fixture-idf-paris-75016',
            'streets' => ['Rue de Passy', 'Avenue Victor Hugo', 'Boulevard Suchet'],
        ],
        [
            'codePostal' => '75017',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Batignolles',
            'latitude' => '48.8835000',
            'longitude' => '2.3219000',
            'mapboxId' => 'fixture-idf-paris-75017',
            'streets' => ['Rue des Batignolles', 'Avenue de Clichy', 'Boulevard Malesherbes'],
        ],
        [
            'codePostal' => '75018',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Montmartre',
            'latitude' => '48.8867040',
            'longitude' => '2.3404520',
            'mapboxId' => 'fixture-idf-paris-75018',
            'streets' => ['Rue Lepic', 'Rue Caulaincourt', 'Rue des Abbesses'],
        ],
        [
            'codePostal' => '75019',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Buttes-Chaumont',
            'latitude' => '48.8838000',
            'longitude' => '2.3817000',
            'mapboxId' => 'fixture-idf-paris-75019',
            'streets' => ['Avenue Jean-Jaurès', 'Rue de Crimée', 'Rue Manin'],
        ],
        [
            'codePostal' => '75020',
            'ville' => 'Paris',
            'department' => 'Paris',
            'neighborhood' => 'Belleville',
            'latitude' => '48.8647000',
            'longitude' => '2.3984000',
            'mapboxId' => 'fixture-idf-paris-75020',
            'streets' => ['Rue de Belleville', 'Rue des Pyrénées', 'Boulevard de Charonne'],
        ],

        [
            'codePostal' => '92100',
            'ville' => 'Boulogne-Billancourt',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre-ville',
            'latitude' => '48.8397000',
            'longitude' => '2.2399000',
            'mapboxId' => 'fixture-idf-boulogne-billancourt-92100',
            'streets' => ['Avenue Jean-Baptiste Clément', 'Route de la Reine', 'Rue Gallieni'],
        ],
        [
            'codePostal' => '92000',
            'ville' => 'Nanterre',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre',
            'latitude' => '48.8924000',
            'longitude' => '2.2153000',
            'mapboxId' => 'fixture-idf-nanterre-92000',
            'streets' => ['Avenue Georges Clemenceau', 'Rue Maurice Thorez', 'Boulevard du Couchant'],
        ],
        [
            'codePostal' => '92400',
            'ville' => 'Courbevoie',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Bécon',
            'latitude' => '48.8967000',
            'longitude' => '2.2567000',
            'mapboxId' => 'fixture-idf-courbevoie-92400',
            'streets' => ['Avenue Marceau', 'Rue de Bezons', 'Boulevard Saint-Denis'],
        ],
        [
            'codePostal' => '92700',
            'ville' => 'Colombes',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre',
            'latitude' => '48.9221000',
            'longitude' => '2.2548000',
            'mapboxId' => 'fixture-idf-colombes-92700',
            'streets' => ['Rue Saint-Denis', 'Boulevard Charles de Gaulle', 'Avenue Henri Barbusse'],
        ],
        [
            'codePostal' => '92600',
            'ville' => 'Asnières-sur-Seine',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre',
            'latitude' => '48.9146000',
            'longitude' => '2.2854000',
            'mapboxId' => 'fixture-idf-asnieres-sur-seine-92600',
            'streets' => ['Rue des Bourguignons', 'Grande Rue Charles de Gaulle', 'Avenue d’Argenteuil'],
        ],
        [
            'codePostal' => '92300',
            'ville' => 'Levallois-Perret',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre',
            'latitude' => '48.8932000',
            'longitude' => '2.2879000',
            'mapboxId' => 'fixture-idf-levallois-perret-92300',
            'streets' => ['Rue du Président Wilson', 'Rue Anatole France', 'Rue Baudin'],
        ],
        [
            'codePostal' => '92200',
            'ville' => 'Neuilly-sur-Seine',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Sablons',
            'latitude' => '48.8846000',
            'longitude' => '2.2697000',
            'mapboxId' => 'fixture-idf-neuilly-sur-seine-92200',
            'streets' => ['Avenue Charles de Gaulle', 'Rue de Chartres', 'Boulevard Bineau'],
        ],
        [
            'codePostal' => '92110',
            'ville' => 'Clichy',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre',
            'latitude' => '48.9045000',
            'longitude' => '2.3048000',
            'mapboxId' => 'fixture-idf-clichy-92110',
            'streets' => ['Rue Martre', 'Boulevard Jean Jaurès', 'Rue de Paris'],
        ],
        [
            'codePostal' => '92130',
            'ville' => 'Issy-les-Moulineaux',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Corentin Celton',
            'latitude' => '48.8245000',
            'longitude' => '2.2743000',
            'mapboxId' => 'fixture-idf-issy-les-moulineaux-92130',
            'streets' => ['Rue Ernest Renan', 'Avenue Victor Cresson', 'Boulevard Gallieni'],
        ],
        [
            'codePostal' => '92500',
            'ville' => 'Rueil-Malmaison',
            'department' => 'Hauts-de-Seine',
            'neighborhood' => 'Centre',
            'latitude' => '48.8760000',
            'longitude' => '2.1811000',
            'mapboxId' => 'fixture-idf-rueil-malmaison-92500',
            'streets' => ['Avenue Paul Doumer', 'Rue Hervet', 'Boulevard Richelieu'],
        ],

        [
            'codePostal' => '93200',
            'ville' => 'Saint-Denis',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Centre',
            'latitude' => '48.9362000',
            'longitude' => '2.3574000',
            'mapboxId' => 'fixture-idf-saint-denis-93200',
            'streets' => ['Rue de la République', 'Boulevard Carnot', 'Rue Gabriel Péri'],
        ],
        [
            'codePostal' => '93100',
            'ville' => 'Montreuil',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Croix de Chavaux',
            'latitude' => '48.8638000',
            'longitude' => '2.4485000',
            'mapboxId' => 'fixture-idf-montreuil-93100',
            'streets' => ['Rue de Paris', 'Boulevard Rouget de Lisle', 'Avenue de la Résistance'],
        ],
        [
            'codePostal' => '93300',
            'ville' => 'Aubervilliers',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Centre',
            'latitude' => '48.9146000',
            'longitude' => '2.3822000',
            'mapboxId' => 'fixture-idf-aubervilliers-93300',
            'streets' => ['Avenue Victor Hugo', 'Rue du Moutier', 'Rue de la Commune de Paris'],
        ],
        [
            'codePostal' => '93500',
            'ville' => 'Pantin',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Église',
            'latitude' => '48.8956000',
            'longitude' => '2.4093000',
            'mapboxId' => 'fixture-idf-pantin-93500',
            'streets' => ['Avenue Jean Lolive', 'Rue Hoche', 'Rue Cartier-Bresson'],
        ],
        [
            'codePostal' => '93000',
            'ville' => 'Bobigny',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Centre',
            'latitude' => '48.9086000',
            'longitude' => '2.4397000',
            'mapboxId' => 'fixture-idf-bobigny-93000',
            'streets' => ['Avenue Jean Jaurès', 'Rue de la République', 'Avenue Henri Barbusse'],
        ],
        [
            'codePostal' => '93700',
            'ville' => 'Drancy',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Centre',
            'latitude' => '48.9258000',
            'longitude' => '2.4453000',
            'mapboxId' => 'fixture-idf-drancy-93700',
            'streets' => ['Avenue Henri Barbusse', 'Rue Anatole France', 'Avenue Jean Jaurès'],
        ],
        [
            'codePostal' => '93160',
            'ville' => 'Noisy-le-Grand',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Mont d’Est',
            'latitude' => '48.8498000',
            'longitude' => '2.5529000',
            'mapboxId' => 'fixture-idf-noisy-le-grand-93160',
            'streets' => ['Avenue Aristide Briand', 'Boulevard du Mont d’Est', 'Rue Pierre Brossolette'],
        ],
        [
            'codePostal' => '93600',
            'ville' => 'Aulnay-sous-Bois',
            'department' => 'Seine-Saint-Denis',
            'neighborhood' => 'Centre',
            'latitude' => '48.9381000',
            'longitude' => '2.4940000',
            'mapboxId' => 'fixture-idf-aulnay-sous-bois-93600',
            'streets' => ['Boulevard de Strasbourg', 'Rue Anatole France', 'Avenue Dumont'],
        ],

        [
            'codePostal' => '94000',
            'ville' => 'Créteil',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'Préfecture',
            'latitude' => '48.7904000',
            'longitude' => '2.4556000',
            'mapboxId' => 'fixture-idf-creteil-94000',
            'streets' => ['Avenue du Général de Gaulle', 'Rue de Paris', 'Avenue Pierre Brossolette'],
        ],
        [
            'codePostal' => '94400',
            'ville' => 'Vitry-sur-Seine',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.7872000',
            'longitude' => '2.4033000',
            'mapboxId' => 'fixture-idf-vitry-sur-seine-94400',
            'streets' => ['Avenue Paul Vaillant-Couturier', 'Rue Gabriel Péri', 'Avenue Rouget de Lisle'],
        ],
        [
            'codePostal' => '94100',
            'ville' => 'Saint-Maur-des-Fossés',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'La Varenne',
            'latitude' => '48.7993000',
            'longitude' => '2.4994000',
            'mapboxId' => 'fixture-idf-saint-maur-des-fosses-94100',
            'streets' => ['Avenue du Bac', 'Boulevard de Créteil', 'Rue Baratte Cholet'],
        ],
        [
            'codePostal' => '94500',
            'ville' => 'Champigny-sur-Marne',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.8172000',
            'longitude' => '2.5150000',
            'mapboxId' => 'fixture-idf-champigny-sur-marne-94500',
            'streets' => ['Rue Louis Talamoni', 'Avenue Roger Salengro', 'Boulevard de Stalingrad'],
        ],
        [
            'codePostal' => '94200',
            'ville' => 'Ivry-sur-Seine',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.8131000',
            'longitude' => '2.3882000',
            'mapboxId' => 'fixture-idf-ivry-sur-seine-94200',
            'streets' => ['Avenue Georges Gosnat', 'Rue Marat', 'Avenue Danielle Casanova'],
        ],
        [
            'codePostal' => '94300',
            'ville' => 'Vincennes',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.8478000',
            'longitude' => '2.4392000',
            'mapboxId' => 'fixture-idf-vincennes-94300',
            'streets' => ['Rue de Montreuil', 'Avenue de Paris', 'Rue Raymond du Temple'],
        ],
        [
            'codePostal' => '94130',
            'ville' => 'Nogent-sur-Marne',
            'department' => 'Val-de-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.8367000',
            'longitude' => '2.4825000',
            'mapboxId' => 'fixture-idf-nogent-sur-marne-94130',
            'streets' => ['Grande Rue Charles de Gaulle', 'Boulevard de Strasbourg', 'Rue Jacques Kablé'],
        ],

        [
            'codePostal' => '78000',
            'ville' => 'Versailles',
            'department' => 'Yvelines',
            'neighborhood' => 'Notre-Dame',
            'latitude' => '48.8049000',
            'longitude' => '2.1204000',
            'mapboxId' => 'fixture-idf-versailles-78000',
            'streets' => ['Avenue de Paris', 'Rue de la Paroisse', 'Boulevard de la Reine'],
        ],
        [
            'codePostal' => '78100',
            'ville' => 'Saint-Germain-en-Laye',
            'department' => 'Yvelines',
            'neighborhood' => 'Centre',
            'latitude' => '48.8964000',
            'longitude' => '2.0904000',
            'mapboxId' => 'fixture-idf-saint-germain-en-laye-78100',
            'streets' => ['Rue de Paris', 'Rue au Pain', 'Avenue Foch'],
        ],
        [
            'codePostal' => '78200',
            'ville' => 'Mantes-la-Jolie',
            'department' => 'Yvelines',
            'neighborhood' => 'Centre',
            'latitude' => '48.9905000',
            'longitude' => '1.7169000',
            'mapboxId' => 'fixture-idf-mantes-la-jolie-78200',
            'streets' => ['Rue Nationale', 'Boulevard Victor Duhamel', 'Rue Porte aux Saints'],
        ],
        [
            'codePostal' => '78300',
            'ville' => 'Poissy',
            'department' => 'Yvelines',
            'neighborhood' => 'Centre',
            'latitude' => '48.9290000',
            'longitude' => '2.0490000',
            'mapboxId' => 'fixture-idf-poissy-78300',
            'streets' => ['Rue du Général de Gaulle', 'Avenue Maurice Berteaux', 'Boulevard Robespierre'],
        ],
        [
            'codePostal' => '78500',
            'ville' => 'Sartrouville',
            'department' => 'Yvelines',
            'neighborhood' => 'Centre',
            'latitude' => '48.9482000',
            'longitude' => '2.1917000',
            'mapboxId' => 'fixture-idf-sartrouville-78500',
            'streets' => ['Avenue Jean Jaurès', 'Rue Léon Jouhaux', 'Boulevard de Bezons'],
        ],

        [
            'codePostal' => '91000',
            'ville' => 'Évry-Courcouronnes',
            'department' => 'Essonne',
            'neighborhood' => 'Centre',
            'latitude' => '48.6238000',
            'longitude' => '2.4293000',
            'mapboxId' => 'fixture-idf-evry-courcouronnes-91000',
            'streets' => ['Boulevard des Coquibus', 'Cours Blaise Pascal', 'Avenue de la République'],
        ],
        [
            'codePostal' => '91300',
            'ville' => 'Massy',
            'department' => 'Essonne',
            'neighborhood' => 'Atlantis',
            'latitude' => '48.7309000',
            'longitude' => '2.2713000',
            'mapboxId' => 'fixture-idf-massy-91300',
            'streets' => ['Avenue Carnot', 'Rue de Paris', 'Avenue Raymond Aron'],
        ],
        [
            'codePostal' => '91120',
            'ville' => 'Palaiseau',
            'department' => 'Essonne',
            'neighborhood' => 'Centre',
            'latitude' => '48.7145000',
            'longitude' => '2.2469000',
            'mapboxId' => 'fixture-idf-palaiseau-91120',
            'streets' => ['Rue de Paris', 'Avenue du 8 Mai 1945', 'Rue Lazare Carnot'],
        ],
        [
            'codePostal' => '91100',
            'ville' => 'Corbeil-Essonnes',
            'department' => 'Essonne',
            'neighborhood' => 'Centre',
            'latitude' => '48.6138000',
            'longitude' => '2.4820000',
            'mapboxId' => 'fixture-idf-corbeil-essonnes-91100',
            'streets' => ['Boulevard Jean Jaurès', 'Rue Saint-Spire', 'Avenue Darblay'],
        ],
        [
            'codePostal' => '91600',
            'ville' => 'Savigny-sur-Orge',
            'department' => 'Essonne',
            'neighborhood' => 'Centre',
            'latitude' => '48.6760000',
            'longitude' => '2.3486000',
            'mapboxId' => 'fixture-idf-savigny-sur-orge-91600',
            'streets' => ['Boulevard Aristide Briand', 'Rue Charles Rossignol', 'Avenue Gabriel Péri'],
        ],

        [
            'codePostal' => '77000',
            'ville' => 'Melun',
            'department' => 'Seine-et-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.5399000',
            'longitude' => '2.6608000',
            'mapboxId' => 'fixture-idf-melun-77000',
            'streets' => ['Rue Saint-Aspais', 'Boulevard Gambetta', 'Avenue Thiers'],
        ],
        [
            'codePostal' => '77100',
            'ville' => 'Meaux',
            'department' => 'Seine-et-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.9603000',
            'longitude' => '2.8883000',
            'mapboxId' => 'fixture-idf-meaux-77100',
            'streets' => ['Rue du Général Leclerc', 'Avenue Salvador Allende', 'Cours Raoult'],
        ],
        [
            'codePostal' => '77500',
            'ville' => 'Chelles',
            'department' => 'Seine-et-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.8833000',
            'longitude' => '2.6000000',
            'mapboxId' => 'fixture-idf-chelles-77500',
            'streets' => ['Avenue de la Résistance', 'Rue Gambetta', 'Boulevard Chilpéric'],
        ],
        [
            'codePostal' => '77300',
            'ville' => 'Fontainebleau',
            'department' => 'Seine-et-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.4047000',
            'longitude' => '2.7016000',
            'mapboxId' => 'fixture-idf-fontainebleau-77300',
            'streets' => ['Rue Grande', 'Boulevard Magenta', 'Rue de France'],
        ],
        [
            'codePostal' => '77200',
            'ville' => 'Torcy',
            'department' => 'Seine-et-Marne',
            'neighborhood' => 'Centre',
            'latitude' => '48.8502000',
            'longitude' => '2.6508000',
            'mapboxId' => 'fixture-idf-torcy-77200',
            'streets' => ['Avenue Jean Moulin', 'Rue de Paris', 'Promenade du Belvédère'],
        ],

        [
            'codePostal' => '95100',
            'ville' => 'Argenteuil',
            'department' => 'Val-d’Oise',
            'neighborhood' => 'Centre',
            'latitude' => '48.9472000',
            'longitude' => '2.2467000',
            'mapboxId' => 'fixture-idf-argenteuil-95100',
            'streets' => ['Boulevard Héloïse', 'Avenue Gabriel Péri', 'Rue Paul Vaillant-Couturier'],
        ],
        [
            'codePostal' => '95000',
            'ville' => 'Cergy',
            'department' => 'Val-d’Oise',
            'neighborhood' => 'Préfecture',
            'latitude' => '49.0365000',
            'longitude' => '2.0761000',
            'mapboxId' => 'fixture-idf-cergy-95000',
            'streets' => ['Boulevard de l’Oise', 'Avenue Bernard Hirsch', 'Rue des Chauffours'],
        ],
        [
            'codePostal' => '95300',
            'ville' => 'Pontoise',
            'department' => 'Val-d’Oise',
            'neighborhood' => 'Centre',
            'latitude' => '49.0500000',
            'longitude' => '2.1000000',
            'mapboxId' => 'fixture-idf-pontoise-95300',
            'streets' => ['Rue de l’Hôtel de Ville', 'Rue de Gisors', 'Boulevard Jean Jaurès'],
        ],
        [
            'codePostal' => '95200',
            'ville' => 'Sarcelles',
            'department' => 'Val-d’Oise',
            'neighborhood' => 'Village',
            'latitude' => '48.9974000',
            'longitude' => '2.3787000',
            'mapboxId' => 'fixture-idf-sarcelles-95200',
            'streets' => ['Avenue Paul Valéry', 'Boulevard Henri Poincaré', 'Rue Pierre Brossolette'],
        ],
        [
            'codePostal' => '95140',
            'ville' => 'Garges-lès-Gonesse',
            'department' => 'Val-d’Oise',
            'neighborhood' => 'Centre',
            'latitude' => '48.9719000',
            'longitude' => '2.3980000',
            'mapboxId' => 'fixture-idf-garges-les-gonesse-95140',
            'streets' => ['Avenue de Stalingrad', 'Rue Jean Goujon', 'Boulevard de la Muette'],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
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

        $propertyReferenceIndex = 1;

        foreach (self::LOCATIONS as $location) {
            $numberOfPropertiesForLocation = $faker->numberBetween(
                self::MIN_PROPERTIES_PER_LOCATION,
                self::MAX_PROPERTIES_PER_LOCATION
            );

            for ($i = 1; $i <= $numberOfPropertiesForLocation; ++$i) {
                $propertyData = $faker->randomElement(self::PROPERTIES);

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

                $slug = $this->generateUniqueNumericSlug($faker, $usedSlugs);

                $property = new Property();

                $property
                    ->setUser($user)
                    ->setTypeBien($categoryBien)
                    ->setTypeTransaction($categoryBienTransaction)
                    ->setCodePostal($location['codePostal'])
                    ->setLatitude($randomGps['latitude'])
                    ->setLongitude($randomGps['longitude'])
                    ->setMapboxId(sprintf('%s-%06d', $location['mapboxId'], $propertyReferenceIndex))
                    ->setFeatureType('address')
                    ->setShowAdresse((bool) $faker->numberBetween(0, 1))
                    ->setAnneeConstruction((string) $faker->numberBetween(1950, 2025))
                    ->setChambres((string) $faker->numberBetween(1, 8))
                    ->setSalleDeBains((string) $faker->numberBetween(1, 4))
                    ->setSurfaceTotal((string) $faker->numberBetween(25, 450))
                    ->setDpe((string) $faker->numberBetween(50, 350))
                    ->setDpeLettre($faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G']))
                    ->setGes((string) $faker->numberBetween(5, 80))
                    ->setGesLettre($faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G']))
                    ->setDpeMin((string) $faker->numberBetween(400, 900))
                    ->setDpeMax((string) $faker->numberBetween(901, 2200))
                    ->setDateIndexationEnergie(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years', 'now')))
                    ->setPrix((string) $faker->numberBetween(90000, 2500000))
                    ->setReferenceInterne('BOOLTS-'.$faker->unique()->numberBetween(100000, 999999))
                    ->setMontantLoyerHorsCharge((string) $faker->numberBetween(500, 6500))
                    ->setMontantDepotDeGarantie((string) $faker->numberBetween(500, 12000))
                    ->setMontantDesCharges((string) $faker->numberBetween(50, 900))
                    ->setStatut($faker->randomElement([
                        StatutAnnonceImmobiliere::PUBLIEE,
                        StatutAnnonceImmobiliere::DISPONIBLE,
                        StatutAnnonceImmobiliere::SOUS_OFFRE,
                        StatutAnnonceImmobiliere::OFFRE_ACCEPTEE,
                        StatutAnnonceImmobiliere::RESERVEE,
                        StatutAnnonceImmobiliere::DOSSIER_EN_COURS,
                    ]))
                    ->setSlug($slug);

                $this->fillTranslation($property, 'fr', $localizedAddress['fr'], $propertyData['typeBien']);
                $this->fillTranslation($property, 'en', $localizedAddress['en'], $propertyData['typeBien']);

                if (method_exists($property, 'setCreatedAt')) {
                    $property->setCreatedAt(
                        \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now'))
                    );
                }

                if (method_exists($property, 'setUpdatedAt')) {
                    $property->setUpdatedAt(new \DateTimeImmutable());
                }

                if ([] !== $caracteristiques) {
                    $maxCaracteristiques = min(8, \count($caracteristiques));
                    $numberOfCaracteristiques = $faker->numberBetween(1, $maxCaracteristiques);

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

    private function buildUniqueLocalizedAddress(
        \Faker\Generator $faker,
        array $location,
        array &$usedFullAddresses
    ): array {
        $attempts = 0;

        do {
            ++$attempts;

            $street = $faker->randomElement($location['streets']);
            $streetNumber = $faker->numberBetween(1, 220);

            $adresse = $streetNumber.' '.$street;

            $fullAddress = sprintf(
                '%s, %s %s, France',
                $adresse,
                $location['codePostal'],
                $location['ville']
            );

            $key = strtolower($fullAddress);
        } while (isset($usedFullAddresses[$key]) && $attempts < 200);

        if (isset($usedFullAddresses[$key])) {
            throw new \RuntimeException(sprintf(
                'Impossible de générer une adresse unique pour %s %s.',
                $location['codePostal'],
                $location['ville']
            ));
        }

        $usedFullAddresses[$key] = true;

        return [
            'fr' => [
                'adresse' => $adresse,
                'ville' => $location['ville'],
                'pays' => 'France',
                'region' => 'Île-de-France',
                'district' => $location['department'],
                'locality' => $location['ville'],
                'neighborhood' => $location['neighborhood'],
                'poi' => null,
                'fullAddress' => $fullAddress,
            ],
            'en' => [
                'adresse' => $adresse,
                'ville' => $location['ville'],
                'pays' => 'France',
                'region' => 'Île-de-France',
                'district' => $location['department'],
                'locality' => $location['ville'],
                'neighborhood' => $location['neighborhood'],
                'poi' => null,
                'fullAddress' => $fullAddress,
            ],
        ];
    }

    private function fillTranslation(Property $property, string $locale, array $address, string $typeBien): void
    {
        $typeLabelFr = [
            'maison' => 'maison',
            'appartement' => 'appartement',
            'villa' => 'villa',
            'fond-de-commerce' => 'fonds de commerce',
            'bureaux' => 'bureaux',
            'local-commercial' => 'local commercial',
            'terrain' => 'terrain',
            'ferme' => 'ferme',
            'parking-garage-box' => 'parking, garage ou box',
        ];

        $typeLabelEn = [
            'maison' => 'house',
            'appartement' => 'apartment',
            'villa' => 'villa',
            'fond-de-commerce' => 'business assets',
            'bureaux' => 'office space',
            'local-commercial' => 'commercial premises',
            'terrain' => 'land',
            'ferme' => 'farm',
            'parking-garage-box' => 'parking space, garage or box',
        ];

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
            $type = $typeLabelFr[$typeBien] ?? 'bien immobilier';

            $translation->setTitreDuLogement(
                ucfirst($type).' à '.$address['ville'].' - '.$address['neighborhood']
            );

            $translation->setDescriptionLogement(
                'Découvrez ce '.$type.' situé à '.$address['ville'].', dans le secteur '.$address['neighborhood'].'. '.
                'Ce bien bénéficie d’un emplacement recherché en Île-de-France, proche des commodités, des transports et des services essentiels. '.
                'Une opportunité idéale pour un projet immobilier local ou un investissement en région parisienne.'
            );

            return;
        }

        $type = $typeLabelEn[$typeBien] ?? 'property';

        $translation->setTitreDuLogement(
            ucfirst($type).' in '.$address['ville'].' - '.$address['neighborhood']
        );

        $translation->setDescriptionLogement(
            'Discover this '.$type.' located in '.$address['ville'].', in the '.$address['neighborhood'].' area. '.
            'This property benefits from a sought-after location in the Paris region, close to amenities, transport and essential services. '.
            'An ideal opportunity for a local real estate project or an investment in Île-de-France.'
        );
    }

    private function generateUniqueRandomGpsAround(
        float $latitude,
        float $longitude,
        float $radiusKm,
        \Faker\Generator $faker,
        array &$usedGps
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
        } while (isset($usedGps[$key]) && $attempts < 200);

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
        \Faker\Generator $faker
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

        $lon2 = fmod($lon2 + 3 * M_PI, 2 * M_PI) - M_PI;

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