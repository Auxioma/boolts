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

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencyPaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class AgencyPaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            AgencyPaymentMethod::class
        );
    }

    public function findOneByStripePaymentMethodId(
        string $stripePaymentMethodId,
    ): ?AgencyPaymentMethod {
        return $this->findOneBy([
            'stripePaymentMethodId' => $stripePaymentMethodId,
        ]);
    }

    public function findOneByFingerprint(
        AgencyBillingProfile $billingProfile,
        string $fingerprint,
    ): ?AgencyPaymentMethod {
        return $this->findOneBy([
            'billingProfile' => $billingProfile,
            'fingerprint' => $fingerprint,
            'isActive' => true,
        ]);
    }

    /**
     * @return list<AgencyPaymentMethod>
     */
    public function findActiveByBillingProfile(
        AgencyBillingProfile $billingProfile,
    ): array {
        return $this->findBy(
            [
                'billingProfile' => $billingProfile,
                'isActive' => true,
            ],
            [
                'isDefault' => 'DESC',
                'id' => 'DESC',
            ]
        );
    }

    public function unsetDefaultForBillingProfile(
        AgencyBillingProfile $billingProfile,
    ): void {
        $this->createQueryBuilder('paymentMethod')
            ->update()
            ->set('paymentMethod.isDefault', ':isDefault')
            ->where(
                'paymentMethod.billingProfile = :billingProfile'
            )
            ->setParameter('isDefault', false)
            ->setParameter(
                'billingProfile',
                $billingProfile
            )
            ->getQuery()
            ->execute();
    }
}
