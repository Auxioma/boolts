<?php

declare(strict_types=1);

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
