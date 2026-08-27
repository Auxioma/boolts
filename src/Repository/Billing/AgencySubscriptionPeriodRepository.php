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

    public function findOneForPeriod(
        AgencySubscription $subscription,
        \DateTimeImmutable $periodStart,
        \DateTimeImmutable $periodEnd,
    ): ?AgencySubscriptionPeriod {
        return $this->createQueryBuilder('period')
            ->where('period.subscription = :subscription')
            ->andWhere('period.periodStart = :periodStart')
            ->andWhere('period.periodEnd = :periodEnd')
            ->setParameter('subscription', $subscription)
            ->setParameter('periodStart', $periodStart)
            ->setParameter('periodEnd', $periodEnd)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
