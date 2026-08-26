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
        $paidSubscription = $this->findLatestByStatuses($agency, [SubscriptionStatus::ACTIVE]);

        if ($paidSubscription instanceof AgencySubscription) {
            return $paidSubscription;
        }

        return $this->findLatestByStatuses($agency, [SubscriptionStatus::FREE]);
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
