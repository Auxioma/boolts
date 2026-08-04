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

use App\Entity\Booster\BoosterPackPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BoosterPackPrice> */
final class BoosterPackPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoosterPackPrice::class);
    }

    /** @return list<BoosterPackPrice> */
    public function findActiveWithPackAndCurrency(): array
    {
        return $this->createQueryBuilder('price')
            ->addSelect('pack', 'currency')
            ->innerJoin('price.boosterPack', 'pack')
            ->innerJoin('price.currency', 'currency')
            ->where('price.isActive = :active')
            ->andWhere('pack.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pack.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
