<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\CreditNoteLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CreditNoteLine> */
final class CreditNoteLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditNoteLine::class);
    }
}
