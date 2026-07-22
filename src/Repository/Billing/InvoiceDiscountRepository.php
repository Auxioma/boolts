<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\InvoiceDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<InvoiceDiscount> */
final class InvoiceDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceDiscount::class);
    }
}
