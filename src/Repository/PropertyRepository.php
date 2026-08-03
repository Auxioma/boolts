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

use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\PropertyBoost;
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

    /**
     * Retourne une requête pour récupérer les biens immobiliers d’un utilisateur donné.
     * Cette méthode est utilisée pour la pagination dans le contrôleur DetailAgenceController.
     * De plus, il y aura les filtre de recherche à ajouter dans cette requête.
     * par default, elle retourne tous les biens de l’utilisateur sans filtre en ASC.
     */
    public function findPropertysByUserQuery(
        User $user,
        string $search = null,
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

    public function findForDashboardPerformanceQuery(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.propertyImages', 'pi')
            ->addSelect('pi')
            ->innerJoin('p.user', 'u')
            ->addSelect('u')
            ->leftJoin('u.devise', 'currency')
            ->addSelect('currency')
            ->andWhere('p.user = :user')
            ->andWhere('p.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', StatutAnnonceImmobiliere::PUBLIEE)
            ->orderBy('p.createdAt', 'DESC');
    }

    public function findPropertysByUserWithFiltersQuery(
    User $user,
    ?string $search = null,
    array $filters = [],
    string $sort = 'p.createdAt',
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

        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.statut = :statut')
            ->setParameter(
                'statut',
                StatutAnnonceImmobiliere::PUBLIEE
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
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'LOWER(p.referenceInterne) LIKE :search',
                        'LOWER(pt.titreDuLogement) LIKE :search',
                        'LOWER(pt.ville) LIKE :search'
                    )
                )
                ->setParameter(
                    'search',
                    '%'.mb_strtolower(
                        mb_trim($search)
                    ).'%'
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

        if ([] !== $quartiers) {
            if (
                $this
                    ->getClassMetadata()
                    ->hasField('quartier')
            ) {
                $this->addTextFilter(
                    $qb,
                    'p.quartier',
                    'quartier',
                    $quartiers
                );
            } elseif (
                $this
                    ->getClassMetadata()
                    ->hasField('neighborhood')
            ) {
                $this->addTextFilter(
                    $qb,
                    'p.neighborhood',
                    'quartier',
                    $quartiers
                );
            } elseif (
                $this
                    ->getClassMetadata()
                    ->hasField('district')
            ) {
                $this->addTextFilter(
                    $qb,
                    'p.district',
                    'quartier',
                    $quartiers
                );
            }
        }

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
                );
        }

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
                );
        }

        return $qb->orderBy(
            'p.createdAt',
            $direction
        );
    }

    /**
     * @param list<int> $propertyIds
     * @return list<int>
     */
    public function findBoostedPropertyIds(array $propertyIds): array
    {
        if ([] === $propertyIds) {
            return [];
        }

        $rows = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT IDENTITY(pb.property) AS propertyId')
            ->from(PropertyBoost::class, 'pb')
            ->andWhere('pb.property IN (:propertyIds)')
            ->andWhere('pb.status IN (:statuses)')
            ->setParameter('propertyIds', $propertyIds)
            ->setParameter('statuses', [
                PropertyBoostStatus::ACTIVE->value,
                PropertyBoostStatus::SCHEDULED->value,
            ])
            ->getQuery()
            ->getScalarResult();

        return array_map('intval', array_column($rows, 'propertyId'));
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
    /**
     * Retourne les logements les plus populaires.
     */
    public function logementPopulaire(
        ?string $country,
        ?string $city,
        string $locale,
        int|string $id,
    ): array {
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
     * Logement Ajouter Ressament, filtré par la date de update.
     */
    /**
     * Retourne les logements récemment ajoutés.
     */
    public function logemntRecementAjouter(
        ?string $country,
        ?string $city,
        string $locale,
        int|string $id,
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'pt')
            ->andWhere('pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->andWhere('IDENTITY(p.typeTransaction) = :transactionTypeId')
            ->setParameter('transactionTypeId', $id)
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(10);

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
}
