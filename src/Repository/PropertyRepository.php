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

namespace App\Repository;

use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\PropertyBoost;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Favoris;
use App\Entity\Property;
use App\Entity\PropertyView;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Intl\Countries;

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
     * @return list<\DateTimeImmutable>
     */
    public function findPublishedDatesForDashboard(User $user, ?\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.createdAt')
            ->andWhere('p.user = :user')
            ->andWhere('p.statut = :status')
            ->andWhere('p.createdAt IS NOT NULL')
            ->andWhere('p.createdAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('status', StatutAnnonceImmobiliere::PUBLIEE)
            ->setParameter('end', $end);

        if (null !== $start) {
            $queryBuilder
                ->andWhere('p.createdAt >= :start')
                ->setParameter('start', $start);
        }

        return array_map(
            static fn (array $row): \DateTimeImmutable => new \DateTimeImmutable($row['createdAt']),
            $queryBuilder->getQuery()->getScalarResult(),
        );
    }

    public function countUsedForAgencyQuota(User $agency): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.user = :agency')
            ->andWhere('p.statut != :deletedStatus')
            ->setParameter('agency', $agency)
            ->setParameter(
                'deletedStatus',
                StatutAnnonceImmobiliere::SUPPRIMEE
            )
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<Property>
     */
    public function findPublishedPropertiesExceedingQuota(
        User $agency,
        int $allowedPublishedProperties,
    ): array {
        if ($allowedPublishedProperties < 0) {
            $allowedPublishedProperties = 0;
        }

        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :agency')
            ->andWhere('p.statut = :publishedStatus')
            ->setParameter('agency', $agency)
            ->setParameter('publishedStatus', StatutAnnonceImmobiliere::PUBLIEE)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setFirstResult($allowedPublishedProperties)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne une requête pour récupérer les biens immobiliers d’un utilisateur donné.
     * Cette méthode est utilisée pour la pagination dans le contrôleur DetailAgenceController.
     * De plus, il y aura les filtre de recherche à ajouter dans cette requête.
     * par default, elle retourne tous les biens de l’utilisateur sans filtre en ASC.
     */
    public function findPropertysByUserQuery(
        User $user,
        ?string $search = null,
        string $sort = 'p.createdAt',
        string $direction = 'DESC',
    ): QueryBuilder {
        $direction = mb_strtoupper($direction);

        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'pt')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE);

        if ($search) {
            $qb
                ->andWhere(
                    'LOWER(p.referenceInterne) LIKE :search
                OR LOWER(pt.titreDuLogement) LIKE :search
                OR LOWER(pt.ville) LIKE :search'
                )
                ->setParameter(
                    'search',
                    '%'.mb_strtolower($search).'%'
                );
        }

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

    public function findForDashboardPerformanceQuery(
        User $user,
        ?\DateTimeImmutable $start,
        \DateTimeImmutable $end,
        string $sort = 'created',
        string $direction = 'DESC',
    ): QueryBuilder {
        $direction = mb_strtoupper($direction);

        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.propertyImages', 'pi')
            ->addSelect('pi')
            ->innerJoin('p.user', 'u')
            ->addSelect('u')
            ->leftJoin('u.devise', 'currency')
            ->addSelect('currency')
            ->andWhere('p.user = :user')
            ->andWhere('p.statut = :statut')
            ->andWhere('p.createdAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE)
            ->setParameter('end', $end);

        if (null !== $start) {
            $queryBuilder
                ->andWhere('p.createdAt >= :start')
                ->setParameter('start', $start);
        }

        if ('views' === $sort) {
            $viewsCountSelect = '(SELECT COUNT(periodView.id) FROM '.PropertyView::class.' periodView WHERE periodView.property = p AND periodView.viewedAt <= :end';

            if (null !== $start) {
                $viewsCountSelect .= ' AND periodView.viewedAt >= :start';
            }

            $viewsCountSelect .= ') AS HIDDEN viewsCount';

            return $queryBuilder
                ->addSelect($viewsCountSelect)
                ->orderBy('viewsCount', $direction)
                ->addOrderBy('p.createdAt', 'DESC');
        }

        if ('favorites' === $sort) {
            $favoritesCountSelect = '(SELECT COUNT(periodFavorite.id) FROM '.Favoris::class.' periodFavorite WHERE periodFavorite.property = p AND periodFavorite.createdAt IS NOT NULL AND periodFavorite.createdAt <= :end';

            if (null !== $start) {
                $favoritesCountSelect .= ' AND periodFavorite.createdAt >= :start';
            }

            $favoritesCountSelect .= ') AS HIDDEN favoritesCount';

            return $queryBuilder
                ->addSelect($favoritesCountSelect)
                ->orderBy('favoritesCount', $direction)
                ->addOrderBy('p.createdAt', 'DESC');
        }

        return $queryBuilder->orderBy('p.createdAt', $direction);
    }

    /**
     * @return list<Property>
     */
    public function findForDashboardExport(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'propertyTranslation')
            ->addSelect('propertyTranslation')
            ->leftJoin('p.propertyImages', 'propertyImage')
            ->addSelect('propertyImage')
            ->leftJoin('p.typeBien', 'typeBien')
            ->addSelect('typeBien')
            ->leftJoin('typeBien.translations', 'typeBienTranslation')
            ->addSelect('typeBienTranslation')
            ->leftJoin('p.typeTransaction', 'typeTransaction')
            ->addSelect('typeTransaction')
            ->leftJoin('typeTransaction.translations', 'typeTransactionTranslation')
            ->addSelect('typeTransactionTranslation')
            ->leftJoin('p.caracteristique', 'caracteristique')
            ->addSelect('caracteristique')
            ->leftJoin('caracteristique.translations', 'caracteristiqueTranslation')
            ->addSelect('caracteristiqueTranslation')
            ->innerJoin('p.user', 'u')
            ->addSelect('u')
            ->leftJoin('u.devise', 'currency')
            ->addSelect('currency')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('propertyImage.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Options de tri autorisées pour la liste "Mes biens" de l'agence.
     *
     * Toute autre valeur retombe sur le tri par défaut
     * (date de modification, du plus récent au plus ancien).
     */
    public const array MES_BIENS_SORTS = [
        'p.updatedAt',
        'p.createdAt',
        'p.views',
        'favorisCount',
    ];

    /**
     * Statuts affichés dans la liste principale paginée / filtrable
     * de la page « Mes biens » de l'agence (les brouillons ont leur
     * propre section et sont donc exclus).
     */
    public const array MES_BIENS_LISTED_STATUTS = [
        StatutAnnonceImmobiliere::PUBLIEE,
        StatutAnnonceImmobiliere::DEPUBLIEE,
        StatutAnnonceImmobiliere::PENDING,
        StatutAnnonceImmobiliere::REFUSEE,
        StatutAnnonceImmobiliere::SUSPENDUE,
    ];

    public function findPropertysByUserWithFiltersQuery(
        User $user,
        ?string $search = null,
        array $filters = [],
        string $sort = 'p.updatedAt',
        string $direction = 'DESC',
        ?string $locale = null,
    ): QueryBuilder {
        if (
            isset($filters['modal_filter'])
            && \is_array($filters['modal_filter'])
        ) {
            $filters = $filters['modal_filter'];
        }

        $direction = mb_strtoupper($direction);

        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        if (!\in_array($sort, self::MES_BIENS_SORTS, true)) {
            $sort = 'p.updatedAt';
        }

        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.statut IN (:statuts)')
            ->setParameter(
                'statuts',
                self::MES_BIENS_LISTED_STATUTS
            );

        if (
            null !== $locale
            && '' !== mb_trim($locale)
        ) {
            $qb
                ->andWhere('pt.locale = :locale')
                ->setParameter('locale', $locale);
        }

        if (
            null !== $search
            && '' !== mb_trim($search)
        ) {
            $normalizedSearch = mb_strtolower(
                mb_trim($search)
            );

            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'LOWER(p.referenceInterne) LIKE :search',
                        'LOWER(p.slug) LIKE :search',
                        'LOWER(pt.titreDuLogement) LIKE :search',
                        'LOWER(pt.ville) LIKE :search',
                        'LOWER(pt.pays) LIKE :search',
                        'LOWER(pt.adresse) LIKE :search',
                        'LOWER(pt.fullAddress) LIKE :search'
                    )
                )
                ->setParameter(
                    'search',
                    '%'.$normalizedSearch.'%'
                );
        }

        $natureDeLaPropriete = $this->normalizeSingleValue(
            $filters['natureDeLaPropriete'] ?? null,
            [
                'id',
                'value',
                'code',
                'name',
                'label',
            ]
        );

        if (null !== $natureDeLaPropriete) {
            $qb
                ->andWhere(
                    'IDENTITY(p.typeTransaction) = :natureDeLaPropriete'
                )
                ->setParameter(
                    'natureDeLaPropriete',
                    (int) $natureDeLaPropriete
                );
        }

        $typesDePropriete = $this->normalizeArrayValue(
            $filters['typeDePropriete'] ?? [],
            [
                'id',
                'value',
                'code',
                'name',
                'label',
            ]
        );

        if ([] !== $typesDePropriete) {
            $qb
                ->andWhere(
                    'IDENTITY(p.typeBien) IN (:typesDePropriete)'
                )
                ->setParameter(
                    'typesDePropriete',
                    array_map(
                        'intval',
                        $typesDePropriete
                    )
                );
        }

        $pays = $this->normalizeCountryFilterValues(
            $filters['pays'] ?? [],
            $locale
        );

        $villes = $this->normalizeArrayValue(
            $filters['ville'] ?? [],
            [
                'city_name',
                'ville',
                'locality',
                'name',
                'label',
                'value',
                'postal_code',
                'postcode',
            ]
        );

        $quartiers = $this->normalizeArrayValue(
            $filters['quartier'] ?? [],
            [
                'district_name',
                'neighborhood',
                'quartier',
                'district',
                'name',
                'label',
                'value',
            ]
        );

        $this->addTextFilter(
            $qb,
            'pt.pays',
            'pays',
            $pays
        );

        $this->addTextFilter(
            $qb,
            'pt.ville',
            'ville',
            $villes
        );

        /*
         * Le quartier n'est pas porté par Property mais par sa traduction
         * (PropertyTranslation::$neighborhood / $district). On filtre donc
         * sur l'alias "pt" déjà joint, en acceptant l'un ou l'autre champ.
         */
        $this->addTranslationDistrictFilter($qb, $quartiers);

        if (
            $this
                ->getClassMetadata()
                ->hasField('chambres')
        ) {
            $this->addRangeFilter(
                $qb,
                'p.chambres',
                $filters,
                'minChambres',
                'maxChambres'
            );
        }

        if (
            $this
                ->getClassMetadata()
                ->hasField('salleDeBains')
        ) {
            $this->addRangeFilter(
                $qb,
                'p.salleDeBains',
                $filters,
                'minSallesDeBain',
                'maxSallesDeBain'
            );
        }

        if (
            $this
                ->getClassMetadata()
                ->hasField('surfaceTotal')
        ) {
            $this->addRangeFilter(
                $qb,
                'p.surfaceTotal',
                $filters,
                'minSurface',
                'maxSurface'
            );
        }

        if (
            $this
                ->getClassMetadata()
                ->hasField('anneeConstruction')
        ) {
            $this->addRangeFilter(
                $qb,
                'p.anneeConstruction',
                $filters,
                'minAnneeConstruction',
                'maxAnneeConstruction'
            );
        }

        if (
            $this
                ->getClassMetadata()
                ->hasField('prix')
            && $this
                ->getClassMetadata()
                ->hasField('montantLoyerHorsCharge')
        ) {
            $this->addRangeFilter(
                $qb,
                'COALESCE(
                    p.prix,
                    p.montantLoyerHorsCharge
                )',
                $filters,
                'minPrix',
                'maxPrix'
            );
        } elseif (
            $this
                ->getClassMetadata()
                ->hasField('prix')
        ) {
            $this->addRangeFilter(
                $qb,
                'p.prix',
                $filters,
                'minPrix',
                'maxPrix'
            );
        } elseif (
            $this
                ->getClassMetadata()
                ->hasField(
                    'montantLoyerHorsCharge'
                )
        ) {
            $this->addRangeFilter(
                $qb,
                'p.montantLoyerHorsCharge',
                $filters,
                'minPrix',
                'maxPrix'
            );
        }

        $dpe = $this->normalizeArrayValue(
            $filters['dpe'] ?? [],
            [
                'value',
                'label',
                'name',
                'code',
            ]
        );

        if (
            [] !== $dpe
            && $this
                ->getClassMetadata()
                ->hasField('dpeLettre')
        ) {
            $qb
                ->andWhere(
                    'UPPER(p.dpeLettre) IN (:dpe)'
                )
                ->setParameter(
                    'dpe',
                    array_map(
                        'strtoupper',
                        $dpe
                    )
                );
        }

        /*
         * Tri par nombre de visites (vues) de l'annonce.
         *
         * direction = DESC -> du plus vu au moins vu
         * direction = ASC  -> du moins vu au plus vu
         */
        if ('p.views' === $sort) {
            return $qb
                ->leftJoin(
                    'p.propertyViews',
                    'pv'
                )
                ->addSelect(
                    'COUNT(pv.id) AS HIDDEN viewsCount'
                )
                ->groupBy('p.id')
                ->orderBy(
                    'viewsCount',
                    $direction
                )
                ->addOrderBy('p.id', $direction);
        }

        /*
         * Tri par nombre de favoris de l'annonce.
         *
         * direction = DESC -> du plus de favoris au moins de favoris
         * direction = ASC  -> du moins de favoris au plus de favoris
         */
        if ('favorisCount' === $sort) {
            return $qb
                ->leftJoin(
                    Favoris::class,
                    'f',
                    'WITH',
                    'f.property = p'
                )
                ->addSelect(
                    'COUNT(f.id) AS HIDDEN favorisCount'
                )
                ->groupBy('p.id')
                ->orderBy(
                    'favorisCount',
                    $direction
                )
                ->addOrderBy('p.id', $direction);
        }

        /*
         * Tri par date de création de l'annonce.
         */
        if ('p.createdAt' === $sort) {
            return $qb
                ->orderBy('p.createdAt', $direction)
                ->addOrderBy('p.id', $direction);
        }

        /*
         * Tri par défaut : date de dernière modification de l'annonce.
         *
         * direction = DESC -> de la plus récemment modifiée à la plus ancienne
         * direction = ASC  -> de la plus ancienne à la plus récemment modifiée
         *
         * COALESCE : updatedAt peut être null sur d'anciennes données,
         * on retombe alors sur la date de création.
         */
        return $qb
            ->addSelect(
                'COALESCE(p.updatedAt, p.createdAt) AS HIDDEN lastModifiedAt'
            )
            ->orderBy('lastModifiedAt', $direction)
            ->addOrderBy('p.id', $direction);
    }

    /**
     * @param list<int> $propertyIds
     *
     * @return list<int>
     */
    public function findBoostedPropertyIds(array $propertyIds): array
    {
        if ([] === $propertyIds) {
            return [];
        }

        $rows = [];

        foreach (array_chunk($propertyIds, 500) as $propertyIdChunk) {
            $rows = [
                ...$rows,
                ...$this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('DISTINCT IDENTITY(pb.property) AS propertyId')
                    ->from(PropertyBoost::class, 'pb')
                    ->andWhere('pb.property IN (:propertyIds)')
                    ->andWhere('pb.status = :status')
                    ->setParameter('propertyIds', $propertyIdChunk)
                    ->setParameter('status', PropertyBoostStatus::ACTIVE->value)
                    ->getQuery()
                    ->getScalarResult(),
            ];
        }

        return array_values(array_unique(array_map('intval', array_column($rows, 'propertyId'))));
    }

    /**
     * Biens « À la Une » de la page d'accueil : annonces publiées dont le
     * boost est validé (statut ACTIF, non annulé) et dont on est aujourd'hui
     * dans la fenêtre [startsAt ; endsAt].
     *
     * Localisation à repli progressif — la langue (locale) est toujours
     * conservée :
     *   1. ville + pays exacts (comme les autres sections d'accueil) ;
     *   2. sinon, pays seul ;
     *   3. sinon, sans filtre géographique (un boost est une promotion
     *      payée : on préfère l'afficher partout plutôt que masquer le bloc).
     *
     * @return list<Property>
     */
    public function findActiveBoostedForHome(
        ?string $country,
        ?string $city,
        string $locale,
        int|string $transactionTypeId,
        ?float $latitude = null,
        ?float $longitude = null,
        int $limit = 10,
    ): array {
        // Géolocalisation navigateur autorisée : on affiche tous les biens
        // boostés du pays (déterminé via l'IP), triés du plus proche au plus
        // loin — pas de filtre ville, qui serait trop restrictif ici.
        if (null !== $latitude && null !== $longitude) {
            $results = $this->queryActiveBoostedForHome($country, null, $locale, $transactionTypeId);

            usort(
                $results,
                static fn (Property $a, Property $b): int => self::distanceToUserKm($a, $latitude, $longitude)
                    <=> self::distanceToUserKm($b, $latitude, $longitude)
            );

            return \array_slice($results, 0, $limit);
        }

        $hasCountry = null !== $country && '' !== mb_trim($country);
        $hasCity = null !== $city && '' !== mb_trim($city);

        // 1. Ville + pays exacts.
        $results = $this->queryActiveBoostedForHome($country, $city, $locale, $transactionTypeId);

        // 2. Repli : pays seul (uniquement si une ville avait été demandée).
        if ([] === $results && $hasCity && $hasCountry) {
            $results = $this->queryActiveBoostedForHome($country, null, $locale, $transactionTypeId);
        }

        // 3. Repli : sans filtre géographique (langue conservée).
        if ([] === $results && ($hasCountry || $hasCity)) {
            $results = $this->queryActiveBoostedForHome(null, null, $locale, $transactionTypeId);
        }

        shuffle($results);

        return \array_slice($results, 0, $limit);
    }

    /**
     * @return list<Property>
     */
    private function queryActiveBoostedForHome(
        ?string $country,
        ?string $city,
        string $locale,
        int|string $transactionTypeId,
    ): array {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->innerJoin('p.user', 'agency')
            ->addSelect('agency')
            ->leftJoin('agency.devise', 'currency')
            ->addSelect('currency')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->leftJoin('p.typeBien', 'typeBien')
            ->addSelect('typeBien')
            ->leftJoin('p.typeTransaction', 'typeTransaction')
            ->addSelect('typeTransaction')
            ->innerJoin(PropertyBoost::class, 'boost', 'WITH', 'boost.property = p')
            ->andWhere('p.statut = :statut')
            ->andWhere('pt.locale = :locale')
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->andWhere('boost.status = :boostStatus')
            ->andWhere('boost.canceledAt IS NULL')
            ->andWhere('boost.startsAt <= :now')
            ->andWhere('boost.endsAt >= :now')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE)
            ->setParameter('locale', $locale)
            ->setParameter('transactionTypeId', $transactionTypeId)
            ->setParameter('boostStatus', PropertyBoostStatus::ACTIVE->value)
            ->setParameter('now', $now);

        if (null !== $country && '' !== mb_trim($country)) {
            $qb
                ->andWhere('LOWER(pt.pays) = LOWER(:country)')
                ->setParameter('country', mb_trim($country));
        }

        if (null !== $city && '' !== mb_trim($city)) {
            $qb
                ->andWhere('LOWER(pt.ville) = LOWER(:city)')
                ->setParameter('city', mb_trim($city));
        }

        return $qb
            ->getQuery()
            ->getResult();
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
     * Un favori est un signal d'intérêt bien plus fort qu'une simple vue :
     * on le pondère pour calculer le "taux d'engagement" d'une annonce.
     */
    private const int FAVORI_ENGAGEMENT_WEIGHT = 5;

    /**
     * Poids plancher utilisé pour le tirage pondéré de {@see logementPopulaire()} :
     * un bien sans aucune vue ni favori garde une petite chance d'être tiré
     * (poids nul = probabilité nulle, ce qui l'exclurait définitivement).
     */
    private const int MIN_ENGAGEMENT_WEIGHT = 1;

    /**
     * Nombre maximum de candidats remontés de la base avant tri final en
     * PHP (engagement + distance). Les biens au-delà de ce rang n'ont de
     * toute façon aucune chance d'entrer dans le top affiché.
     */
    private const int MAX_ENGAGEMENT_CANDIDATES = 500;

    /**
     * Même logique que {@see MAX_ENGAGEMENT_CANDIDATES}, mais pour le tri
     * "récemment ajoutés" (date décroissante, distance en départage).
     */
    private const int MAX_RECENT_CANDIDATES = 500;

    private const float EARTH_RADIUS_KM = 6371.0;

    /**
     * Retourne les logements les plus populaires.
     *
     * Sélection : tirage aléatoire pondéré (sans remise) parmi les biens
     * publiés du pays (déterminé via l'IP) — un bien avec beaucoup de vues
     * et de favoris a statistiquement plus de chances d'être tiré, mais rien
     * n'est jamais garanti ni exclu. Voir {@see weightedRandomSample()}.
     */
    public function logementPopulaire(
        ?string $country,
        string $locale,
        int|string $id,
        int $limit = 10,
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.propertyViews', 'pv')
            ->leftJoin('p.translations', 'pt')
            ->innerJoin('p.user', 'agency')
            ->addSelect('agency')
            ->leftJoin('agency.devise', 'currency')
            ->addSelect('currency')
            ->leftJoin('p.typeBien', 'typeBien')
            ->addSelect('typeBien')
            ->leftJoin('p.typeTransaction', 'typeTransaction')
            ->addSelect('typeTransaction')
            ->leftJoin(Favoris::class, 'f', 'WITH', 'f.property = p')
            ->addSelect('COUNT(DISTINCT pv.id) AS viewsCount')
            ->addSelect('COUNT(DISTINCT f.id) AS favorisCount')
            ->andWhere('p.statut = :statut')
            ->andWhere('pt.locale = :locale')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE)
            ->setParameter('locale', $locale)
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->setParameter('transactionTypeId', $id)
            ->groupBy('p.id')
            ->setMaxResults(self::MAX_ENGAGEMENT_CANDIDATES);

        if (null !== $country && '' !== mb_trim($country)) {
            $qb
                ->andWhere('LOWER(pt.pays) = LOWER(:country)')
                ->setParameter('country', mb_trim($country));
        }

        $rows = $qb->getQuery()->getResult();

        return self::weightedRandomSample($rows, $limit);
    }

    /**
     * Tirage aléatoire pondéré sans remise (méthode de Efraimidis-Spirakis) :
     * chaque ligne reçoit une clé aléatoire dont l'exposant dépend de son
     * poids, puis on garde les $limit clés les plus hautes. Plus le poids
     * est élevé, plus la clé tend statistiquement vers 1 — sans qu'un bien
     * à faible engagement soit pour autant exclu d'office.
     *
     * @param list<array{0: Property, viewsCount: int|string, favorisCount: int|string}> $rows
     *
     * @return list<Property>
     */
    private static function weightedRandomSample(array $rows, int $limit): array
    {
        if ([] === $rows) {
            return [];
        }

        $keyedRows = array_map(
            static function (array $row): array {
                $weight = (int) $row['viewsCount'] + (int) $row['favorisCount'] * self::FAVORI_ENGAGEMENT_WEIGHT;
                $weight = max($weight, self::MIN_ENGAGEMENT_WEIGHT);

                $randomUnit = mt_rand(1, mt_getrandmax()) / mt_getrandmax();

                return [
                    'key' => $randomUnit ** (1 / $weight),
                    'property' => $row[0],
                ];
            },
            $rows
        );

        usort(
            $keyedRows,
            static fn (array $a, array $b): int => $b['key'] <=> $a['key']
        );

        return array_map(
            static fn (array $row): Property => $row['property'],
            \array_slice($keyedRows, 0, $limit)
        );
    }

    /**
     * Distance (km) entre un bien et l'utilisateur. Un bien sans
     * coordonnées est renvoyé en fin de classement (distance infinie)
     * plutôt qu'exclu du tri par engagement.
     */
    private static function distanceToUserKm(Property $property, float $latitude, float $longitude): float
    {
        $propertyLatitude = $property->getLatitude();
        $propertyLongitude = $property->getLongitude();

        if (null === $propertyLatitude || null === $propertyLongitude) {
            return \PHP_FLOAT_MAX;
        }

        return self::haversineDistanceKm(
            $latitude,
            $longitude,
            (float) $propertyLatitude,
            (float) $propertyLongitude
        );
    }

    private static function haversineDistanceKm(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2,
    ): float {
        $latitude1Rad = deg2rad($latitude1);
        $latitude2Rad = deg2rad($latitude2);
        $deltaLatitude = deg2rad($latitude2 - $latitude1);
        $deltaLongitude = deg2rad($longitude2 - $longitude1);

        $a = sin($deltaLatitude / 2) ** 2
            + cos($latitude1Rad) * cos($latitude2Rad) * sin($deltaLongitude / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Retourne les logements récemment ajoutés.
     *
     * Tri : date de mise à jour décroissante en priorité (le tri reste
     * chronologique, y compris avec géolocalisation) ; en cas d'égalité, le
     * bien le plus proche de l'utilisateur passe devant.
     *
     * Le pays (déterminé via l'IP) est toujours appliqué comme filtre. Si
     * aucune coordonnée n'est fournie (géolocalisation refusée /
     * indisponible), on filtre en plus sur la ville, trié uniquement en base
     * de données. Sinon, on élargit la recherche à tout le pays (pas de
     * filtre ville, potentiellement trop restrictif) puisqu'on dispose d'un
     * critère de proximité plus fiable.
     */
    public function logemntRecementAjouter(
        ?string $country,
        ?string $city,
        string $locale,
        int|string $id,
        ?float $latitude = null,
        ?float $longitude = null,
        int $limit = 10,
    ): array {
        $hasCoordinates = null !== $latitude && null !== $longitude;

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'pt')
            ->innerJoin('p.user', 'agency')
            ->addSelect('agency')
            ->leftJoin('agency.devise', 'currency')
            ->addSelect('currency')
            ->leftJoin('p.typeBien', 'typeBien')
            ->addSelect('typeBien')
            ->leftJoin('p.typeTransaction', 'typeTransaction')
            ->addSelect('typeTransaction')
            ->andWhere('p.statut = :statut')
            ->andWhere('pt.locale = :locale')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE)
            ->setParameter('locale', $locale)
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->setParameter('transactionTypeId', $id)
            ->orderBy('p.updatedAt', 'DESC');

        if (null !== $country && '' !== mb_trim($country)) {
            $qb
                ->andWhere('LOWER(pt.pays) = LOWER(:country)')
                ->setParameter('country', mb_trim($country));
        }

        if (!$hasCoordinates) {
            $qb->setMaxResults($limit);

            if (null !== $city && '' !== mb_trim($city)) {
                $qb
                    ->andWhere('LOWER(pt.ville) = LOWER(:city)')
                    ->setParameter('city', mb_trim($city));
            }

            return $qb
                ->getQuery()
                ->getResult();
        }

        $properties = $qb
            ->setMaxResults(self::MAX_RECENT_CANDIDATES)
            ->getQuery()
            ->getResult();

        usort(
            $properties,
            static function (Property $a, Property $b) use ($latitude, $longitude): int {
                $updatedAtA = $a->getUpdatedAt() ?? \DateTimeImmutable::createFromFormat('U', '0');
                $updatedAtB = $b->getUpdatedAt() ?? \DateTimeImmutable::createFromFormat('U', '0');

                if ($updatedAtA != $updatedAtB) {
                    return $updatedAtB <=> $updatedAtA;
                }

                return self::distanceToUserKm($a, $latitude, $longitude)
                    <=> self::distanceToUserKm($b, $latitude, $longitude);
            }
        );

        return \array_slice($properties, 0, $limit);
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
        float $west,
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

    public function countForPublicSearch(array $filters, ?string $locale = null): int
    {
        if (isset($filters['modal_filter']) && \is_array($filters['modal_filter'])) {
            $filters = $filters['modal_filter'];
        }

        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->leftJoin('p.translations', 'pt')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE);

        if (null !== $locale && '' !== mb_trim($locale)) {
            $qb
                ->andWhere('pt.locale = :locale')
                ->setParameter('locale', $locale);
        }

        $natureDeLaPropriete = $this->normalizeSingleValue(
            $filters['natureDeLaPropriete'] ?? null,
            [
                'id',
                'value',
                'code',
                'name',
                'label',
            ]
        );

        if (null !== $natureDeLaPropriete) {
            $qb
                ->andWhere('IDENTITY(p.typeTransaction) = :natureDeLaPropriete')
                ->setParameter('natureDeLaPropriete', (int) $natureDeLaPropriete);
        }

        $typesDePropriete = $this->normalizeArrayValue(
            $filters['typeDePropriete'] ?? [],
            [
                'id',
                'value',
                'code',
                'name',
                'label',
            ]
        );

        if ([] !== $typesDePropriete) {
            $qb
                ->andWhere('IDENTITY(p.typeBien) IN (:typesDePropriete)')
                ->setParameter('typesDePropriete', array_map('intval', $typesDePropriete));
        }

        $pays = $this->normalizeArrayValue(
            $filters['pays'] ?? [],
            [
                'country_name',
                'pays',
                'country',
                'label',
                'name',
                'value',
                'country_code',
                'code',
            ]
        );

        $villes = $this->normalizeArrayValue(
            $filters['ville'] ?? [],
            [
                'city_name',
                'ville',
                'locality',
                'name',
                'label',
                'value',
                'postal_code',
                'postcode',
            ]
        );

        $quartiers = $this->normalizeArrayValue(
            $filters['quartier'] ?? [],
            [
                'district_name',
                'neighborhood',
                'quartier',
                'district',
                'name',
                'label',
                'value',
            ]
        );

        $this->addTextFilter($qb, 'pt.pays', 'pays', $pays);
        $this->addTextFilter($qb, 'pt.ville', 'ville', $villes);

        if ([] !== $quartiers) {
            if ($this->getClassMetadata()->hasField('quartier')) {
                $this->addTextFilter($qb, 'p.quartier', 'quartier', $quartiers);
            } elseif ($this->getClassMetadata()->hasField('neighborhood')) {
                $this->addTextFilter($qb, 'p.neighborhood', 'quartier', $quartiers);
            } elseif ($this->getClassMetadata()->hasField('district')) {
                $this->addTextFilter($qb, 'p.district', 'quartier', $quartiers);
            }
        }

        if ($this->getClassMetadata()->hasField('chambres')) {
            $this->addRangeFilter($qb, 'p.chambres', $filters, 'minChambres', 'maxChambres');
        }

        if ($this->getClassMetadata()->hasField('salleDeBains')) {
            $this->addRangeFilter($qb, 'p.salleDeBains', $filters, 'minSallesDeBain', 'maxSallesDeBain');
        }

        if ($this->getClassMetadata()->hasField('surfaceTotal')) {
            $this->addRangeFilter($qb, 'p.surfaceTotal', $filters, 'minSurface', 'maxSurface');
        }

        if ($this->getClassMetadata()->hasField('anneeConstruction')) {
            $this->addRangeFilter($qb, 'p.anneeConstruction', $filters, 'minAnneeConstruction', 'maxAnneeConstruction');
        }

        if (
            $this->getClassMetadata()->hasField('prix')
            && $this->getClassMetadata()->hasField('montantLoyerHorsCharge')
        ) {
            $this->addRangeFilter(
                $qb,
                'COALESCE(p.prix, p.montantLoyerHorsCharge)',
                $filters,
                'minPrix',
                'maxPrix'
            );
        } elseif ($this->getClassMetadata()->hasField('prix')) {
            $this->addRangeFilter(
                $qb,
                'p.prix',
                $filters,
                'minPrix',
                'maxPrix'
            );
        } elseif ($this->getClassMetadata()->hasField('montantLoyerHorsCharge')) {
            $this->addRangeFilter(
                $qb,
                'p.montantLoyerHorsCharge',
                $filters,
                'minPrix',
                'maxPrix'
            );
        }

        $dpe = $this->normalizeArrayValue(
            $filters['dpe'] ?? [],
            [
                'value',
                'label',
                'name',
                'code',
            ]
        );

        if ([] !== $dpe && $this->getClassMetadata()->hasField('dpeLettre')) {
            $qb
                ->andWhere('UPPER(p.dpeLettre) IN (:dpe)')
                ->setParameter('dpe', array_map('strtoupper', $dpe));
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function normalizeCountryFilterValues(
        mixed $value,
        ?string $locale = null,
    ): array {
        if (
            null === $value
            || '' === $value
            || '[]' === $value
        ) {
            return [];
        }

        if (\is_string($value)) {
            $decodedValue = json_decode(
                $value,
                true
            );

            if (
                \JSON_ERROR_NONE === json_last_error()
                && \is_array($decodedValue)
            ) {
                $value = $decodedValue;
            } else {
                $value = [$value];
            }
        }

        if (!\is_array($value)) {
            $value = [$value];
        }

        $countries = [];

        foreach ($value as $country) {
            if (\is_scalar($country)) {
                $countryName = mb_trim(
                    (string) $country
                );

                if ('' !== $countryName) {
                    $countries[] = $countryName;
                }

                continue;
            }

            if (!\is_array($country)) {
                continue;
            }

            $countryCode = $country['country_code']
                ?? $country['code']
                ?? $country['raw']['country_code']
                ?? $country['raw']['code']
                ?? null;

            if (
                null !== $countryCode
                && '' !== mb_trim((string) $countryCode)
            ) {
                $countryCode = mb_strtoupper(
                    mb_trim((string) $countryCode)
                );

                foreach (['fr', 'en'] as $countryLocale) {
                    try {
                        $translatedCountryName = Countries::getName(
                            $countryCode,
                            $countryLocale
                        );

                        if (
                            null !== $translatedCountryName
                            && '' !== mb_trim($translatedCountryName)
                        ) {
                            $countries[] = mb_trim(
                                $translatedCountryName
                            );
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            $originalCountryName = $country['country_name']
                ?? $country['pays']
                ?? $country['country']
                ?? $country['label']
                ?? $country['name']
                ?? $country['value']
                ?? null;

            if (
                null !== $originalCountryName
                && '' !== mb_trim((string) $originalCountryName)
            ) {
                $countries[] = mb_trim(
                    (string) $originalCountryName
                );
            }
        }

        return array_values(
            array_unique(
                array_filter(
                    $countries,
                    static fn (string $country): bool => '' !== mb_trim($country)
                )
            )
        );
    }

    private function addTextFilter(
        QueryBuilder $qb,
        string $field,
        string $parameterPrefix,
        array $values,
    ): void {
        if ([] === $values) {
            return;
        }

        $orX = $qb->expr()->orX();

        foreach ($values as $index => $value) {
            $value = mb_trim((string) $value);

            if ('' === $value) {
                continue;
            }

            $parameterName = $parameterPrefix.'_'.$index;

            $orX->add(\sprintf('LOWER(%s) LIKE :%s', $field, $parameterName));

            $qb->setParameter(
                $parameterName,
                '%'.mb_strtolower($value).'%'
            );
        }

        if ($orX->count() > 0) {
            $qb->andWhere($orX);
        }
    }

    private function addRangeFilter(
        QueryBuilder $qb,
        string $field,
        array $filters,
        string $minKey,
        string $maxKey,
    ): void {
        $min = $this->normalizeSingleValue($filters[$minKey] ?? null);
        $max = $this->normalizeSingleValue($filters[$maxKey] ?? null);

        if (null !== $min) {
            $qb
                ->andWhere(\sprintf('%s >= :%s', $field, $minKey))
                ->setParameter($minKey, (float) $min);
        }

        if (null !== $max) {
            $qb
                ->andWhere(\sprintf('%s <= :%s', $field, $maxKey))
                ->setParameter($maxKey, (float) $max);
        }
    }

    private function normalizeSingleValue(mixed $value, array $preferredKeys = []): ?string
    {
        if (\is_array($value)) {
            $value = reset($value);
        }

        if (null === $value) {
            return null;
        }

        /*
         * Si la valeur est une string JSON, on essaye de la décoder.
         */
        if (\is_string($value)) {
            $value = mb_trim($value);

            $decoded = json_decode($value, true);

            if (\JSON_ERROR_NONE === json_last_error() && \is_array($decoded)) {
                $value = reset($decoded);
            }
        }

        /*
         * Si la valeur est encore un tableau ou un objet JSON décodé,
         * on extrait une valeur propre.
         */
        $value = $this->extractStringValue($value, $preferredKeys);

        if (null === $value) {
            return null;
        }

        $value = mb_trim($value);

        return '' === $value ? null : $value;
    }

    private function normalizeArrayValue(mixed $value, array $preferredKeys = []): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        /*
         * Cas JSON :
         *
         * [{"label":"France","code":"FR","country_name":"France"}]
         * [{"city_name":"Paris","country_name":"France"}]
         * [{"district_name":"Montorgueil","city_name":"Paris"}]
         */
        if (\is_string($value)) {
            $value = mb_trim($value);

            /*
             * Premier essai :
             * Symfony reçoit normalement déjà la valeur URL décodée.
             */
            $decoded = json_decode($value, true);

            if (\JSON_ERROR_NONE === json_last_error() && \is_array($decoded)) {
                $value = $decoded;
            } else {
                /*
                 * Deuxième essai :
                 * Sécurité si la valeur arrive encore encodée.
                 */
                $decodedValue = rawurldecode($value);
                $decoded = json_decode($decodedValue, true);

                if (\JSON_ERROR_NONE === json_last_error() && \is_array($decoded)) {
                    $value = $decoded;
                } else {
                    /*
                     * Fallback :
                     * France,Maroc,Espagne
                     */
                    $value = explode(',', $value);
                }
            }
        }

        if (!\is_array($value)) {
            $value = [$value];
        }

        $values = [];

        foreach ($value as $item) {
            $item = $this->extractStringValue($item, $preferredKeys);

            if (null === $item) {
                continue;
            }

            $item = mb_trim($item);

            if ('' === $item) {
                continue;
            }

            $values[] = $item;
        }

        return array_values(array_unique($values));
    }

    private function extractStringValue(mixed $item, array $preferredKeys = []): ?string
    {
        if (null === $item) {
            return null;
        }

        /*
         * Valeur simple :
         * "France", "Paris", "2", "A"
         */
        if (\is_scalar($item)) {
            return (string) $item;
        }

        if (!\is_array($item)) {
            return null;
        }

        /*
         * Ordre par défaut si aucune clé spécifique n'est envoyée.
         *
         * Attention :
         * Pour pays / ville / quartier, on envoie volontairement
         * un ordre différent depuis countForPublicSearch().
         */
        if ([] === $preferredKeys) {
            $preferredKeys = [
                'value',
                'label',
                'name',
                'city_name',
                'district_name',
                'neighborhood',
                'quartier',
                'ville',
                'pays',
                'country_name',
                'postal_code',
                'postcode',
                'code',
            ];
        }

        /*
         * Premier passage : niveau principal.
         *
         * Exemple ville :
         * [
         *     "city_name" => "Paris",
         *     "country_name" => "France"
         * ]
         *
         * Ici, si preferredKeys commence par city_name,
         * on récupère Paris et pas France.
         */
        foreach ($preferredKeys as $key) {
            if (isset($item[$key]) && \is_scalar($item[$key])) {
                return (string) $item[$key];
            }
        }

        /*
         * Deuxième passage : raw.xxx
         *
         * Exemple pays :
         * [
         *     "label" => "France",
         *     "raw" => [
         *         "country_name" => "France"
         *     ]
         * ]
         */
        if (isset($item['raw']) && \is_array($item['raw'])) {
            foreach ($preferredKeys as $key) {
                if (isset($item['raw'][$key]) && \is_scalar($item['raw'][$key])) {
                    return (string) $item['raw'][$key];
                }
            }
        }

        return null;
    }

    /**
     * Retourne les biens immobiliers correspondant aux filtres publics.
     *
     * @return array<int, Property>
     */
    public function findForPublicSearch(array $filters, ?string $locale = null): array
    {
        if (isset($filters['modal_filter']) && \is_array($filters['modal_filter'])) {
            $filters = $filters['modal_filter'];
        }

        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE);

        if (null !== $locale && '' !== mb_trim($locale)) {
            $qb
                ->andWhere('pt.locale = :locale')
                ->setParameter('locale', $locale);
        }

        $natureDeLaPropriete = $this->normalizeSingleValue(
            $filters['natureDeLaPropriete'] ?? null,
            [
                'id',
                'value',
                'code',
                'name',
                'label',
            ]
        );

        if (null !== $natureDeLaPropriete) {
            $qb
                ->andWhere('IDENTITY(p.typeTransaction) = :natureDeLaPropriete')
                ->setParameter('natureDeLaPropriete', (int) $natureDeLaPropriete);
        }

        $typesDePropriete = $this->normalizeArrayValue(
            $filters['typeDePropriete'] ?? [],
            [
                'id',
                'value',
                'code',
                'name',
                'label',
            ]
        );

        if ([] !== $typesDePropriete) {
            $qb
                ->andWhere('IDENTITY(p.typeBien) IN (:typesDePropriete)')
                ->setParameter('typesDePropriete', array_map('intval', $typesDePropriete));
        }

        $pays = $this->normalizeArrayValue(
            $filters['pays'] ?? [],
            [
                'country_name',
                'pays',
                'country',
                'label',
                'name',
                'value',
                'country_code',
                'code',
            ]
        );

        $villes = $this->normalizeArrayValue(
            $filters['ville'] ?? [],
            [
                'city_name',
                'ville',
                'locality',
                'name',
                'label',
                'value',
                'postal_code',
                'postcode',
            ]
        );

        $quartiers = $this->normalizeArrayValue(
            $filters['quartier'] ?? [],
            [
                'district_name',
                'neighborhood',
                'quartier',
                'district',
                'name',
                'label',
                'value',
            ]
        );

        $this->addTextFilter($qb, 'pt.pays', 'pays', $pays);
        $this->addTextFilter($qb, 'pt.ville', 'ville', $villes);

        if ([] !== $quartiers) {
            if ($this->getClassMetadata()->hasField('quartier')) {
                $this->addTextFilter($qb, 'p.quartier', 'quartier', $quartiers);
            } elseif ($this->getClassMetadata()->hasField('neighborhood')) {
                $this->addTextFilter($qb, 'p.neighborhood', 'quartier', $quartiers);
            } elseif ($this->getClassMetadata()->hasField('district')) {
                $this->addTextFilter($qb, 'p.district', 'quartier', $quartiers);
            }
        }

        if ($this->getClassMetadata()->hasField('chambres')) {
            $this->addRangeFilter($qb, 'p.chambres', $filters, 'minChambres', 'maxChambres');
        }

        if ($this->getClassMetadata()->hasField('salleDeBains')) {
            $this->addRangeFilter($qb, 'p.salleDeBains', $filters, 'minSallesDeBain', 'maxSallesDeBain');
        }

        if ($this->getClassMetadata()->hasField('surfaceTotal')) {
            $this->addRangeFilter($qb, 'p.surfaceTotal', $filters, 'minSurface', 'maxSurface');
        }

        if ($this->getClassMetadata()->hasField('anneeConstruction')) {
            $this->addRangeFilter($qb, 'p.anneeConstruction', $filters, 'minAnneeConstruction', 'maxAnneeConstruction');
        }

        if (
            $this->getClassMetadata()->hasField('prix')
            && $this->getClassMetadata()->hasField('montantLoyerHorsCharge')
        ) {
            $this->addRangeFilter(
                $qb,
                'COALESCE(p.prix, p.montantLoyerHorsCharge)',
                $filters,
                'minPrix',
                'maxPrix'
            );
        } elseif ($this->getClassMetadata()->hasField('prix')) {
            $this->addRangeFilter(
                $qb,
                'p.prix',
                $filters,
                'minPrix',
                'maxPrix'
            );
        } elseif ($this->getClassMetadata()->hasField('montantLoyerHorsCharge')) {
            $this->addRangeFilter(
                $qb,
                'p.montantLoyerHorsCharge',
                $filters,
                'minPrix',
                'maxPrix'
            );
        }

        $dpe = $this->normalizeArrayValue(
            $filters['dpe'] ?? [],
            [
                'value',
                'label',
                'name',
                'code',
            ]
        );

        if ([] !== $dpe && $this->getClassMetadata()->hasField('dpeLettre')) {
            $qb
                ->andWhere('UPPER(p.dpeLettre) IN (:dpe)')
                ->setParameter('dpe', array_map('strtoupper', $dpe));
        }

        return $qb
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Suggestions d'auto-complétion pour la barre de recherche libre de la
     * liste « Mes biens ».
     *
     * Les valeurs proposées proviennent exactement des colonnes interrogées
     * par le LIKE de {@see findPropertysByUserWithFiltersQuery()} : référence
     * interne, titre du logement, ville, pays et adresse des biens de
     * l'agence. Le slug (identifiant numérique) est volontairement exclu car
     * il n'a pas de sens comme suggestion.
     *
     * @return list<array{value: string, type: string}>
     */
    public function findAgencySearchSuggestions(
        User $user,
        ?string $query = null,
        ?string $locale = null,
        int $limit = 10,
    ): array {
        $needle = (null !== $query && '' !== mb_trim($query))
            ? mb_strtolower(mb_trim($query))
            : null;

        $qb = $this->agencyLocationBaseQuery($user, $locale)
            ->select(
                'p.referenceInterne AS reference',
                'pt.titreDuLogement AS titre',
                'pt.ville AS ville',
                'pt.pays AS pays',
                'pt.adresse AS adresse',
                'pt.fullAddress AS fullAddress'
            )
            ->setMaxResults(500);

        if (null !== $needle) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'LOWER(p.referenceInterne) LIKE :suggestQuery',
                        'LOWER(pt.titreDuLogement) LIKE :suggestQuery',
                        'LOWER(pt.ville) LIKE :suggestQuery',
                        'LOWER(pt.pays) LIKE :suggestQuery',
                        'LOWER(pt.adresse) LIKE :suggestQuery',
                        'LOWER(pt.fullAddress) LIKE :suggestQuery'
                    )
                )
                ->setParameter('suggestQuery', '%'.$needle.'%');
        }

        /*
         * fullAddress partage le même type d'affichage que adresse : on veut
         * une seule entrée « Adresse » par valeur distincte.
         */
        $fieldTypes = [
            'reference' => 'reference',
            'titre' => 'titre',
            'ville' => 'ville',
            'pays' => 'pays',
            'adresse' => 'adresse',
            'fullAddress' => 'adresse',
        ];

        $suggestions = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            foreach ($fieldTypes as $column => $type) {
                $value = mb_trim((string) ($row[$column] ?? ''));

                if ('' === $value) {
                    continue;
                }

                $lower = mb_strtolower($value);

                if (null !== $needle && !str_contains($lower, $needle)) {
                    continue;
                }

                if (isset($suggestions[$lower])) {
                    continue;
                }

                $suggestions[$lower] = [
                    'value' => $value,
                    'type' => $type,
                    'rank' => (null !== $needle && str_starts_with($lower, $needle)) ? 0 : 1,
                ];
            }
        }

        uasort(
            $suggestions,
            static fn (array $a, array $b): int => [$a['rank'], $a['value']] <=> [$b['rank'], $b['value']]
        );

        return array_values(
            array_map(
                static fn (array $item): array => [
                    'value' => $item['value'],
                    'type' => $item['type'],
                ],
                \array_slice($suggestions, 0, max(1, $limit))
            )
        );
    }

    /**
     * Pays distincts réellement saisis par l'agence sur ses propres biens.
     * Alimente l'auto-complétion du filtre « Localisation » de la page
     * « Mes biens ».
     *
     * @return list<string>
     */
    public function findAgencyFilterCountries(
        User $user,
        ?string $query = null,
        ?string $locale = null,
    ): array {
        $qb = $this->agencyLocationBaseQuery($user, $locale)
            ->select('DISTINCT pt.pays AS value')
            ->andWhere('pt.pays IS NOT NULL')
            ->andWhere("TRIM(pt.pays) <> ''")
            ->orderBy('pt.pays', 'ASC')
            ->setMaxResults(20);

        $this->applyLocationQueryLike($qb, 'pt.pays', $query);

        return $this->extractLocationValues($qb);
    }

    /**
     * Villes distinctes saisies par l'agence, éventuellement restreintes
     * à un pays.
     *
     * @return list<string>
     */
    public function findAgencyFilterCities(
        User $user,
        ?string $query = null,
        ?string $countryName = null,
        ?string $locale = null,
    ): array {
        $qb = $this->agencyLocationBaseQuery($user, $locale)
            ->select('DISTINCT pt.ville AS value')
            ->andWhere('pt.ville IS NOT NULL')
            ->andWhere("TRIM(pt.ville) <> ''")
            ->orderBy('pt.ville', 'ASC')
            ->setMaxResults(20);

        if (null !== $countryName && '' !== mb_trim($countryName)) {
            $qb
                ->andWhere('LOWER(pt.pays) LIKE :countryName')
                ->setParameter(
                    'countryName',
                    '%'.mb_strtolower(mb_trim($countryName)).'%'
                );
        }

        $this->applyLocationQueryLike($qb, 'pt.ville', $query);

        return $this->extractLocationValues($qb);
    }

    /**
     * Quartiers distincts saisis par l'agence (champ neighborhood ou
     * district de la traduction), éventuellement restreints à une ville.
     *
     * @return list<string>
     */
    public function findAgencyFilterDistricts(
        User $user,
        ?string $query = null,
        ?string $cityName = null,
        ?string $locale = null,
    ): array {
        $qb = $this->agencyLocationBaseQuery($user, $locale)
            ->select('pt.neighborhood AS neighborhood', 'pt.district AS district')
            ->setMaxResults(300);

        if (null !== $cityName && '' !== mb_trim($cityName)) {
            $qb
                ->andWhere('LOWER(pt.ville) LIKE :cityName')
                ->setParameter(
                    'cityName',
                    '%'.mb_strtolower(mb_trim($cityName)).'%'
                );
        }

        $needle = (null !== $query && '' !== mb_trim($query))
            ? mb_strtolower(mb_trim($query))
            : null;

        $values = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            foreach ([$row['neighborhood'] ?? null, $row['district'] ?? null] as $candidate) {
                $candidate = mb_trim((string) ($candidate ?? ''));

                if ('' === $candidate) {
                    continue;
                }

                if (null !== $needle && !str_contains(mb_strtolower($candidate), $needle)) {
                    continue;
                }

                $values[mb_strtolower($candidate)] = $candidate;
            }
        }

        ksort($values);

        return array_values(\array_slice($values, 0, 20));
    }

    private function agencyLocationBaseQuery(User $user, ?string $locale): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.translations', 'pt')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.statut IN (:statuts)')
            ->setParameter('statuts', self::MES_BIENS_LISTED_STATUTS);

        if (null !== $locale && '' !== mb_trim($locale)) {
            $qb
                ->andWhere('pt.locale = :locale')
                ->setParameter('locale', $locale);
        }

        return $qb;
    }

    private function applyLocationQueryLike(QueryBuilder $qb, string $field, ?string $query): void
    {
        if (null === $query || '' === mb_trim($query)) {
            return;
        }

        $qb
            ->andWhere(\sprintf('LOWER(%s) LIKE :locationQuery', $field))
            ->setParameter(
                'locationQuery',
                '%'.mb_strtolower(mb_trim($query)).'%'
            );
    }

    /**
     * @return list<string>
     */
    private function extractLocationValues(QueryBuilder $qb): array
    {
        $values = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $value = mb_trim((string) ($row['value'] ?? ''));

            if ('' !== $value) {
                $values[mb_strtolower($value)] = $value;
            }
        }

        return array_values($values);
    }

    /**
     * Filtre "quartier" pour la liste « Mes biens » : le champ est porté
     * par la traduction (pt.neighborhood / pt.district), pas par Property.
     *
     * @param list<string> $values
     */
    private function addTranslationDistrictFilter(QueryBuilder $qb, array $values): void
    {
        if ([] === $values) {
            return;
        }

        $orX = $qb->expr()->orX();

        foreach ($values as $index => $value) {
            $value = mb_trim((string) $value);

            if ('' === $value) {
                continue;
            }

            $parameterName = 'quartier_'.$index;

            $orX->add(\sprintf('LOWER(pt.neighborhood) LIKE :%s', $parameterName));
            $orX->add(\sprintf('LOWER(pt.district) LIKE :%s', $parameterName));

            $qb->setParameter(
                $parameterName,
                '%'.mb_strtolower($value).'%'
            );
        }

        if ($orX->count() > 0) {
            $qb->andWhere($orX);
        }
    }
}
