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
use App\Entity\Devise;
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
    public function findActiveWithPlanAndCurrency(Devise $currency): array
    {
        return $this->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->andWhere('price.currency = :currency')
            ->setParameter('active', true)
            ->setParameter('currency', $currency)
            ->orderBy('plan.position', 'ASC')
            ->addOrderBy('price.billingPeriod', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveForPlanAndPeriod(int $planId, SubscriptionBillingPeriod $billingPeriod, Devise $currency): ?SubscriptionPlanPrice
    {
        return $this->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('plan.id = :id')
            ->andWhere('price.billingPeriod = :period')
            ->andWhere('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->andWhere('price.currency = :currency')
            ->setParameter('id', $planId)
            ->setParameter('currency', $currency)
            ->setParameter('period', $billingPeriod)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findDefaultFreeMonthlyPrice(Devise $currency): ?SubscriptionPlanPrice
    {
        return $this->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('price.billingPeriod = :period')
            ->andWhere('price.amountMinor = :amount')
            ->andWhere('plan.isFree = :free')
            ->andWhere('plan.isDefault = :default')
            ->andWhere('price.currency = :currency')
            ->setParameter('currency', $currency)
            ->setParameter('period', SubscriptionBillingPeriod::MONTHLY)
            ->setParameter('amount', 0)
            ->setParameter('free', true)
            ->setParameter('default', true)
            ->orderBy('plan.isActive', 'DESC')
            ->addOrderBy('price.isActive', 'DESC')
            ->addOrderBy('plan.position', 'ASC')
            ->addOrderBy('price.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasActivePricesInCurrency(Devise $currency): bool
    {
        return null !== $this->createQueryBuilder('price')
            ->select('price.id')
            ->innerJoin('price.plan', 'plan')
            ->where('price.currency = :currency')
            ->andWhere('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->setParameter('currency', $currency)
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByPaymentProviderPriceId(string $paymentProviderPriceId): ?SubscriptionPlanPrice
    {
        return $this->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('price.paymentProviderPriceId = :paymentProviderPriceId')
            ->setParameter('paymentProviderPriceId', $paymentProviderPriceId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
