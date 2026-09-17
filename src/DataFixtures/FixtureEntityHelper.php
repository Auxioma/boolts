<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;

final class FixtureEntityHelper
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param array<string, mixed> $criteria
     *
     * @return T
     */
    public static function findOrCreate(
        ObjectManager $manager,
        string $class,
        array $criteria,
    ): object {
        $entity = $manager->getRepository($class)->findOneBy($criteria);

        if (null === $entity) {
            $entity = new $class();
            $manager->persist($entity);
        }

        return $entity;
    }
}
