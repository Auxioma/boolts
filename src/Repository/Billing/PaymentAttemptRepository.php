<?php

declare(strict_types=1);

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

    public function nextAttemptNumber(
        AgencySubscription $subscription,
        string $providerInvoiceId,
    ): int {
        $lastAttemptNumber = $this->createQueryBuilder('attempt')
            ->select('MAX(attempt.attemptNumber)')
            ->where('attempt.subscription = :subscription')
            ->andWhere('attempt.providerInvoiceId = :providerInvoiceId')
            ->setParameter('subscription', $subscription)
            ->setParameter('providerInvoiceId', $providerInvoiceId)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $lastAttemptNumber ? 1 : ((int) $lastAttemptNumber) + 1;
    }

    public function hasAttemptNumber(
        AgencySubscription $subscription,
        string $providerInvoiceId,
        int $attemptNumber,
    ): bool {
        return null !== $this->findOneBy([
            'subscription' => $subscription,
            'providerInvoiceId' => $providerInvoiceId,
            'attemptNumber' => $attemptNumber,
        ]);
    }
}
