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

use App\Entity\Favoris;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FavorisFixtures extends Fixture implements DependentFixtureInterface
{
    public const FAVORIS_REFERENCE_PREFIX = 'favoris_';

    public const FAVORIS_COUNT = 3000;

    private const BATCH_SIZE = 500;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /**
         * Tableau anti-doublon.
         *
         * Important :
         * On ne doit jamais le vider pendant la fixture,
         * sinon Doctrine peut recréer le même couple user/property.
         */
        $existingFavoris = [];

        $favorisCreated = 0;
        $attempts = 0;
        $maxAttempts = self::FAVORIS_COUNT * 20;

        while ($favorisCreated < self::FAVORIS_COUNT && $attempts < $maxAttempts) {
            ++$attempts;

            $userIndex = $faker->numberBetween(1, UserFixtures::USER_COUNT);
            $propertyIndex = $faker->numberBetween(1, PropertyFixtures::PROPERTY_COUNT);

            $uniqueKey = $userIndex.'_'.$propertyIndex;

            if (isset($existingFavoris[$uniqueKey])) {
                continue;
            }

            /** @var User $user */
            $user = $this->getReference(
                UserFixtures::USER_REFERENCE_PREFIX.$userIndex,
                User::class
            );

            /** @var Property $property */
            $property = $this->getReference(
                PropertyFixtures::PROPERTY_REFERENCE_PREFIX.$propertyIndex,
                Property::class
            );

            /*
             * On évite qu'une agence mette en favori son propre bien.
             */
            if ($property->getUser() === $user) {
                continue;
            }

            $favoris = new Favoris();
            $favoris
                ->setUser($user)
                ->setProperty($property);

            $manager->persist($favoris);

            $existingFavoris[$uniqueKey] = true;

            ++$favorisCreated;

            $this->addReference(
                self::FAVORIS_REFERENCE_PREFIX.$favorisCreated,
                $favoris
            );

            if (0 === $favorisCreated % self::BATCH_SIZE) {
                $manager->flush();
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PropertyFixtures::class,
        ];
    }
}
