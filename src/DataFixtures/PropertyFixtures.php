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
    public const PROPERTY_COUNT = 100;

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

    private const ADDRESSES = [
        [
            'codePostal' => '75018',
            'latitude' => '48.8867040',
            'longitude' => '2.3404520',
            'mapboxId' => 'fixture-fr-paris-75018',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '12 Rue Lepic',
                'ville' => 'Paris',
                'pays' => 'France',
                'region' => 'Île-de-France',
                'district' => 'Paris',
                'locality' => 'Paris',
                'neighborhood' => 'Montmartre',
                'poi' => null,
                'fullAddress' => '12 Rue Lepic, 75018 Paris, France',
            ],
            'en' => [
                'adresse' => '12 Lepic Street',
                'ville' => 'Paris',
                'pays' => 'France',
                'region' => 'Île-de-France',
                'district' => 'Paris',
                'locality' => 'Paris',
                'neighborhood' => 'Montmartre',
                'poi' => null,
                'fullAddress' => '12 Lepic Street, 75018 Paris, France',
            ],
        ],
        [
            'codePostal' => '69002',
            'latitude' => '45.7612200',
            'longitude' => '4.8356100',
            'mapboxId' => 'fixture-fr-lyon-69002',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '25 Rue de la République',
                'ville' => 'Lyon',
                'pays' => 'France',
                'region' => 'Auvergne-Rhône-Alpes',
                'district' => 'Rhône',
                'locality' => 'Lyon',
                'neighborhood' => 'Presqu’île',
                'poi' => null,
                'fullAddress' => '25 Rue de la République, 69002 Lyon, France',
            ],
            'en' => [
                'adresse' => '25 Republic Street',
                'ville' => 'Lyon',
                'pays' => 'France',
                'region' => 'Auvergne-Rhône-Alpes',
                'district' => 'Rhône',
                'locality' => 'Lyon',
                'neighborhood' => 'Presqu’île',
                'poi' => null,
                'fullAddress' => '25 Republic Street, 69002 Lyon, France',
            ],
        ],
        [
            'codePostal' => '13007',
            'latitude' => '43.2849200',
            'longitude' => '5.3516900',
            'mapboxId' => 'fixture-fr-marseille-13007',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '45 Corniche Président John Fitzgerald Kennedy',
                'ville' => 'Marseille',
                'pays' => 'France',
                'region' => 'Provence-Alpes-Côte d’Azur',
                'district' => 'Bouches-du-Rhône',
                'locality' => 'Marseille',
                'neighborhood' => 'Endoume',
                'poi' => null,
                'fullAddress' => '45 Corniche Président John Fitzgerald Kennedy, 13007 Marseille, France',
            ],
            'en' => [
                'adresse' => '45 President John Fitzgerald Kennedy Corniche',
                'ville' => 'Marseille',
                'pays' => 'France',
                'region' => 'Provence-Alpes-Côte d’Azur',
                'district' => 'Bouches-du-Rhône',
                'locality' => 'Marseille',
                'neighborhood' => 'Endoume',
                'poi' => null,
                'fullAddress' => '45 President John Fitzgerald Kennedy Corniche, 13007 Marseille, France',
            ],
        ],
        [
            'codePostal' => '10001',
            'latitude' => '40.7484400',
            'longitude' => '-73.9856640',
            'mapboxId' => 'fixture-us-new-york-10001',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '350 5e Avenue',
                'ville' => 'New York',
                'pays' => 'États-Unis',
                'region' => 'État de New York',
                'district' => 'Comté de New York',
                'locality' => 'New York',
                'neighborhood' => 'Manhattan',
                'poi' => 'Empire State Building',
                'fullAddress' => '350 5e Avenue, New York, NY 10001, États-Unis',
            ],
            'en' => [
                'adresse' => '350 5th Avenue',
                'ville' => 'New York',
                'pays' => 'United States',
                'region' => 'New York',
                'district' => 'New York County',
                'locality' => 'New York',
                'neighborhood' => 'Manhattan',
                'poi' => 'Empire State Building',
                'fullAddress' => '350 5th Avenue, New York, NY 10001, United States',
            ],
        ],
        [
            'codePostal' => '90028',
            'latitude' => '34.1022340',
            'longitude' => '-118.3409650',
            'mapboxId' => 'fixture-us-los-angeles-90028',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '6801 Boulevard Hollywood',
                'ville' => 'Los Angeles',
                'pays' => 'États-Unis',
                'region' => 'Californie',
                'district' => 'Comté de Los Angeles',
                'locality' => 'Los Angeles',
                'neighborhood' => 'Hollywood',
                'poi' => null,
                'fullAddress' => '6801 Boulevard Hollywood, Los Angeles, CA 90028, États-Unis',
            ],
            'en' => [
                'adresse' => '6801 Hollywood Boulevard',
                'ville' => 'Los Angeles',
                'pays' => 'United States',
                'region' => 'California',
                'district' => 'Los Angeles County',
                'locality' => 'Los Angeles',
                'neighborhood' => 'Hollywood',
                'poi' => null,
                'fullAddress' => '6801 Hollywood Boulevard, Los Angeles, CA 90028, United States',
            ],
        ],
        [
            'codePostal' => '33139',
            'latitude' => '25.7906540',
            'longitude' => '-80.1300450',
            'mapboxId' => 'fixture-us-miami-33139',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '100 Ocean Drive',
                'ville' => 'Miami Beach',
                'pays' => 'États-Unis',
                'region' => 'Floride',
                'district' => 'Comté de Miami-Dade',
                'locality' => 'Miami Beach',
                'neighborhood' => 'South Beach',
                'poi' => null,
                'fullAddress' => '100 Ocean Drive, Miami Beach, FL 33139, États-Unis',
            ],
            'en' => [
                'adresse' => '100 Ocean Drive',
                'ville' => 'Miami Beach',
                'pays' => 'United States',
                'region' => 'Florida',
                'district' => 'Miami-Dade County',
                'locality' => 'Miami Beach',
                'neighborhood' => 'South Beach',
                'poi' => null,
                'fullAddress' => '100 Ocean Drive, Miami Beach, FL 33139, United States',
            ],
        ],
        [
            'codePostal' => 'H2Y 1C6',
            'latitude' => '45.5045800',
            'longitude' => '-73.5567900',
            'mapboxId' => 'fixture-ca-montreal-h2y1c6',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '110 Rue Notre-Dame Ouest',
                'ville' => 'Montréal',
                'pays' => 'Canada',
                'region' => 'Québec',
                'district' => 'Montréal',
                'locality' => 'Montréal',
                'neighborhood' => 'Vieux-Montréal',
                'poi' => null,
                'fullAddress' => '110 Rue Notre-Dame Ouest, Montréal, QC H2Y 1C6, Canada',
            ],
            'en' => [
                'adresse' => '110 Notre-Dame Street West',
                'ville' => 'Montreal',
                'pays' => 'Canada',
                'region' => 'Quebec',
                'district' => 'Montreal',
                'locality' => 'Montreal',
                'neighborhood' => 'Old Montreal',
                'poi' => null,
                'fullAddress' => '110 Notre-Dame Street West, Montreal, QC H2Y 1C6, Canada',
            ],
        ],
        [
            'codePostal' => 'M5V 2T6',
            'latitude' => '43.6425660',
            'longitude' => '-79.3870570',
            'mapboxId' => 'fixture-ca-toronto-m5v2t6',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '301 Rue Front Ouest',
                'ville' => 'Toronto',
                'pays' => 'Canada',
                'region' => 'Ontario',
                'district' => 'Toronto',
                'locality' => 'Toronto',
                'neighborhood' => 'Centre-ville de Toronto',
                'poi' => 'Tour CN',
                'fullAddress' => '301 Rue Front Ouest, Toronto, ON M5V 2T6, Canada',
            ],
            'en' => [
                'adresse' => '301 Front Street West',
                'ville' => 'Toronto',
                'pays' => 'Canada',
                'region' => 'Ontario',
                'district' => 'Toronto',
                'locality' => 'Toronto',
                'neighborhood' => 'Downtown Toronto',
                'poi' => 'CN Tower',
                'fullAddress' => '301 Front Street West, Toronto, ON M5V 2T6, Canada',
            ],
        ],
        [
            'codePostal' => 'V6B 2W9',
            'latitude' => '49.2827290',
            'longitude' => '-123.1207380',
            'mapboxId' => 'fixture-ca-vancouver-v6b2w9',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '800 Rue Robson',
                'ville' => 'Vancouver',
                'pays' => 'Canada',
                'region' => 'Colombie-Britannique',
                'district' => 'Vancouver',
                'locality' => 'Vancouver',
                'neighborhood' => 'Centre-ville de Vancouver',
                'poi' => null,
                'fullAddress' => '800 Rue Robson, Vancouver, BC V6B 2W9, Canada',
            ],
            'en' => [
                'adresse' => '800 Robson Street',
                'ville' => 'Vancouver',
                'pays' => 'Canada',
                'region' => 'British Columbia',
                'district' => 'Vancouver',
                'locality' => 'Vancouver',
                'neighborhood' => 'Downtown Vancouver',
                'poi' => null,
                'fullAddress' => '800 Robson Street, Vancouver, BC V6B 2W9, Canada',
            ],
        ],
        [
            'codePostal' => '2000',
            'latitude' => '-33.8599350',
            'longitude' => '151.2090290',
            'mapboxId' => 'fixture-au-sydney-2000',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '1 Rue Macquarie',
                'ville' => 'Sydney',
                'pays' => 'Australie',
                'region' => 'Nouvelle-Galles du Sud',
                'district' => 'Sydney',
                'locality' => 'Sydney',
                'neighborhood' => 'Centre-ville de Sydney',
                'poi' => null,
                'fullAddress' => '1 Rue Macquarie, Sydney NSW 2000, Australie',
            ],
            'en' => [
                'adresse' => '1 Macquarie Street',
                'ville' => 'Sydney',
                'pays' => 'Australia',
                'region' => 'New South Wales',
                'district' => 'Sydney',
                'locality' => 'Sydney',
                'neighborhood' => 'Sydney CBD',
                'poi' => null,
                'fullAddress' => '1 Macquarie Street, Sydney NSW 2000, Australia',
            ],
        ],
        [
            'codePostal' => '3000',
            'latitude' => '-37.8108500',
            'longitude' => '144.9631400',
            'mapboxId' => 'fixture-au-melbourne-3000',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '328 Rue Swanston',
                'ville' => 'Melbourne',
                'pays' => 'Australie',
                'region' => 'Victoria',
                'district' => 'Melbourne',
                'locality' => 'Melbourne',
                'neighborhood' => 'Centre-ville de Melbourne',
                'poi' => null,
                'fullAddress' => '328 Rue Swanston, Melbourne VIC 3000, Australie',
            ],
            'en' => [
                'adresse' => '328 Swanston Street',
                'ville' => 'Melbourne',
                'pays' => 'Australia',
                'region' => 'Victoria',
                'district' => 'Melbourne',
                'locality' => 'Melbourne',
                'neighborhood' => 'Melbourne CBD',
                'poi' => null,
                'fullAddress' => '328 Swanston Street, Melbourne VIC 3000, Australia',
            ],
        ],
        [
            'codePostal' => '4000',
            'latitude' => '-27.4697700',
            'longitude' => '153.0251310',
            'mapboxId' => 'fixture-au-brisbane-4000',
            'featureType' => 'address',
            'fr' => [
                'adresse' => '167 Rue Albert',
                'ville' => 'Brisbane',
                'pays' => 'Australie',
                'region' => 'Queensland',
                'district' => 'Brisbane',
                'locality' => 'Brisbane',
                'neighborhood' => 'Centre-ville de Brisbane',
                'poi' => null,
                'fullAddress' => '167 Rue Albert, Brisbane QLD 4000, Australie',
            ],
            'en' => [
                'adresse' => '167 Albert Street',
                'ville' => 'Brisbane',
                'pays' => 'Australia',
                'region' => 'Queensland',
                'district' => 'Brisbane',
                'locality' => 'Brisbane',
                'neighborhood' => 'Brisbane CBD',
                'poi' => null,
                'fullAddress' => '167 Albert Street, Brisbane QLD 4000, Australia',
            ],
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

        for ($i = 1; $i <= self::PROPERTY_COUNT; ++$i) {
            $propertyData = $faker->randomElement(self::PROPERTIES);
            $address = $faker->randomElement(self::ADDRESSES);

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
                ->setCodePostal($address['codePostal'])
                ->setLatitude($address['latitude'])
                ->setLongitude($address['longitude'])
                ->setMapboxId($address['mapboxId'])
                ->setFeatureType($address['featureType'])
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

            $this->fillTranslation($property, 'fr', $address['fr'], $propertyData['typeBien']);
            $this->fillTranslation($property, 'en', $address['en'], $propertyData['typeBien']);

            if (method_exists($property, 'setCreatedAt')) {
                $property->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', 'now')));
            }

            if (method_exists($property, 'setUpdatedAt')) {
                $property->setUpdatedAt(new \DateTimeImmutable());
            }

            if ([] !== $caracteristiques) {
                foreach ($faker->randomElements($caracteristiques, $faker->numberBetween(2, min(8, \count($caracteristiques)))) as $caracteristique) {
                    $property->addCaracteristique($caracteristique);
                }
            }

            $property->mergeNewTranslations();

            $manager->persist($property);

            $this->addReference(
                self::PROPERTY_REFERENCE_PREFIX.$i,
                $property
            );
        }

        $manager->flush();
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
                'Ce bien bénéficie d’un emplacement recherché, proche des commodités, des transports et des services essentiels. '.
                'Une opportunité idéale pour un projet immobilier local ou international.'
            );

            return;
        }

        $type = $typeLabelEn[$typeBien] ?? 'property';

        $translation->setTitreDuLogement(
            ucfirst($type).' in '.$address['ville'].' - '.$address['neighborhood']
        );

        $translation->setDescriptionLogement(
            'Discover this '.$type.' located in '.$address['ville'].', in the '.$address['neighborhood'].' area. '.
            'This property benefits from a sought-after location, close to amenities, transport and essential services. '.
            'An ideal opportunity for a local or international real estate project.'
        );
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