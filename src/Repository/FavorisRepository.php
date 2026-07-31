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

use App\Entity\Favoris;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Favoris>
 */
class FavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favoris::class);
    }

    public function findPropertyIdsByUser(User $user): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.property) AS propertyId')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult();

        return array_map('intval', array_column($rows, 'propertyId'));
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

        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.property) AS propertyId, COUNT(f.id) AS favoritesCount')
            ->andWhere('f.property IN (:propertyIds)')
            ->setParameter('propertyIds', $propertyIds)
            ->groupBy('f.property')
            ->getQuery()
            ->getScalarResult();

        $counts = [];

        foreach ($rows as $row) {
            $counts[(int) $row['propertyId']] = (int) $row['favoritesCount'];
        }

        return $counts;
    }

    /**
     * @return list<\DateTimeImmutable>
     */
    public function findCreatedDatesForPropertyOwnerDashboard(User $user, ?\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.createdAt')
            ->innerJoin('f.property', 'p')
            ->andWhere('p.user = :user')
            ->andWhere('f.createdAt IS NOT NULL')
            ->andWhere('f.createdAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('end', $end);

        if (null !== $start) {
            $queryBuilder
                ->andWhere('f.createdAt >= :start')
                ->setParameter('start', $start);
        }

        return array_map(
            static fn (array $row): \DateTimeImmutable => new \DateTimeImmutable($row['createdAt']),
            $queryBuilder->getQuery()->getScalarResult(),
        );
    }

    //    /**
    //     * @return Favoris[] Returns an array of Favoris objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Favoris
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
