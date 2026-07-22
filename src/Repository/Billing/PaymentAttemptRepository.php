<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\PaymentAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentAttempt> */
final class PaymentAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentAttempt::class);
    }
}
