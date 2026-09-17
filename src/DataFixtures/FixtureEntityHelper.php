<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;

final class FixtureEntityHelper
{
    /**
     * @template T of object
     *
     * @param class-string<T>      $class
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
