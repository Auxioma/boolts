<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\PaymentStatus;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\Payment;
use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Booster\BoosterPack;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Booster\PropertyBoost;
use App\Entity\Devise;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class PropertyBoostFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROPERTY_BOOST_REFERENCE_PREFIX = 'property_boost_';

    private const STATUS_COUNTS = [
        PropertyBoostStatus::ACTIVE->value => 60,
        PropertyBoostStatus::SCHEDULED->value => 15,
        PropertyBoostStatus::EXPIRED->value => 25,
        PropertyBoostStatus::CANCELED->value => 10,
    ];

    private const BOOSTER_PACK_AMOUNTS = [
        'boost-1' => 2499,
        'boost-5' => 9999,
        'boost-20' => 29999,
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(20260731);

        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les boosts de biens.');
        }

        $subscriptionPlan = $this->getReference(
            SubscriptionPlanFixtures::SUBSCRIPTION_PLAN_REFERENCE_PREFIX.'pro',
            SubscriptionPlan::class,
        );
        $boosterPacks = $this->boosterPacks();
        $billingProfiles = [];
        $subscriptionSources = [];

        /** @var list<Property> $properties */
        $properties = $manager->getRepository(Property::class)->findBy([
            'statut' => StatutAnnonceImmobiliere::PUBLIEE,
        ]);
        $properties = array_values(array_filter(
            $properties,
            static fn (Property $property): bool => null !== $property->getUser(),
        ));

        $requiredProperties = array_sum(self::STATUS_COUNTS);

        if (count($properties) < $requiredProperties) {
            throw new \RuntimeException(sprintf(
                'Au moins %d annonces publiées avec une agence sont nécessaires pour charger les boosts.',
                $requiredProperties,
            ));
        }

        $properties = $faker->randomElements($properties, $requiredProperties);
        $boostIndex = 1;

        foreach (self::STATUS_COUNTS as $statusValue => $count) {
            $status = PropertyBoostStatus::from($statusValue);

            for ($index = 0; $index < $count; ++$index) {
                /** @var Property $property */
                $property = array_shift($properties);
                $agency = $property->getUser();

                if (null === $agency) {
                    throw new \LogicException('Chaque boost doit être lié à l’agence propriétaire du bien.');
                }

                [$startsAt, $endsAt, $canceledAt] = $this->datesForStatus($status, $faker);
                $agencyId = $agency->getId();

                if (null === $agencyId) {
                    throw new \LogicException('L’agence propriétaire doit être persistée avant ses boosts.');
                }

                if (!isset($billingProfiles[$agencyId])) {
                    $billingProfiles[$agencyId] = $this->createBillingProfile($agency, $currency);
                    $manager->persist($billingProfiles[$agencyId]);
                }

                $boosterPack = null;
                $subscriptionPeriod = null;
                $payment = null;

                if (0 === $boostIndex % 2) {
                    if (!isset($subscriptionSources[$agencyId])) {
                        $subscriptionSources[$agencyId] = $this->createSubscriptionSource(
                            $agency,
                            $billingProfiles[$agencyId],
                            $subscriptionPlan,
                            $currency,
                            $boostIndex,
                        );
                        $manager->persist($subscriptionSources[$agencyId][0]->getSubscription());
                        $manager->persist($subscriptionSources[$agencyId][1]);
                        $manager->persist($subscriptionSources[$agencyId][0]);
                    }

                    [$subscriptionPeriod, $payment] = $subscriptionSources[$agencyId];
                } else {
                    $packCode = $faker->randomElement(array_keys(self::BOOSTER_PACK_AMOUNTS));
                    $boosterPack = $boosterPacks[$packCode];
                    $payment = $this->createPackPayment(
                        $agency,
                        $billingProfiles[$agencyId],
                        $boosterPack,
                        $currency,
                        $boostIndex,
                    );
                    $manager->persist($payment);
                }

                $transaction = (new BoosterTransaction())
                    ->setAgency($agency)
                    ->setProperty($property)
                    ->setQuantity(-1)
                    ->setType(BoosterTransactionType::PROPERTY_BOOST)
                    ->setBoosterPack($boosterPack)
                    ->setSubscriptionPeriod($subscriptionPeriod)
                    ->setPayment($payment)
                    ->setExpiresAt($endsAt)
                    ->setIdempotencyKey(sprintf('fixture-property-boost-%03d', $boostIndex))
                    ->setDescription('Utilisation d’un boost sur une annonce.');

                $boost = (new PropertyBoost())
                    ->setProperty($property)
                    ->setAgency($agency)
                    ->setBoosterTransaction($transaction)
                    ->setStatus($status)
                    ->setStartsAt($startsAt)
                    ->setEndsAt($endsAt)
                    ->setCanceledAt($canceledAt);

                $manager->persist($transaction);
                $manager->persist($boost);
                $this->addReference(self::PROPERTY_BOOST_REFERENCE_PREFIX.$boostIndex, $boost);

                ++$boostIndex;
            }
        }

        $manager->flush();
    }

    /**
     * @return array<string, BoosterPack>
     */
    private function boosterPacks(): array
    {
        $packs = [];

        foreach (array_keys(self::BOOSTER_PACK_AMOUNTS) as $packCode) {
            $packs[$packCode] = $this->getReference(
                BoosterPackFixtures::BOOSTER_PACK_REFERENCE_PREFIX.$packCode,
                BoosterPack::class,
            );
        }

        return $packs;
    }

    private function createBillingProfile(User $agency, Devise $currency): AgencyBillingProfile
    {
        return (new AgencyBillingProfile())
            ->setAgency($agency)
            ->setPreferredCurrency($currency)
            ->setBillingEmail($agency->getEmail());
    }

    /**
     * @return array{0: AgencySubscriptionPeriod, 1: Payment}
     */
    private function createSubscriptionSource(
        User $agency,
        AgencyBillingProfile $billingProfile,
        SubscriptionPlan $plan,
        Devise $currency,
        int $boostIndex,
    ): array {
        $periodStart = new \DateTimeImmutable('-15 days');
        $periodEnd = new \DateTimeImmutable('+15 days');

        $subscription = (new AgencySubscription())
            ->setAgency($agency)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartedAt($periodStart)
            ->setCurrentPeriodStart($periodStart)
            ->setCurrentPeriodEnd($periodEnd)
            ->setPropertyLimitSnapshot($plan->getPropertyLimit())
            ->setIncludedBoostsSnapshot($plan->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($plan->getBoostDurationDays())
            ->setAmountSnapshotMinor(4990)
            ->setCurrencySnapshot($currency);

        $payment = (new Payment())
            ->setReference(sprintf('FIXTURE-SUB-%03d', $boostIndex))
            ->setAgency($agency)
            ->setBillingProfile($billingProfile)
            ->setSubscription($subscription)
            ->setType(PaymentType::SUBSCRIPTION_RENEWAL)
            ->setStatus(PaymentStatus::SUCCEEDED)
            ->setAmountSubtotalMinor(4990)
            ->setAmountTotalMinor(4990)
            ->setAmountPaidMinor(4990)
            ->setCurrency($currency)
            ->setPaidAt($periodStart);

        $period = (new AgencySubscriptionPeriod())
            ->setSubscription($subscription)
            ->setPeriodStart($periodStart)
            ->setPeriodEnd($periodEnd)
            ->setPropertyLimit($plan->getPropertyLimit())
            ->setIncludedBoosts($plan->getIncludedBoosts())
            ->setAmountMinor(4990)
            ->setCurrency($currency)
            ->setPayment($payment)
            ->setStatus(SubscriptionPeriodStatus::PAID);

        return [$period, $payment];
    }

    private function createPackPayment(
        User $agency,
        AgencyBillingProfile $billingProfile,
        BoosterPack $boosterPack,
        Devise $currency,
        int $boostIndex,
    ): Payment {
        $amountMinor = self::BOOSTER_PACK_AMOUNTS[$boosterPack->getCode()];

        return (new Payment())
            ->setReference(sprintf('FIXTURE-BOOST-%03d', $boostIndex))
            ->setAgency($agency)
            ->setBillingProfile($billingProfile)
            ->setBoosterPack($boosterPack)
            ->setType(PaymentType::BOOSTER_PACK)
            ->setStatus(PaymentStatus::SUCCEEDED)
            ->setAmountSubtotalMinor($amountMinor)
            ->setAmountTotalMinor($amountMinor)
            ->setAmountPaidMinor($amountMinor)
            ->setCurrency($currency)
            ->setPaidAt(new \DateTimeImmutable('-1 day'));
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable, 2: ?\DateTimeImmutable}
     */
    private function datesForStatus(PropertyBoostStatus $status, \Faker\Generator $faker): array
    {
        return match ($status) {
            PropertyBoostStatus::ACTIVE => [
                $startsAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-14 days', '-1 day')),
                $startsAt->modify(sprintf('+%d days', $faker->numberBetween(15, 30))),
                null,
            ],
            PropertyBoostStatus::SCHEDULED => [
                $startsAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('+1 day', '+14 days')),
                $startsAt->modify(sprintf('+%d days', $faker->numberBetween(7, 30))),
                null,
            ],
            PropertyBoostStatus::EXPIRED => [
                $endsAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-30 days', '-1 day')),
                $endsAt->modify(sprintf('-%d days', $faker->numberBetween(7, 30))),
                $endsAt,
                null,
            ],
            PropertyBoostStatus::CANCELED => [
                $canceledAt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-14 days', '-1 day')),
                $startsAt = $canceledAt->modify(sprintf('-%d days', $faker->numberBetween(1, 7))),
                $endsAt = $startsAt->modify(sprintf('+%d days', $faker->numberBetween(7, 30))),
                $canceledAt,
            ],
        };
    }

    public function getDependencies(): array
    {
        return [
            PropertyFixtures::class,
            PaysFixtures::class,
            BoosterPackFixtures::class,
            SubscriptionPlanFixtures::class,
        ];
    }
}
