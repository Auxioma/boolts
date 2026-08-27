<?php

declare(strict_types=1);

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
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<AgencySubscriptionPeriod> */
final class AgencySubscriptionPeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgencySubscriptionPeriod::class);
    }

    public function findOneByProviderInvoiceId(string $providerInvoiceId): ?AgencySubscriptionPeriod
    {
        return $this->findOneBy(['providerInvoiceId' => $providerInvoiceId]);
    }

    /**
     * @return list<AgencySubscriptionPeriod>
     */
    public function findForAgency(User $agency, int $limit = 50): array
    {
        return $this->createQueryBuilder('period')
            ->addSelect('subscription', 'plan', 'payment', 'currency', 'paymentCurrency')
            ->innerJoin('period.subscription', 'subscription')
            ->innerJoin('subscription.plan', 'plan')
            ->innerJoin('period.currency', 'currency')
            ->leftJoin('period.payment', 'payment')
            ->leftJoin('payment.currency', 'paymentCurrency')
            ->where('subscription.agency = :agency')
            ->setParameter('agency', $agency)
            ->orderBy('period.periodStart', 'DESC')
            ->addOrderBy('period.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneForPeriod(
        AgencySubscription $subscription,
        \DateTimeImmutable $periodStart,
        \DateTimeImmutable $periodEnd,
    ): ?AgencySubscriptionPeriod {
        return $this->createQueryBuilder('period')
            ->where('period.subscription = :subscription')
            ->andWhere('period.periodStart = :periodStart')
            ->andWhere('period.periodEnd = :periodEnd')
            ->andWhere('period.status != :canceledStatus')
            ->setParameter('subscription', $subscription)
            ->setParameter('periodStart', $periodStart)
            ->setParameter('periodEnd', $periodEnd)
            ->setParameter('canceledStatus', SubscriptionPeriodStatus::CANCELED)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPaidContaining(
        AgencySubscription $subscription,
        \DateTimeImmutable $date,
    ): ?AgencySubscriptionPeriod {
        return $this->createQueryBuilder('period')
            ->where('period.subscription = :subscription')
            ->andWhere('period.status = :status')
            ->andWhere('period.periodStart <= :date')
            ->andWhere('period.periodEnd > :date')
            ->setParameter('subscription', $subscription)
            ->setParameter('status', SubscriptionPeriodStatus::PAID)
            ->setParameter('date', $date)
            ->orderBy('period.periodStart', 'DESC')
            ->addOrderBy('period.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestPaidBefore(
        AgencySubscription $subscription,
        \DateTimeImmutable $date,
    ): ?AgencySubscriptionPeriod {
        return $this->createQueryBuilder('period')
            ->where('period.subscription = :subscription')
            ->andWhere('period.status = :status')
            ->andWhere('period.periodStart < :date')
            ->setParameter('subscription', $subscription)
            ->setParameter('status', SubscriptionPeriodStatus::PAID)
            ->setParameter('date', $date)
            ->orderBy('period.periodStart', 'DESC')
            ->addOrderBy('period.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
