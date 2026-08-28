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
use App\Entity\Billing\Enum\SubscriptionStatus;
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

    public function findCurrentForAgency(User $agency): ?AgencySubscription
    {
        $paidSubscription = $this->findLatestByStatuses(
            $agency,
            [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::PAST_DUE,
                SubscriptionStatus::PAYMENT_FAILED,
                SubscriptionStatus::CANCEL_SCHEDULED,
                SubscriptionStatus::INCOMPLETE,
            ],
        );

        if ($paidSubscription instanceof AgencySubscription) {
            return $paidSubscription;
        }

        return $this->findLatestByStatuses($agency, [SubscriptionStatus::FREE]);
    }

    public function findLatestQuotaForAgency(User $agency): ?AgencySubscription
    {
        $paidSubscription = $this->findLatestByStatuses(
            $agency,
            [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::PAST_DUE,
                SubscriptionStatus::PAYMENT_FAILED,
                SubscriptionStatus::CANCEL_SCHEDULED,
            ],
        );

        if ($paidSubscription instanceof AgencySubscription) {
            return $paidSubscription;
        }

        return $this->findLatestByStatuses($agency, [SubscriptionStatus::FREE]);
    }

    public function findOneActivePaidForAgency(User $agency): ?AgencySubscription
    {
        return $this->createQueryBuilder('subscription')
            ->addSelect('plan', 'price')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.agency = :agency')
            ->andWhere('plan.isFree = :free')
            ->andWhere('subscription.status IN (:statuses)')
            ->setParameter('agency', $agency)
            ->setParameter('free', false)
            ->setParameter('statuses', [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::PAST_DUE,
                SubscriptionStatus::PAYMENT_FAILED,
                SubscriptionStatus::CANCEL_SCHEDULED,
                SubscriptionStatus::INCOMPLETE,
                SubscriptionStatus::UNPAID,
            ])
            ->orderBy('subscription.startedAt', 'DESC')
            ->addOrderBy('subscription.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByProviderSubscriptionId(string $providerSubscriptionId): ?AgencySubscription
    {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'profile', 'plan', 'price')
            ->innerJoin('subscription.agency', 'agency')
            ->leftJoin('agency.billingProfile', 'profile')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.providerSubscriptionId = :providerSubscriptionId')
            ->setParameter('providerSubscriptionId', $providerSubscriptionId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findActiveSubscriptionsToProcess(
        \DateTimeImmutable $now,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'profile', 'plan', 'price')
            ->innerJoin('subscription.agency', 'agency')
            ->leftJoin('agency.billingProfile', 'profile')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.status = :status')
            ->andWhere('plan.isFree = :free')
            ->andWhere('subscription.providerSubscriptionId IS NOT NULL')
            ->andWhere('subscription.cancelAtPeriodEnd = :cancelAtPeriodEnd')
            ->andWhere('subscription.currentPeriodEnd IS NULL OR subscription.currentPeriodEnd <= :now')
            ->setParameter('status', SubscriptionStatus::ACTIVE)
            ->setParameter('free', false)
            ->setParameter('cancelAtPeriodEnd', false)
            ->setParameter('now', $now)
            ->orderBy('subscription.currentPeriodEnd', 'ASC')
            ->addOrderBy('subscription.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findFreeSubscriptionsToRenew(
        \DateTimeImmutable $now,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'plan', 'price', 'currency')
            ->innerJoin('subscription.agency', 'agency')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->leftJoin('price.currency', 'currency')
            ->where('subscription.status = :status')
            ->andWhere('plan.isFree = :free')
            ->andWhere('subscription.endedAt IS NULL')
            ->andWhere('subscription.currentPeriodEnd IS NULL OR subscription.currentPeriodEnd <= :now')
            ->setParameter('status', SubscriptionStatus::FREE)
            ->setParameter('free', true)
            ->setParameter('now', $now)
            ->orderBy('subscription.currentPeriodEnd', 'ASC')
            ->addOrderBy('subscription.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findFailedSubscriptionsToRetry(
        \DateTimeImmutable $now,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'profile', 'plan', 'price')
            ->innerJoin('subscription.agency', 'agency')
            ->leftJoin('agency.billingProfile', 'profile')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.status IN (:statuses)')
            ->andWhere('subscription.paymentFailureCount < :maxAttempts')
            ->andWhere('subscription.nextPaymentRetryAt IS NOT NULL')
            ->andWhere('subscription.nextPaymentRetryAt <= :now')
            ->andWhere('subscription.paymentRecoveryDeadline IS NOT NULL')
            ->andWhere('subscription.paymentRecoveryDeadline >= :now')
            ->andWhere('subscription.providerLatestInvoiceId IS NOT NULL')
            ->setParameter('statuses', [
                SubscriptionStatus::PAST_DUE,
                SubscriptionStatus::PAYMENT_FAILED,
                SubscriptionStatus::UNPAID,
            ])
            ->setParameter('maxAttempts', 5)
            ->setParameter('now', $now)
            ->orderBy('subscription.nextPaymentRetryAt', 'ASC')
            ->addOrderBy('subscription.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findFailedSubscriptionsToFinalize(
        \DateTimeImmutable $now,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'profile', 'plan', 'price')
            ->innerJoin('subscription.agency', 'agency')
            ->leftJoin('agency.billingProfile', 'profile')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.status IN (:statuses)')
            ->andWhere('subscription.providerLatestInvoiceId IS NOT NULL')
            ->andWhere(
                'subscription.paymentFailureCount >= :maxAttempts '
                .'OR subscription.paymentRecoveryDeadline < :now'
            )
            ->setParameter('statuses', [
                SubscriptionStatus::PAST_DUE,
                SubscriptionStatus::PAYMENT_FAILED,
                SubscriptionStatus::UNPAID,
            ])
            ->setParameter('maxAttempts', 5)
            ->setParameter('now', $now)
            ->orderBy('subscription.lastPaymentFailureAt', 'ASC')
            ->addOrderBy('subscription.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findCanceledSubscriptionsToFinalize(
        \DateTimeImmutable $now,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'profile', 'plan', 'price')
            ->innerJoin('subscription.agency', 'agency')
            ->leftJoin('agency.billingProfile', 'profile')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.cancelAtPeriodEnd = :cancelAtPeriodEnd')
            ->andWhere('subscription.status = :status')
            ->andWhere('subscription.currentPeriodEnd IS NOT NULL')
            ->andWhere('subscription.currentPeriodEnd <= :now')
            ->andWhere('subscription.providerSubscriptionId IS NOT NULL')
            ->setParameter('cancelAtPeriodEnd', true)
            ->setParameter('status', SubscriptionStatus::CANCEL_SCHEDULED)
            ->setParameter('now', $now)
            ->orderBy('subscription.currentPeriodEnd', 'ASC')
            ->addOrderBy('subscription.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findSubscriptionsToSynchronize(
        \DateTimeImmutable $staleBefore,
        int $limit = 100,
    ): array {
        return $this->createQueryBuilder('subscription')
            ->addSelect('agency', 'profile', 'plan', 'price')
            ->innerJoin('subscription.agency', 'agency')
            ->leftJoin('agency.billingProfile', 'profile')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.providerSubscriptionId IS NOT NULL')
            ->andWhere('subscription.status IN (:statuses)')
            ->andWhere('subscription.lastStripeSyncAt IS NULL OR subscription.lastStripeSyncAt <= :staleBefore')
            ->setParameter('statuses', [
                SubscriptionStatus::ACTIVE,
                SubscriptionStatus::PAST_DUE,
                SubscriptionStatus::PAYMENT_FAILED,
                SubscriptionStatus::CANCEL_SCHEDULED,
                SubscriptionStatus::INCOMPLETE,
                SubscriptionStatus::UNPAID,
            ])
            ->setParameter('staleBefore', $staleBefore)
            ->orderBy('subscription.lastStripeSyncAt', 'ASC')
            ->addOrderBy('subscription.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencySubscription>
     */
    public function findOpenFreeForAgency(User $agency): array
    {
        return $this->createQueryBuilder('subscription')
            ->addSelect('plan', 'price')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.agency = :agency')
            ->andWhere('subscription.status = :status')
            ->andWhere('subscription.endedAt IS NULL')
            ->setParameter('agency', $agency)
            ->setParameter('status', SubscriptionStatus::FREE)
            ->orderBy('subscription.startedAt', 'DESC')
            ->addOrderBy('subscription.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<SubscriptionStatus> $statuses
     */
    private function findLatestByStatuses(User $agency, array $statuses): ?AgencySubscription
    {
        return $this->createQueryBuilder('subscription')
            ->addSelect('plan', 'price')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.agency = :agency')
            ->andWhere('subscription.status IN (:statuses)')
            ->setParameter('agency', $agency)
            ->setParameter('statuses', $statuses)
            ->orderBy('subscription.startedAt', 'DESC')
            ->addOrderBy('subscription.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
