<?php

declare(strict_types=1);

namespace App\Repository\Billing;

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
}
