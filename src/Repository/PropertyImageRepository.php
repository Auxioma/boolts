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

namespace App\Repository;

use App\Entity\PropertyImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PropertyImage>
 */
class PropertyImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyImage::class);
    }

    /**
     * @param list<int> $propertyIds
     * @return array<int, int>
     */
    public function countByPropertyIds(array $propertyIds): array
    {
        if ([] === $propertyIds) {
            return [];
        }

        $counts = [];

        foreach (array_chunk($propertyIds, 500) as $propertyIdChunk) {
            $rows = $this->createQueryBuilder('pi')
                ->select('IDENTITY(pi.property) AS propertyId, COUNT(pi.id) AS imagesCount')
                ->andWhere('pi.property IN (:propertyIds)')
                ->setParameter('propertyIds', $propertyIdChunk)
                ->groupBy('pi.property')
                ->getQuery()
                ->getScalarResult();

            foreach ($rows as $row) {
                $counts[(int) $row['propertyId']] = (int) $row['imagesCount'];
            }
        }

        return $counts;
    }

    //    /**
    //     * @return PropertyImage[] Returns an array of PropertyImage objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PropertyImage
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
