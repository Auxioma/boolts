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
use App\Entity\Enum\PerformanceEnergetique;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class PropertyFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROPERTY_REFERENCE_PREFIX = 'property_';

    private const NUMBER_OF_PROPERTIES = 1000;

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

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /**
         * On prend uniquement les caractéristiques déjà existantes.
         * Donc ici, je n’invente aucun slug ni aucune référence.
         */
        $caracteristiques = $manager
            ->getRepository(Caracteristique::class)
            ->findAll();

        for ($i = 1; $i <= self::NUMBER_OF_PROPERTIES; ++$i) {
            $propertyData = $faker->randomElement(self::PROPERTIES);

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

            $ville = $faker->city();

            $property = new Property();

            $property
                ->setTypeBien($categoryBien)
                ->setTypeTransaction($categoryBienTransaction)
                ->setTitreDuLogement(
                    $this->generateTitle(
                        $propertyData['typeBien'],
                        $propertyData['typeTransaction'],
                        $ville
                    )
                )
                ->setDescriptionLogement(
                    $faker->paragraphs(3, true)
                )
                ->setPrix(
                    $this->generatePrice(
                        $propertyData['typeBien'],
                        $propertyData['typeTransaction'],
                        $faker
                    )
                )
                ->setAnneeConstruction(
                    (string) $faker->numberBetween(1900, 2026)
                )
                ->setPerformanceEnergetique(
                    $faker->randomElement(PerformanceEnergetique::cases())
                )
                ->setAdresse($faker->streetAddress())
                ->setCodePostal($faker->postcode())
                ->setVille($ville)
                ->setPays('France')
                ->setReferenceInterne(
                    \sprintf('PROPERTY-%04d', $i)
                )
                ->setChambres(
                    (string) $faker->numberBetween(0, 8)
                )
                ->setSalleDeBains(
                    (string) $faker->numberBetween(0, 4)
                )
                ->setSurfaceTotal(
                    (string) $faker->numberBetween(10, 1000)
                )
                ->setStatut(
                    $faker->randomElement(StatutAnnonceImmobiliere::cases())
                );

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

    private function generateTitle(
        string $typeBien,
        string $typeTransaction,
        string $ville,
    ): string {
        $typeBienLabel = str_replace('-', ' ', $typeBien);

        $transactionLabel = match ($typeTransaction) {
            'vente' => 'à vendre',
            'location' => 'à louer',
            default => '',
        };

        return ucfirst($typeBienLabel).' '.$transactionLabel.' à '.$ville;
    }

    private function generatePrice(
        string $typeBien,
        string $typeTransaction,
        Generator $faker,
    ): string {
        if ('location' === $typeTransaction) {
            return (string) $faker->numberBetween(50, 10000);
        }

        return (string) $faker->numberBetween(8000, 3500000);
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
            CategoryBienFixtures::class,
            CategoryBienTransactionFixtures::class,
        ];
    }
}
