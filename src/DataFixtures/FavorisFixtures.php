<?php

declare(strict_types=1);

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

    private const FAVORITE_PROPERTY_RATE = 0.8;

    private const BATCH_SIZE = 50;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(20260727);

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

        $favoritesTarget = (int) ceil(count($properties) * self::FAVORITE_PROPERTY_RATE);
        /** @var list<Property> $favoriteProperties */
        $favoriteProperties = $faker->randomElements($properties, $favoritesTarget);
        $favorisCreated = 0;

        foreach ($favoriteProperties as $property) {
            $ownerId = $property->getUser()?->getId();
            $eligibleUsers = array_filter(
                $users,
                static fn (User $user): bool => $user->getId() !== $ownerId,
            );

            if ([] === $eligibleUsers) {
                throw new \RuntimeException('Un utilisateur distinct du propriétaire est requis pour créer un favori.');
            }

            /** @var User $user */
            $user = $faker->randomElement($eligibleUsers);

            if (null === $user->getId() || null === $property->getId()) {
                throw new \LogicException('Les utilisateurs et biens doivent être persistés avant les favoris.');
            }

            $favoris = new Favoris();
            $favoris
                ->setUser($user)
                ->setProperty($property);

            $manager->persist($favoris);

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
