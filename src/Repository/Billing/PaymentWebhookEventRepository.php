<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\PaymentWebhookEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentWebhookEvent> */
final class PaymentWebhookEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentWebhookEvent::class);
    }
}
