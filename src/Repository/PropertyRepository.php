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

    /**
     * filtre des bien similaire.
     */
    public function getBienSimilaire(Property $property, int $limit = 6): array
    {
        $qb = $this->createQueryBuilder('p');

        $ville = $property->getVille();
        $typeBien = $property->getTypeBien();
        $prix = $property->getPrix();
        $loyerHC = $property->getMontantLoyerHorsCharge();

        $qb
            ->andWhere('p.id != :currentId')
            ->setParameter('currentId', $property->getId())
            ->setMaxResults($limit)
        ;

        /*if ($ville) {
            $qb
                ->andWhere('p.ville = :ville')
                ->setParameter('ville', $ville)
            ;
        }*/

        if ($typeBien) {
            $qb
                ->andWhere('p.typeBien = :typeBien')
                ->setParameter('typeBien', $typeBien)
            ;
        }

        /*
         * Si c'est une vente, on compare le prix avec une marge de 20%
         */
        /*if ($prix) {
            $prixMin = $prix * 0.8;
            $prixMax = $prix * 1.2;

            $qb
                ->andWhere('p.prix BETWEEN :prixMin AND :prixMax')
                ->setParameter('prixMin', $prixMin)
                ->setParameter('prixMax', $prixMax)
            ;
        }*/

        /*
         * Si c'est une location, on compare le loyer hors charge avec une marge de 20%
         */
        if ($loyerHC) {
            $loyerMin = $loyerHC * 0.8;
            $loyerMax = $loyerHC * 1.2;

            $qb
                ->andWhere('p.montantLoyerHorsCharge BETWEEN :loyerMin AND :loyerMax')
                ->setParameter('loyerMin', $loyerMin)
                ->setParameter('loyerMax', $loyerMax)
            ;
        }

        $qb->orderBy('p.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * filtre de recherche de des bien immobilier de la page d'acceuil.
     */
    public function findBySearchQueryBuilder(
        ?int $transactionTypeId,
        ?string $ville,
        ?string $cp,
        ?string $pays,
        ?string $locale,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'pt')
            ->andWhere('pt.locale = :locale')
            ->setParameter('locale', $locale)
        ;

        if (null === $transactionTypeId || empty($pays)) {
            return $qb->andWhere('p.id IS NULL');
        }

        $qb
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->setParameter('transactionTypeId', $transactionTypeId);

        $qb
            ->andWhere('pt.pays = :pays')
            ->setParameter('pays', mb_trim($pays));

        if (!empty($ville)) {
            $qb
                ->andWhere('pt.ville = :ville')
                ->setParameter('ville', mb_trim($ville));
        }

        if (!empty($cp)) {
            $qb
                ->andWhere('p.codePostal = :cp')
                ->setParameter('cp', mb_trim($cp));
        }

        return $qb->orderBy('p.createdAt', 'DESC');
    }

    /**
     * logment les plus populaire a paris.
     */
    public function logementPopulaire($country, $locale, $id): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.propertyViews', 'pv')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('COUNT(pv.id) AS HIDDEN viewsCount')
            ->andWhere('pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->setParameter('transactionTypeId', $id)
            ->groupBy('p.id')
            ->orderBy('viewsCount', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults(10);

        if ($country) {
            $qb
                ->andWhere('pt.pays = :country')
                ->setParameter('country', $country);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Logement Ajouter Ressament, filtré par la date de update.
     */
    public function logemntRecementAjouter($country, $locale, $id): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'pt')
            ->andWhere('pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('pt.pays = :country')
            ->setParameter('country', $country)
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->setParameter('transactionTypeId', $id)
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

public function findBySearchAndMapBoundsQueryBuilder(
    int $transactionTypeId,
    ?string $ville,
    ?string $cp,
    string $pays,
    string $locale,
    float $north,
    float $south,
    float $east,
    float $west
): QueryBuilder {
    $queryBuilder = $this->findBySearchQueryBuilder(
        $transactionTypeId,
        $ville,
        $cp,
        $pays,
        $locale
    );

    $queryBuilder
        ->andWhere('p.latitude IS NOT NULL')
        ->andWhere('p.longitude IS NOT NULL')
        ->andWhere('p.latitude BETWEEN :south AND :north')
        ->setParameter('south', $south)
        ->setParameter('north', $north);

    if ($west <= $east) {
        $queryBuilder
            ->andWhere('p.longitude BETWEEN :west AND :east')
            ->setParameter('west', $west)
            ->setParameter('east', $east);
    } else {
        $queryBuilder
            ->andWhere('(p.longitude >= :west OR p.longitude <= :east)')
            ->setParameter('west', $west)
            ->setParameter('east', $east);
    }

    return $queryBuilder;
}
}
