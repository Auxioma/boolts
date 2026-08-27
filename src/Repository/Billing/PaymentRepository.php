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
use App\Entity\Billing\Payment;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Payment> */
final class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findOneByProviderInvoiceId(string $providerInvoiceId): ?Payment
    {
        return $this->findOneBy(['providerInvoiceId' => $providerInvoiceId]);
    }

    public function findOneByProviderPaymentIntentId(string $providerPaymentIntentId): ?Payment
    {
        return $this->findOneBy(['providerPaymentIntentId' => $providerPaymentIntentId]);
    }

    public function findRenewalForInvoice(
        AgencySubscription $subscription,
        string $providerInvoiceId,
    ): ?Payment {
        return $this->createQueryBuilder('payment')
            ->where('payment.subscription = :subscription')
            ->andWhere('payment.providerInvoiceId = :providerInvoiceId')
            ->andWhere('payment.type = :type')
            ->setParameter('subscription', $subscription)
            ->setParameter('providerInvoiceId', $providerInvoiceId)
            ->setParameter('type', PaymentType::SUBSCRIPTION_RENEWAL)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<Payment>
     */
    public function findSubscriptionPaymentsForAgency(User $agency, int $limit = 24): array
    {
        return $this->createQueryBuilder('payment')
            ->where('payment.agency = :agency')
            ->andWhere('payment.type IN (:types)')
            ->setParameter('agency', $agency)
            ->setParameter('types', [
                PaymentType::SUBSCRIPTION_INITIAL,
                PaymentType::SUBSCRIPTION_RENEWAL,
                PaymentType::SUBSCRIPTION_UPGRADE,
                PaymentType::SUBSCRIPTION_DOWNGRADE_ADJUSTMENT,
            ])
            ->orderBy('payment.paidAt', 'DESC')
            ->addOrderBy('payment.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
