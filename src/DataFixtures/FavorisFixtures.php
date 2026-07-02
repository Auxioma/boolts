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

    public const FAVORIS_COUNT = 30;

    private const BATCH_SIZE = 50;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /**
         * On récupère les vrais utilisateurs créés en base.
         * Comme ça, on évite les erreurs de références inexistantes.
         *
         * Exemple d'erreur évitée :
         * Reference to "property_1525" does not exist
         */
        $users = $manager
            ->getRepository(User::class)
            ->findAll();

        /**
         * On récupère les vrais biens créés en base.
         * Important :
         * PropertyFixtures crée un nombre dynamique de biens.
         * Il ne faut donc pas utiliser PropertyFixtures::PROPERTY_COUNT ici.
         */
        $properties = $manager
            ->getRepository(Property::class)
            ->findAll();

        if ([] === $users || [] === $properties) {
            return;
        }

        /**
         * Tableau anti-doublon.
         *
         * Clé utilisée :
         * userId_propertyId
         *
         * Exemple :
         * 12_458
         */
        $existingFavoris = [];

        $favorisCreated = 0;
        $attempts = 0;

        /**
         * Sécurité pour éviter une boucle infinie.
         *
         * On met large, car certains couples peuvent être refusés :
         * - doublon user/property
         * - agence qui veut mettre son propre bien en favori
         */
        $maxAttempts = self::FAVORIS_COUNT * 100;

        while ($favorisCreated < self::FAVORIS_COUNT && $attempts < $maxAttempts) {
            ++$attempts;

            /** @var User $user */
            $user = $faker->randomElement($users);

            /** @var Property $property */
            $property = $faker->randomElement($properties);

            if (null === $user->getId() || null === $property->getId()) {
                continue;
            }

            /**
             * On évite qu'une agence mette en favori son propre bien.
             */
            if (
                null !== $property->getUser()
                && null !== $property->getUser()->getId()
                && $property->getUser()->getId() === $user->getId()
            ) {
                continue;
            }

            $uniqueKey = $user->getId().'_'.$property->getId();

            if (isset($existingFavoris[$uniqueKey])) {
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