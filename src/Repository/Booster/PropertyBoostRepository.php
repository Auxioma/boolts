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

namespace App\Repository\Booster;

use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\PropertyBoost;
use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PropertyBoost> */
final class PropertyBoostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyBoost::class);
    }

    /**
     * Indique si l'annonce dispose d'un boost actif non expiré et non annulé.
     */
    public function hasActiveBoost(Property $property, ?\DateTimeImmutable $now = null): bool
    {
        $now ??= new \DateTimeImmutable();

        $count = (int) $this->createQueryBuilder('boost')
            ->select('COUNT(boost.id)')
            ->where('boost.property = :property')
            ->andWhere('boost.status = :status')
            ->andWhere('boost.canceledAt IS NULL')
            ->andWhere('boost.endsAt >= :now')
            ->setParameter('property', $property)
            ->setParameter('status', PropertyBoostStatus::ACTIVE->value)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
