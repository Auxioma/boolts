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

namespace App\Service\Booster;

use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Booster\PropertyBoost;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionPeriodRepository;
use App\Repository\Booster\BoosterTransactionRepository;
use App\Repository\Booster\PropertyBoostRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Applique un boost sur une annonce en consommant un crédit de boost.
 *
 * Règles métier :
 *  - seules les annonces publiées ou en pause (dépubliées) sont éligibles ;
 *  - une annonce déjà boostée ne peut pas l'être une seconde fois ;
 *  - les boosts inclus dans le forfait sont consommés en priorité, puis les
 *    boosts achetés indépendamment ;
 *  - la durée du boost dépend de sa source : durée du forfait pour un boost
 *    forfait, durée du pack pour un boost acheté.
 */
final class PropertyBoostService
{
    /**
     * Durée de repli (en jours) lorsqu'aucune source ne fournit de durée.
     */
    private const FALLBACK_DURATION_DAYS = 7;

    /**
     * @var list<StatutAnnonceImmobiliere>
     */
    private const ELIGIBLE_STATUSES = [
        StatutAnnonceImmobiliere::PUBLIEE,
        StatutAnnonceImmobiliere::DEPUBLIEE,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BoosterTransactionRepository $boosterTransactionRepository,
        private readonly PropertyBoostRepository $propertyBoostRepository,
        private readonly AgencySubscriptionPeriodRepository $subscriptionPeriodRepository,
    ) {
    }

    /**
     * Décrit le boost qui serait appliqué : solde disponible, source
     * (forfait en priorité) et durée. Sert à alimenter la modal de
     * confirmation avant validation définitive.
     *
     * @return array{
     *     available: int,
     *     availableSubscription: int,
     *     availableIndependent: int,
     *     source: 'subscription'|'independent'|null,
     *     durationDays: int|null
     * }
     */
    public function preview(User $agency, ?\DateTimeImmutable $now = null): array
    {
        $now ??= new \DateTimeImmutable();

        $balance = $this->boosterTransactionRepository->countAvailableBySourceForAgency($agency, $now);

        if ($balance['total'] < 1) {
            return [
                'available' => 0,
                'availableSubscription' => $balance['subscription'],
                'availableIndependent' => $balance['independent'],
                'source' => null,
                'durationDays' => null,
            ];
        }

        [$sourcePeriod, , $durationDays] = $this->resolveSource($agency, $balance, $now);

        return [
            'available' => $balance['total'],
            'availableSubscription' => $balance['subscription'],
            'availableIndependent' => $balance['independent'],
            'source' => null !== $sourcePeriod ? 'subscription' : 'independent',
            'durationDays' => $durationDays,
        ];
    }

    /**
     * @throws BoostException si le boost ne peut pas être appliqué
     */
    public function boost(Property $property, User $agency, ?\DateTimeImmutable $now = null): PropertyBoost
    {
        $now ??= new \DateTimeImmutable();

        if ($property->getUser()?->getId() !== $agency->getId()) {
            throw new BoostException('Vous ne pouvez pas booster cette annonce.');
        }

        if (!\in_array($property->getStatut(), self::ELIGIBLE_STATUSES, true)) {
            throw new BoostException('Seules les annonces publiées ou en pause peuvent être boostées.');
        }

        if ($this->propertyBoostRepository->hasActiveBoost($property, $now)) {
            throw new BoostException('Cette annonce est déjà boostée.');
        }

        $balance = $this->boosterTransactionRepository->countAvailableBySourceForAgency($agency, $now);

        if ($balance['total'] < 1) {
            throw new BoostException('Vous n’avez plus de boost disponible. Achetez un pack boost pour en obtenir.');
        }

        [$sourcePeriod, $sourcePack, $durationDays] = $this->resolveSource($agency, $balance, $now);

        $startsAt = $now;
        $endsAt = $now->modify(\sprintf('+%d days', $durationDays));

        $transaction = (new BoosterTransaction())
            ->setAgency($agency)
            ->setProperty($property)
            ->setQuantity(-1)
            ->setType(BoosterTransactionType::PROPERTY_BOOST)
            ->setBoosterPack($sourcePack)
            ->setSubscriptionPeriod($sourcePeriod)
            ->setExpiresAt($endsAt)
            ->setIdempotencyKey(\sprintf(
                'property-boost-%d-%s',
                $property->getId(),
                bin2hex(random_bytes(6)),
            ))
            ->setDescription(\sprintf(
                'Boost de l’annonce #%d pour %d jour(s).',
                $property->getId(),
                $durationDays,
            ));

        $boost = (new PropertyBoost())
            ->setProperty($property)
            ->setAgency($agency)
            ->setBoosterTransaction($transaction)
            ->setStatus(PropertyBoostStatus::ACTIVE)
            ->setStartsAt($startsAt)
            ->setEndsAt($endsAt);

        /*
         * Une annonce en pause qui est boostée est automatiquement
         * republiée : le boost n'a de sens que sur une annonce visible.
         */
        if (StatutAnnonceImmobiliere::DEPUBLIEE === $property->getStatut()) {
            $property->setStatut(StatutAnnonceImmobiliere::PUBLIEE);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($boost);
        $this->entityManager->flush();

        return $boost;
    }

    /**
     * Détermine la source du boost (forfait en priorité) et sa durée.
     *
     * @param array{subscription: int, independent: int, total: int} $balance
     *
     * @return array{0: ?\App\Entity\Billing\AgencySubscriptionPeriod, 1: ?\App\Entity\Booster\BoosterPack, 2: int}
     */
    private function resolveSource(User $agency, array $balance, \DateTimeImmutable $now): array
    {
        if ($balance['subscription'] >= 1) {
            $period = $this->subscriptionPeriodRepository->findActiveForAgency($agency, $now);

            if (null !== $period) {
                $durationDays = max(
                    1,
                    $period->getSubscription()->getBoostDurationDaysSnapshot(),
                );

                return [$period, null, $durationDays];
            }
        }

        $packCredit = $this->boosterTransactionRepository
            ->findEarliestExpiringReusablePackCredit($agency, $now);
        $pack = $packCredit?->getBoosterPack();

        $durationDays = max(
            1,
            $pack?->getBoostDurationDays() ?? self::FALLBACK_DURATION_DAYS,
        );

        return [null, $pack, $durationDays];
    }
}
