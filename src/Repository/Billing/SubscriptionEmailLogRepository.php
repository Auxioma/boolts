<?php

declare(strict_types=1);

namespace App\Repository\Billing;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionEmailType;
use App\Entity\Billing\SubscriptionEmailLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SubscriptionEmailLog> */
final class SubscriptionEmailLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionEmailLog::class);
    }

    public function findOneForEvent(
        AgencySubscription $subscription,
        SubscriptionEmailType $eventType,
        string $eventKey,
    ): ?SubscriptionEmailLog {
        return $this->findOneBy([
            'subscription' => $subscription,
            'eventType' => $eventType,
            'eventKey' => $eventKey,
        ]);
    }
}
