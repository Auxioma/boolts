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

namespace App\DataFixtures;

use App\Entity\AgencyProfileDailyVisit;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\BoosterPack;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Booster\PropertyBoost;
use App\Entity\Property;
use App\Entity\PropertyView;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Activité des agences : vues des annonces, visites du profil agence
 * et boosts (achat d'un pack puis boosts actifs et expirés).
 */
final class PropertyActivityFixtures extends Fixture implements DependentFixtureInterface
{
    private const MIN_PROPERTY_VIEWS = 15;
    private const MAX_PROPERTY_VIEWS = 180;
    private const MAX_DAILY_PROFILE_VISITS = 25;

    private const BOOSTER_PACK_CODE = 'boost-20';
    /**
     * L'accueil affiche les biens « À la Une » séparément pour la vente et la location.
     */
    private const ACTIVE_BOOSTS_PER_TRANSACTION_TYPE = 5;

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();
        $pack = $this->getReference(BoosterPackFixtures::BOOSTER_PACK_REFERENCE_PREFIX.self::BOOSTER_PACK_CODE, BoosterPack::class);

        for ($i = 1; $i <= UserFixtures::AGENCY_COUNT; ++$i) {
            $agency = $this->getReference(UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$i, User::class);
            $properties = $manager->getRepository(Property::class)->findBy(['user' => $agency]);

            foreach ($properties as $property) {
                if ($property->getPropertyViews()->isEmpty()) {
                    $this->addPropertyViews($manager, $property, $now);
                }
            }

            if (null === $manager->getRepository(AgencyProfileDailyVisit::class)->findOneBy(['agency' => $agency])) {
                $this->addProfileVisits($manager, $agency, $now);
            }

            if (null === $manager->getRepository(PropertyBoost::class)->findOneBy(['agency' => $agency])) {
                $this->addBoosts($manager, $agency, $properties, $pack, $now);
            }

            $manager->flush();
        }
    }

    private function addPropertyViews(ObjectManager $manager, Property $property, \DateTimeImmutable $now): void
    {
        $createdAt = $property->getCreatedAt() ?? $now;

        for ($n = random_int(self::MIN_PROPERTY_VIEWS, self::MAX_PROPERTY_VIEWS); $n > 0; --$n) {
            $visitorHash = hash('sha256', 'fixture-visitor:'.bin2hex(random_bytes(16)));
            $viewedAt = self::randomDateBetween($createdAt, $now);

            $view = (new PropertyView())
                ->setProperty($property)
                ->setVisitorHash($visitorHash)
                ->setViewKey(hash('sha256', implode('|', ['property', $property->getId(), $visitorHash, $viewedAt->format('Y-m-d')])))
                ->setViewedAt($viewedAt);

            $property->addPropertyView($view);
            $manager->persist($view);
        }
    }

    private function addProfileVisits(ObjectManager $manager, User $agency, \DateTimeImmutable $now): void
    {
        $day = $now->modify('-6 months')->setTime(0, 0);
        $today = $now->setTime(0, 0);

        for (; $day <= $today; $day = $day->modify('+1 day')) {
            $visits = random_int(0, self::MAX_DAILY_PROFILE_VISITS);

            if (0 === $visits) {
                continue;
            }

            $manager->persist(
                (new AgencyProfileDailyVisit())
                    ->setAgency($agency)
                    ->setViewedOn($day)
                    ->setVisits($visits)
            );
        }
    }

    /**
     * Achat d'un pack, puis consommation de crédits : des boosts actifs
     * pour la vente et pour la location.
     *
     * @param list<Property> $properties
     */
    private function addBoosts(ObjectManager $manager, User $agency, array $properties, BoosterPack $pack, \DateTimeImmutable $now): void
    {
        $durationDays = $pack->getBoostDurationDays();

        $manager->persist(
            (new BoosterTransaction())
                ->setAgency($agency)
                ->setQuantity($pack->getBoostQuantity())
                ->setType(BoosterTransactionType::PACK_PURCHASE)
                ->setBoosterPack($pack)
                ->setIdempotencyKey(\sprintf('fixture-pack-purchase-%d', $agency->getId()))
                ->setDescription(\sprintf('Achat du pack %s.', $pack->getName()))
        );

        shuffle($properties);

        $propertiesByTransactionType = [];

        foreach ($properties as $property) {
            $propertiesByTransactionType[$property->getTypeTransaction()?->getId()][] = $property;
        }

        foreach ($propertiesByTransactionType as $sameTypeProperties) {
            foreach (\array_slice($sameTypeProperties, 0, self::ACTIVE_BOOSTS_PER_TRANSACTION_TYPE) as $property) {
                $startsAt = self::randomDateBetween(
                    max($property->getCreatedAt() ?? $now, $now->modify(\sprintf('-%d days', $durationDays - 1))),
                    $now,
                );
                $this->addBoost($manager, $agency, $property, $pack, $startsAt, $durationDays, PropertyBoostStatus::ACTIVE);
            }
        }
    }

    private function addBoost(
        ObjectManager $manager,
        User $agency,
        Property $property,
        BoosterPack $pack,
        \DateTimeImmutable $startsAt,
        int $durationDays,
        PropertyBoostStatus $status,
    ): void {
        $endsAt = $startsAt->modify(\sprintf('+%d days', $durationDays));

        $transaction = (new BoosterTransaction())
            ->setAgency($agency)
            ->setProperty($property)
            ->setQuantity(-1)
            ->setType(BoosterTransactionType::PROPERTY_BOOST)
            ->setBoosterPack($pack)
            ->setExpiresAt($endsAt)
            ->setIdempotencyKey(\sprintf('fixture-property-boost-%d-%s', $property->getId(), bin2hex(random_bytes(6))))
            ->setDescription(\sprintf('Boost de l’annonce #%d pour %d jour(s).', $property->getId(), $durationDays));

        $boost = (new PropertyBoost())
            ->setProperty($property)
            ->setAgency($agency)
            ->setBoosterTransaction($transaction)
            ->setStatus($status)
            ->setStartsAt($startsAt)
            ->setEndsAt($endsAt);

        $manager->persist($transaction);
        $manager->persist($boost);
    }

    private static function randomDateBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): \DateTimeImmutable
    {
        return $from->setTimestamp(random_int($from->getTimestamp(), max($from->getTimestamp(), $to->getTimestamp())));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PropertyFixtures::class,
            BoosterPackFixtures::class,
        ];
    }
}
