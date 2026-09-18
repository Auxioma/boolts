<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

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
