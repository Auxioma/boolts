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

namespace App\Repository\Booster;

use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BoosterTransaction> */
final class BoosterTransactionRepository extends ServiceEntityRepository
{
    private const REUSABLE_CREDIT_TYPES = [
        BoosterTransactionType::PACK_PURCHASE,
        BoosterTransactionType::ADMIN_CREDIT,
    ];

    private const DEBIT_TYPES = [
        BoosterTransactionType::PROPERTY_BOOST,
        BoosterTransactionType::REFUND,
        BoosterTransactionType::ADMIN_DEBIT,
    ];

    private const ACTIVE_PERIOD_STATUSES = [
        SubscriptionPeriodStatus::FREE,
        SubscriptionPeriodStatus::PAID,
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoosterTransaction::class);
    }

    public function findOneByIdempotencyKey(string $idempotencyKey): ?BoosterTransaction
    {
        return $this->findOneBy(['idempotencyKey' => $idempotencyKey]);
    }

    public function countAvailableForAgency(User $agency, ?\DateTimeImmutable $now = null): int
    {
        return $this->countAvailableBySourceForAgency($agency, $now)['total'];
    }

    /**
     * @return array{subscription: int, independent: int, total: int}
     */
    public function countAvailableBySourceForAgency(User $agency, ?\DateTimeImmutable $now = null): array
    {
        $now ??= new \DateTimeImmutable();

        $reusableBalance = $this->sumReusableCredits($agency, $now) - $this->sumReusableDebits($agency);
        $subscriptionBalance = $this->sumCurrentSubscriptionPeriodAllowance($agency, $now)
            - $this->sumCurrentSubscriptionPeriodDebits($agency, $now);

        $independent = max(0, $reusableBalance);
        $subscription = max(0, $subscriptionBalance);

        return [
            'subscription' => $subscription,
            'independent' => $independent,
            'total' => $subscription + $independent,
        ];
    }

    private function sumReusableCredits(User $agency, \DateTimeImmutable $now): int
    {
        return $this->toInt($this->createQueryBuilder('transaction')
            ->select('COALESCE(SUM(transaction.quantity), 0)')
            ->where('transaction.agency = :agency')
            ->andWhere('transaction.type IN (:types)')
            ->andWhere('transaction.quantity > 0')
            ->andWhere('transaction.subscriptionPeriod IS NULL')
            ->andWhere('transaction.expiresAt IS NULL OR transaction.expiresAt >= :now')
            ->setParameter('agency', $agency)
            ->setParameter('types', $this->enumValues(self::REUSABLE_CREDIT_TYPES))
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult());
    }

    private function sumReusableDebits(User $agency): int
    {
        return $this->toInt($this->createQueryBuilder('transaction')
            ->select('COALESCE(SUM(ABS(transaction.quantity)), 0)')
            ->where('transaction.agency = :agency')
            ->andWhere('transaction.type IN (:types)')
            ->andWhere('transaction.subscriptionPeriod IS NULL')
            ->setParameter('agency', $agency)
            ->setParameter('types', $this->enumValues(self::DEBIT_TYPES))
            ->getQuery()
            ->getSingleScalarResult());
    }

    private function sumCurrentSubscriptionPeriodAllowance(User $agency, \DateTimeImmutable $now): int
    {
        return $this->toInt($this->getEntityManager()->createQueryBuilder()
            ->select('COALESCE(SUM(period.includedBoosts), 0)')
            ->from(AgencySubscriptionPeriod::class, 'period')
            ->innerJoin('period.subscription', 'subscription')
            ->where('subscription.agency = :agency')
            ->andWhere('period.status IN (:statuses)')
            ->andWhere('period.periodStart <= :now')
            ->andWhere('period.periodEnd >= :now')
            ->setParameter('agency', $agency)
            ->setParameter('statuses', $this->enumValues(self::ACTIVE_PERIOD_STATUSES))
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult());
    }

    private function sumCurrentSubscriptionPeriodDebits(User $agency, \DateTimeImmutable $now): int
    {
        return $this->toInt($this->createQueryBuilder('transaction')
            ->select('COALESCE(SUM(ABS(transaction.quantity)), 0)')
            ->innerJoin('transaction.subscriptionPeriod', 'period')
            ->innerJoin('period.subscription', 'subscription')
            ->where('transaction.agency = :agency')
            ->andWhere('subscription.agency = :agency')
            ->andWhere('transaction.type IN (:types)')
            ->andWhere('period.status IN (:statuses)')
            ->andWhere('period.periodStart <= :now')
            ->andWhere('period.periodEnd >= :now')
            ->setParameter('agency', $agency)
            ->setParameter('types', $this->enumValues(self::DEBIT_TYPES))
            ->setParameter('statuses', $this->enumValues(self::ACTIVE_PERIOD_STATUSES))
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult());
    }

    /**
     * @param list<\BackedEnum> $enums
     *
     * @return list<string>
     */
    private function enumValues(array $enums): array
    {
        return array_map(static fn (\BackedEnum $enum): string => (string) $enum->value, $enums);
    }

    private function toInt(mixed $value): int
    {
        return (int) $value;
    }
}
