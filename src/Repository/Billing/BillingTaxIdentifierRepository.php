<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\BillingTaxIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BillingTaxIdentifier> */
final class BillingTaxIdentifierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BillingTaxIdentifier::class);
    }
}
