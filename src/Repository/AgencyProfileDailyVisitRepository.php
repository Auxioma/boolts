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

use App\Entity\AgencyProfileDailyVisit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgencyProfileDailyVisit>
 */
class AgencyProfileDailyVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgencyProfileDailyVisit::class);
    }

    /** @return list<AgencyProfileDailyVisit> */
    public function findForDashboard(User $agency, ?\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $queryBuilder = $this->createQueryBuilder('pv')
            ->andWhere('pv.agency = :agency')
            ->andWhere('pv.viewedOn <= :end')
            ->setParameter('agency', $agency)
            ->setParameter('end', $end)
            ->orderBy('pv.viewedOn', 'ASC');

        if (null !== $start) {
            $queryBuilder
                ->andWhere('pv.viewedOn >= :start')
                ->setParameter('start', $start);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
