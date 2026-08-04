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

namespace App\Repository\Billing;

use App\Entity\Billing\AgencySubscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<AgencySubscription> */
final class AgencySubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgencySubscription::class);
    }

    public function findLatestForAgency(User $agency): ?AgencySubscription
    {
        return $this->createQueryBuilder('subscription')
            ->addSelect('plan', 'price')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.agency = :agency')
            ->setParameter('agency', $agency)
            ->orderBy('subscription.startedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
