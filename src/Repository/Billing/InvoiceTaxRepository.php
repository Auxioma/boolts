<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\InvoiceTax;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<InvoiceTax> */
final class InvoiceTaxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceTax::class);
    }
}
