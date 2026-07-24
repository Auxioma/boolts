<?php

declare(strict_types=1);

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
