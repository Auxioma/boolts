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

namespace App\Repository;

use App\Entity\PropertyView;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PropertyView>
 */
class PropertyViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyView::class);
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
            $rows = $this->createQueryBuilder('pv')
                ->select('IDENTITY(pv.property) AS propertyId, COUNT(pv.id) AS viewsCount')
                ->andWhere('pv.property IN (:propertyIds)')
                ->setParameter('propertyIds', $propertyIdChunk)
                ->groupBy('pv.property')
                ->getQuery()
                ->getScalarResult();

            foreach ($rows as $row) {
                $counts[(int) $row['propertyId']] = (int) $row['viewsCount'];
            }
        }

        return $counts;
    }

    /**
     * @return list<\DateTimeImmutable>
     */
    public function findViewedDatesForDashboard(User $user, ?\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $queryBuilder = $this->createQueryBuilder('pv')
            ->select('pv.viewedAt')
            ->innerJoin('pv.property', 'p')
            ->andWhere('p.user = :user')
            ->andWhere('pv.viewedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('end', $end);

        if (null !== $start) {
            $queryBuilder
                ->andWhere('pv.viewedAt >= :start')
                ->setParameter('start', $start);
        }

        return array_map(
            static fn (array $row): \DateTimeImmutable => new \DateTimeImmutable($row['viewedAt']),
            $queryBuilder->getQuery()->getScalarResult(),
        );
    }

    //    /**
    //     * @return PropertyView[] Returns an array of PropertyView objects
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

    //    public function findOneBySomeField($value): ?PropertyView
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
