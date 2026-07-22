<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\AgencyBillingProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<AgencyBillingProfile> */
final class AgencyBillingProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgencyBillingProfile::class);
    }
}
