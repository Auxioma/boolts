<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\PaymentFee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentFee> */
final class PaymentFeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentFee::class);
    }
}
