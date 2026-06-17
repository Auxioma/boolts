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
use Faker\Generator;

class PropertyFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROPERTY_REFERENCE_PREFIX = 'property_';

    public const PROPERTY_COUNT = 1000;

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

    /**
     * Faker internationaux.
     *
     * Les coordonnées sont volontairement bornées par pays
     * pour éviter d’avoir une ville allemande avec une latitude au Brésil.
     */
    private const INTERNATIONAL_FAKER_PROFILES = [
        [
            'locale' => 'fr_FR',
            'pays' => 'France',
            'latMin' => 41.0000000,
            'latMax' => 51.0000000,
            'lngMin' => -5.0000000,
            'lngMax' => 9.0000000,
        ],
        [
            'locale' => 'en_US',
            'pays' => 'États-Unis',
            'latMin' => 25.0000000,
            'latMax' => 49.0000000,
            'lngMin' => -124.0000000,
            'lngMax' => -66.0000000,
        ],
        [
            'locale' => 'en_GB',
            'pays' => 'Royaume-Uni',
            'latMin' => 50.0000000,
            'latMax' => 58.0000000,
            'lngMin' => -6.0000000,
            'lngMax' => 1.8000000,
        ],
        [
            'locale' => 'de_DE',
            'pays' => 'Allemagne',
            'latMin' => 47.0000000,
            'latMax' => 55.0000000,
            'lngMin' => 5.0000000,
            'lngMax' => 15.0000000,
        ],
        [
            'locale' => 'es_ES',
            'pays' => 'Espagne',
            'latMin' => 36.0000000,
            'latMax' => 43.8000000,
            'lngMin' => -9.5000000,
            'lngMax' => 3.3000000,
        ],
        [
            'locale' => 'it_IT',
            'pays' => 'Italie',
            'latMin' => 37.0000000,
            'latMax' => 46.5000000,
            'lngMin' => 7.0000000,
            'lngMax' => 18.5000000,
        ],
        [
            'locale' => 'pt_PT',
            'pays' => 'Portugal',
            'latMin' => 37.0000000,
            'latMax' => 42.2000000,
            'lngMin' => -9.6000000,
            'lngMax' => -6.0000000,
        ],
        [
            'locale' => 'nl_NL',
            'pays' => 'Pays-Bas',
            'latMin' => 51.0000000,
            'latMax' => 53.7000000,
            'lngMin' => 3.0000000,
            'lngMax' => 7.3000000,
        ],
        [
            'locale' => 'pl_PL',
            'pays' => 'Pologne',
            'latMin' => 49.0000000,
            'latMax' => 54.9000000,
            'lngMin' => 14.0000000,
            'lngMax' => 24.2000000,
        ],
        [
            'locale' => 'ro_RO',
            'pays' => 'Roumanie',
            'latMin' => 43.5000000,
            'latMax' => 48.5000000,
            'lngMin' => 20.0000000,
            'lngMax' => 29.8000000,
        ],
        [
            'locale' => 'el_GR',
            'pays' => 'Grèce',
            'latMin' => 35.0000000,
            'latMax' => 41.8000000,
            'lngMin' => 19.0000000,
            'lngMax' => 28.3000000,
        ],
        [
            'locale' => 'ja_JP',
            'pays' => 'Japon',
            'latMin' => 31.0000000,
            'latMax' => 45.5000000,
            'lngMin' => 129.0000000,
            'lngMax' => 145.8000000,
        ],
        [
            'locale' => 'zh_CN',
            'pays' => 'Chine',
            'latMin' => 22.0000000,
            'latMax' => 41.0000000,
            'lngMin' => 100.0000000,
            'lngMax' => 122.5000000,
        ],
        [
            'locale' => 'en_AU',
            'pays' => 'Australie',
            'latMin' => -38.0000000,
            'latMax' => -12.0000000,
            'lngMin' => 113.0000000,
            'lngMax' => 153.0000000,
        ],
        [
            'locale' => 'en_CA',
            'pays' => 'Canada',
            'latMin' => 43.0000000,
            'latMax' => 60.0000000,
            'lngMin' => -123.0000000,
            'lngMax' => -52.0000000,
        ],
    ];

    /**
     * Cache des Faker par locale.
     *
     * @var array<string, Generator>
     */
    private array $fakerByLocale = [];

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

        /**
         * On prend les caractéristiques déjà existantes.
         */
        $caracteristiques = $manager
            ->getRepository(Caracteristique::class)
            ->findAll();

        /**
         * Protection des slugs uniques pendant le chargement.
         *
         * @var array<string, bool> $usedSlugs
         */
        $usedSlugs = [];

        for ($i = 1; $i <= self::PROPERTY_COUNT; ++$i) {
            $propertyData = $faker->randomElement(self::PROPERTIES);

            $typeBien = $propertyData['typeBien'];
            $typeTransaction = $propertyData['typeTransaction'];

            $location = $this->generateInternationalLocation($faker);

            /** @var User $user */
            $user = $this->getReference(
                $faker->randomElement($agenceReferences),
                User::class
            );

            /** @var CategoryBien $categoryBien */
            $categoryBien = $this->getReference(
                CategoryBienFixtures::CATEGORY_BIEN_REFERENCE_PREFIX.$typeBien,
                CategoryBien::class
            );

            /** @var CategoryBienTransaction $categoryBienTransaction */
            $categoryBienTransaction = $this->getReference(
                CategoryBienTransactionFixtures::CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX.$typeTransaction,
                CategoryBienTransaction::class
            );

            $createdAtMutable = $faker->dateTimeBetween('-1 year', 'now');
            $updatedAtMutable = $faker->dateTimeBetween($createdAtMutable, 'now');
            $dateIndexationEnergieMutable = $faker->dateTimeBetween('-3 years', 'now');

            $createdAt = \DateTimeImmutable::createFromMutable($createdAtMutable);
            $updatedAt = \DateTimeImmutable::createFromMutable($updatedAtMutable);
            $dateIndexationEnergie = \DateTimeImmutable::createFromMutable($dateIndexationEnergieMutable);

            $stats = $this->generatePropertyStats($typeBien, $faker);
            $energy = $this->generateEnergyData($faker);
            $finance = $this->generateFinanceData($typeBien, $typeTransaction, $faker);
            $slug = $this->generateUniqueNumericSlug($faker, $usedSlugs);

            $property = new Property();

            $property
                ->setUser($user)
                ->setTypeBien($categoryBien)
                ->setTypeTransaction($categoryBienTransaction)

                /*
                 * Adresse internationale générée avec Faker.
                 */
                ->setAdresse($location['adresse'])
                ->setCodePostal($location['codePostal'])
                ->setVille($location['ville'])
                ->setPays($location['pays'])
                ->setLatitude($location['latitude'])
                ->setLongitude($location['longitude'])

                /*
                 * Données Mapbox simulées.
                 */
                ->setMapboxId($this->generateMapboxId($location, $i))
                ->setFullAddress($this->generateFullAddress($location))
                ->setFeatureType('address')
                ->setRegion($location['region'])
                ->setDistrict($location['district'])
                ->setLocality($location['locality'])
                ->setNeighborhood($location['neighborhood'])
                ->setPoi($location['poi'])

                /*
                 * Caractéristiques principales.
                 */
                ->setAnneeConstruction((string) $faker->numberBetween(1900, 2026))
                ->setChambres((string) $stats['chambres'])
                ->setSalleDeBains((string) $stats['salleDeBains'])
                ->setSurfaceTotal((string) $stats['surfaceTotal'])

                /*
                 * Données énergie.
                 */
                ->setDpe((string) $energy['dpe'])
                ->setDpeLettre($energy['dpeLettre'])
                ->setGes((string) $energy['ges'])
                ->setGesLettre($energy['gesLettre'])
                ->setDpeMin((string) $energy['dpeMin'])
                ->setDpeMax((string) $energy['dpeMax'])
                ->setDateIndexationEnergie($dateIndexationEnergie)

                /*
                 * Titre + description.
                 */
                ->setTitreDuLogement(
                    $this->generateTitle(
                        $typeBien,
                        $typeTransaction,
                        $location['ville'],
                        $location['pays']
                    )
                )
                ->setDescriptionLogement(
                    $this->generateDescription(
                        $typeBien,
                        $typeTransaction,
                        $location,
                        $stats,
                        $faker
                    )
                )

                /*
                 * Logique financière :
                 * vente    => prix rempli, location null
                 * location => prix null, loyer + charges + dépôt remplis
                 */
                ->setPrix($finance['prix'])
                ->setMontantLoyerHorsCharge($finance['montantLoyerHorsCharge'])
                ->setMontantDepotDeGarantie($finance['montantDepotDeGarantie'])
                ->setMontantDesCharges($finance['montantDesCharges'])

                ->setReferenceInterne(\sprintf('PROPERTY-%04d', $i))
                ->setStatut($this->generateStatus($faker))
                ->setCreatedAt($createdAt)
                ->setUpdatedAt($updatedAt)
                ->setSlug($slug);

            $this->addRandomCaracteristiques(
                $property,
                $caracteristiques,
                $faker
            );

            $manager->persist($property);

            $this->addReference(
                self::PROPERTY_REFERENCE_PREFIX.$i,
                $property
            );
        }

        $manager->flush();
    }

    /**
     * Génère une adresse internationale avec un Faker adapté au pays.
     *
     * @return array{
     *     pays: string,
     *     ville: string,
     *     codePostal: string,
     *     adresse: string,
     *     region: string,
     *     district: string,
     *     locality: string,
     *     neighborhood: string,
     *     poi: string,
     *     latitude: string,
     *     longitude: string
     * }
     */
    private function generateInternationalLocation(Generator $mainFaker): array
    {
        $profile = $mainFaker->randomElement(self::INTERNATIONAL_FAKER_PROFILES);

        $faker = $this->getFakerByLocale($profile['locale']);

        $ville = $this->fakerValue($faker, 'city') ?? $mainFaker->city();
        $codePostal = $this->fakerValue($faker, 'postcode') ?? $mainFaker->postcode();
        $adresse = $this->fakerValue($faker, 'streetAddress') ?? $mainFaker->streetAddress();

        $region = $this->fakerValue($faker, 'state')
            ?? $this->fakerValue($faker, 'region')
            ?? $ville;

        $district = $this->fakerValue($faker, 'citySuffix')
            ?? $this->fakerValue($faker, 'secondaryAddress')
            ?? $ville;

        $neighborhood = $this->fakerValue($faker, 'streetName')
            ?? $this->fakerValue($faker, 'streetSuffix')
            ?? $ville;

        $poi = $this->fakerValue($faker, 'company')
            ?? $this->fakerValue($faker, 'catchPhrase')
            ?? 'Centre-ville';

        return [
            'pays' => $profile['pays'],
            'ville' => $ville,
            'codePostal' => $codePostal,
            'adresse' => $adresse,
            'region' => $region,
            'district' => $district,
            'locality' => $ville,
            'neighborhood' => $neighborhood,
            'poi' => $poi,
            'latitude' => $this->generateCoordinate($mainFaker, $profile['latMin'], $profile['latMax']),
            'longitude' => $this->generateCoordinate($mainFaker, $profile['lngMin'], $profile['lngMax']),
        ];
    }

    private function getFakerByLocale(string $locale): Generator
    {
        if (!isset($this->fakerByLocale[$locale])) {
            try {
                $this->fakerByLocale[$locale] = Factory::create($locale);
            } catch (\Throwable) {
                $this->fakerByLocale[$locale] = Factory::create('en_US');
            }
        }

        return $this->fakerByLocale[$locale];
    }

    private function fakerValue(Generator $faker, string $method): ?string
    {
        try {
            $value = $faker->{$method}();

            if (!\is_scalar($value)) {
                return null;
            }

            $value = mb_trim((string) $value);

            return '' !== $value ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateCoordinate(Generator $faker, float $min, float $max): string
    {
        return number_format(
            $faker->randomFloat(7, $min, $max),
            7,
            '.',
            ''
        );
    }

    private function generateTitle(
        string $typeBien,
        string $typeTransaction,
        string $ville,
        string $pays,
    ): string {
        $typeBienLabel = str_replace('-', ' ', $typeBien);

        $transactionLabel = match ($typeTransaction) {
            'vente' => 'à vendre',
            'location' => 'à louer',
            default => '',
        };

        return ucfirst($typeBienLabel).' '.$transactionLabel.' à '.$ville.', '.$pays;
    }

    /**
     * @param array<string, string>                                      $location
     * @param array{chambres: int, salleDeBains: int, surfaceTotal: int} $stats
     */
    private function generateDescription(
        string $typeBien,
        string $typeTransaction,
        array $location,
        array $stats,
        Generator $faker,
    ): string {
        $typeBienLabel = str_replace('-', ' ', $typeBien);

        $transactionLabel = match ($typeTransaction) {
            'vente' => 'proposé à la vente',
            'location' => 'proposé à la location',
            default => 'disponible',
        };

        $intro = \sprintf(
            'Ce %s est %s dans le secteur de %s, à %s, %s.',
            $typeBienLabel,
            $transactionLabel,
            $location['neighborhood'],
            $location['ville'],
            $location['pays']
        );

        $details = \sprintf(
            'Le bien dispose d’une surface totale de %d m², avec %d chambre(s) et %d salle(s) de bains.',
            $stats['surfaceTotal'],
            $stats['chambres'],
            $stats['salleDeBains']
        );

        $environment = \sprintf(
            'L’adresse bénéficie d’un environnement recherché, proche de %s, avec un accès pratique aux commerces, transports et services du quartier.',
            $location['poi']
        );

        return $intro."\n\n".$details."\n\n".$environment."\n\n".$faker->paragraphs(2, true);
    }

    /**
     * Génère des données cohérentes selon le type de bien.
     *
     * @return array{chambres: int, salleDeBains: int, surfaceTotal: int}
     */
    private function generatePropertyStats(string $typeBien, Generator $faker): array
    {
        return match ($typeBien) {
            'appartement' => [
                'chambres' => $faker->numberBetween(0, 5),
                'salleDeBains' => $faker->numberBetween(1, 3),
                'surfaceTotal' => $faker->numberBetween(25, 180),
            ],

            'maison' => [
                'chambres' => $faker->numberBetween(2, 7),
                'salleDeBains' => $faker->numberBetween(1, 4),
                'surfaceTotal' => $faker->numberBetween(80, 350),
            ],

            'villa' => [
                'chambres' => $faker->numberBetween(3, 10),
                'salleDeBains' => $faker->numberBetween(2, 8),
                'surfaceTotal' => $faker->numberBetween(180, 900),
            ],

            'ferme' => [
                'chambres' => $faker->numberBetween(2, 8),
                'salleDeBains' => $faker->numberBetween(1, 4),
                'surfaceTotal' => $faker->numberBetween(150, 1200),
            ],

            'terrain' => [
                'chambres' => 0,
                'salleDeBains' => 0,
                'surfaceTotal' => $faker->numberBetween(300, 5000),
            ],

            'parking-garage-box' => [
                'chambres' => 0,
                'salleDeBains' => 0,
                'surfaceTotal' => $faker->numberBetween(10, 40),
            ],

            'bureaux' => [
                'chambres' => 0,
                'salleDeBains' => $faker->numberBetween(1, 4),
                'surfaceTotal' => $faker->numberBetween(40, 800),
            ],

            'local-commercial' => [
                'chambres' => 0,
                'salleDeBains' => $faker->numberBetween(1, 3),
                'surfaceTotal' => $faker->numberBetween(30, 600),
            ],

            'fond-de-commerce' => [
                'chambres' => 0,
                'salleDeBains' => $faker->numberBetween(1, 4),
                'surfaceTotal' => $faker->numberBetween(50, 1000),
            ],

            default => [
                'chambres' => $faker->numberBetween(0, 5),
                'salleDeBains' => $faker->numberBetween(0, 3),
                'surfaceTotal' => $faker->numberBetween(20, 500),
            ],
        };
    }

    /**
     * Vente :
     * - prix rempli
     * - location à null
     *
     * Location :
     * - prix à null
     * - loyer hors charge rempli
     * - dépôt de garantie rempli
     * - charges remplies
     *
     * @return array{
     *     prix: ?string,
     *     montantLoyerHorsCharge: ?string,
     *     montantDepotDeGarantie: ?string,
     *     montantDesCharges: ?string
     * }
     */
    private function generateFinanceData(
        string $typeBien,
        string $typeTransaction,
        Generator $faker,
    ): array {
        if ('vente' === $typeTransaction) {
            return [
                'prix' => (string) $this->generateSalePrice($typeBien, $faker),
                'montantLoyerHorsCharge' => null,
                'montantDepotDeGarantie' => null,
                'montantDesCharges' => null,
            ];
        }

        $loyerHorsCharge = $this->generateRentPrice($typeBien, $faker);
        $charges = (int) round($loyerHorsCharge * $faker->numberBetween(5, 20) / 100);
        $depotGarantie = $loyerHorsCharge * $faker->numberBetween(1, 3);

        return [
            'prix' => null,
            'montantLoyerHorsCharge' => (string) $loyerHorsCharge,
            'montantDepotDeGarantie' => (string) $depotGarantie,
            'montantDesCharges' => (string) $charges,
        ];
    }

    private function generateSalePrice(string $typeBien, Generator $faker): int
    {
        return match ($typeBien) {
            'parking-garage-box' => $faker->numberBetween(8000, 85000),
            'terrain' => $faker->numberBetween(30000, 900000),
            'appartement' => $faker->numberBetween(90000, 1500000),
            'maison' => $faker->numberBetween(150000, 2500000),
            'villa' => $faker->numberBetween(500000, 6500000),
            'ferme' => $faker->numberBetween(120000, 1800000),
            'bureaux' => $faker->numberBetween(180000, 3500000),
            'local-commercial' => $faker->numberBetween(120000, 2500000),
            'fond-de-commerce' => $faker->numberBetween(50000, 1200000),
            default => $faker->numberBetween(80000, 1500000),
        };
    }

    private function generateRentPrice(string $typeBien, Generator $faker): int
    {
        return match ($typeBien) {
            'parking-garage-box' => $faker->numberBetween(50, 450),
            'appartement' => $faker->numberBetween(450, 5500),
            'maison' => $faker->numberBetween(900, 7500),
            'villa' => $faker->numberBetween(2500, 25000),
            'bureaux' => $faker->numberBetween(700, 18000),
            'local-commercial' => $faker->numberBetween(800, 22000),
            'fond-de-commerce' => $faker->numberBetween(1200, 30000),
            default => $faker->numberBetween(400, 8000),
        };
    }

    /**
     * @return array{
     *     dpe: int,
     *     dpeLettre: string,
     *     ges: int,
     *     gesLettre: string,
     *     dpeMin: int,
     *     dpeMax: int
     * }
     */
    private function generateEnergyData(Generator $faker): array
    {
        $dpeLettre = $faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G']);

        [$dpeMin, $dpeMax] = match ($dpeLettre) {
            'A' => [10, 69],
            'B' => [70, 109],
            'C' => [110, 179],
            'D' => [180, 249],
            'E' => [250, 329],
            'F' => [330, 419],
            'G' => [420, 650],
        };

        $dpe = $faker->numberBetween($dpeMin, $dpeMax);

        $gesLettre = $faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G']);

        $ges = match ($gesLettre) {
            'A' => $faker->numberBetween(1, 5),
            'B' => $faker->numberBetween(6, 10),
            'C' => $faker->numberBetween(11, 20),
            'D' => $faker->numberBetween(21, 35),
            'E' => $faker->numberBetween(36, 55),
            'F' => $faker->numberBetween(56, 80),
            'G' => $faker->numberBetween(81, 120),
        };

        return [
            'dpe' => $dpe,
            'dpeLettre' => $dpeLettre,
            'ges' => $ges,
            'gesLettre' => $gesLettre,
            'dpeMin' => $dpeMin,
            'dpeMax' => $dpeMax,
        ];
    }

    /**
     * @param array<string, string> $location
     */
    private function generateFullAddress(array $location): string
    {
        return \sprintf(
            '%s, %s %s, %s',
            $location['adresse'],
            $location['codePostal'],
            $location['ville'],
            $location['pays']
        );
    }

    /**
     * @param array<string, string> $location
     */
    private function generateMapboxId(array $location, int $index): string
    {
        $countrySlug = mb_strtolower(
            preg_replace('/[^a-z0-9]+/i', '-', $location['pays']) ?? 'country'
        );

        $citySlug = mb_strtolower(
            preg_replace('/[^a-z0-9]+/i', '-', $location['ville']) ?? 'city'
        );

        return \sprintf(
            'address.%s.%s.%d',
            mb_trim($countrySlug, '-'),
            mb_trim($citySlug, '-'),
            $index
        );
    }

    private function generateStatus(Generator $faker): StatutAnnonceImmobiliere
    {
        return $faker->randomElement([
            StatutAnnonceImmobiliere::PUBLIEE,
            StatutAnnonceImmobiliere::PUBLIEE,
            StatutAnnonceImmobiliere::PUBLIEE,
            StatutAnnonceImmobiliere::VALIDATE,
            StatutAnnonceImmobiliere::PENDING,
            StatutAnnonceImmobiliere::BROUILLON,
        ]);
    }

    /**
     * Slug numérique unique.
     *
     * @param array<string, bool> $usedSlugs
     */
    private function generateUniqueNumericSlug(Generator $faker, array &$usedSlugs): string
    {
        do {
            $slug = (string) $faker->numberBetween(1000000000, 9999999999);
        } while (isset($usedSlugs[$slug]));

        $usedSlugs[$slug] = true;

        return $slug;
    }

    /**
     * @param Caracteristique[] $caracteristiques
     */
    private function addRandomCaracteristiques(
        Property $property,
        array $caracteristiques,
        Generator $faker,
    ): void {
        if ([] === $caracteristiques) {
            return;
        }

        $numberOfCaracteristiques = $faker->numberBetween(
            0,
            min(6, \count($caracteristiques))
        );

        if (0 === $numberOfCaracteristiques) {
            return;
        }

        $randomCaracteristiques = $faker->randomElements(
            $caracteristiques,
            $numberOfCaracteristiques
        );

        foreach ($randomCaracteristiques as $caracteristique) {
            $property->addCaracteristique($caracteristique);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryBienFixtures::class,
            CategoryBienTransactionFixtures::class,
        ];
    }
}
