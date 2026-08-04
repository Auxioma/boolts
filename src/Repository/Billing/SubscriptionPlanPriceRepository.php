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

use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\SubscriptionPlanPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SubscriptionPlanPrice> */
final class SubscriptionPlanPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlanPrice::class);
    }

    /** @return list<SubscriptionPlanPrice> */
    public function findActiveWithPlanAndCurrency(): array
    {
        return $this->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('plan.position', 'ASC')
            ->addOrderBy('price.billingPeriod', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveForPlanAndPeriod(int $planId, SubscriptionBillingPeriod $billingPeriod): ?SubscriptionPlanPrice
    {
        return $this->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('plan.id = :id')
            ->andWhere('price.billingPeriod = :period')
            ->andWhere('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->setParameter('id', $planId)
            ->setParameter('period', $billingPeriod)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
