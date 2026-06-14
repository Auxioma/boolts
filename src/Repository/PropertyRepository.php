<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Repository;

use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Favoris;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Property>
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);
    }

    /**
     * Retourne une requête pour récupérer les biens immobiliers d’un utilisateur donné.
     * Cette méthode est utilisée pour la pagination dans le contrôleur DetailAgenceController.
     * De plus, il y aura les filtre de recherche à ajouter dans cette requête.
     * par default, elle retourne tous les biens de l’utilisateur sans filtre en ASC.
     */
    public function findPropertysByUserQuery(
        User $user,
        string $sort = 'p.createdAt',
        string $direction = 'DESC',
    ): QueryBuilder {
        $direction = mb_strtoupper($direction);

        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE);

        if ('p.views' === $sort) {
            return $qb
                ->leftJoin('p.propertyViews', 'pv')
                ->addSelect('COUNT(pv.id) AS HIDDEN viewsCount')
                ->groupBy('p.id')
                ->orderBy('viewsCount', $direction);
        }

        if ('favorisCount' === $sort) {
            return $qb
                ->leftJoin(Favoris::class, 'f', 'WITH', 'f.property = p')
                ->addSelect('COUNT(f.id) AS HIDDEN favorisCount')
                ->groupBy('p.id')
                ->orderBy('favorisCount', $direction);
        }

        return $qb->orderBy('p.createdAt', $direction);
    }
}
