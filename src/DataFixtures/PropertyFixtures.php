<?php

namespace App\DataFixtures;

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Property;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PropertyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $properties = [
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

        for ($i = 1; $i <= 100; ++$i) {
            $propertyData = $faker->randomElement($properties);

            /** @var CategoryBien $categoryBien */
            $categoryBien = $this->getReference(
                CategoryBienFixtures::CATEGORY_BIEN_REFERENCE_PREFIX . $propertyData['typeBien'],
                CategoryBien::class
            );

            /** @var CategoryBienTransaction $categoryBienTransaction */
            $categoryBienTransaction = $this->getReference(
                CategoryBienTransactionFixtures::CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX . $propertyData['typeTransaction'],
                CategoryBienTransaction::class
            );

            $property = new Property();

            $property
                ->setTypeBien($categoryBien)
                ->setTypeTransaction($categoryBienTransaction)
                ->setTitreDuLogement($this->generateTitle($propertyData['typeBien'], $propertyData['typeTransaction'], $faker));

            $manager->persist($property);
        }

        $manager->flush();
    }

    private function generateTitle(string $typeBien, string $typeTransaction, $faker): string
    {
        $typeBienLabel = str_replace('-', ' ', $typeBien);

        $transactionLabel = match ($typeTransaction) {
            'vente' => 'à vendre',
            'location' => 'à louer',
            default => '',
        };

        return ucfirst($typeBienLabel) . ' ' . $transactionLabel . ' à ' . $faker->city();
    }

    public function getDependencies(): array
    {
        return [
            CategoryBienFixtures::class,
            CategoryBienTransactionFixtures::class,
        ];
    }
}
