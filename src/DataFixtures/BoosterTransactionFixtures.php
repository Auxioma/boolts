<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Payment;
use App\Entity\Booster\BoosterPack;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class BoosterTransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public const BOOSTER_TRANSACTION_REFERENCE_PREFIX = 'booster_transaction_';
    public const PROPERTY_BOOST_TRANSACTION_REFERENCE_PREFIX = 'booster_transaction_property_boost_';

    public function load(ObjectManager $manager): void
    {
        $agencyKeysById = $this->agencyKeysById();
        $packCodes = array_keys(BillingFixtureData::BOOSTER_PRICES);

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $agency = $this->getReference(BillingFixtureData::agencyReferences()[$agencyKey], User::class);
            $packCode = $packCodes[$position % count($packCodes)];
            $pack = $this->getReference(
                BoosterPackFixtures::BOOSTER_PACK_REFERENCE_PREFIX.$packCode,
                BoosterPack::class,
            );
            $payment = $this->getReference(BillingFixtureData::boosterPaymentReference($agencyKey), Payment::class);

            $packPurchase = (new BoosterTransaction())
                ->setAgency($agency)
                ->setQuantity($pack->getBoostQuantity())
                ->setType(BoosterTransactionType::PACK_PURCHASE)
                ->setBoosterPack($pack)
                ->setPayment($payment)
                ->setExpiresAt((new \DateTimeImmutable())->modify(sprintf('+%d days', $pack->getBoostDurationDays())))
                ->setIdempotencyKey('fixture-pack-purchase-'.$agencyKey)
                ->setDescription('Achat fixture du pack boost '.$pack->getName());

            $manager->persist($packPurchase);
            $this->addReference(self::BOOSTER_TRANSACTION_REFERENCE_PREFIX.'pack_purchase_'.$agencyKey, $packPurchase);

            if ('free' !== BillingFixtureData::agencyPlanCode($position)) {
                $period = $this->getReference(
                    BillingFixtureData::subscriptionPeriodReference($agencyKey),
                    AgencySubscriptionPeriod::class,
                );

                $subscriptionCredit = (new BoosterTransaction())
                    ->setAgency($agency)
                    ->setQuantity($period->getIncludedBoosts())
                    ->setType(BoosterTransactionType::SUBSCRIPTION_CREDIT)
                    ->setSubscriptionPeriod($period)
                    ->setExpiresAt($period->getPeriodEnd())
                    ->setIdempotencyKey('fixture-subscription-credit-'.$agencyKey)
                    ->setDescription('Boosts inclus dans le forfait fixture.');

                $manager->persist($subscriptionCredit);
                $this->addReference(self::BOOSTER_TRANSACTION_REFERENCE_PREFIX.'subscription_credit_'.$agencyKey, $subscriptionCredit);
            }
        }

        $this->createPropertyBoostTransactions($manager, $agencyKeysById);
        $this->createAdministrativeTransactions($manager);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PropertyFixtures::class,
            BoosterPackFixtures::class,
            PaymentFixtures::class,
            AgencySubscriptionPeriodFixtures::class,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function agencyKeysById(): array
    {
        $agencyKeysById = [];

        foreach (BillingFixtureData::agencyReferences() as $agencyKey => $agencyReference) {
            $agency = $this->getReference($agencyReference, User::class);
            $agencyId = $agency->getId();

            if (null !== $agencyId) {
                $agencyKeysById[$agencyId] = $agencyKey;
            }
        }

        return $agencyKeysById;
    }

    /**
     * @param array<int, string> $agencyKeysById
     */
    private function createPropertyBoostTransactions(ObjectManager $manager, array $agencyKeysById): void
    {
        $requiredCount = array_sum(BillingFixtureData::PROPERTY_BOOST_STATUS_COUNTS);
        $properties = $manager->getRepository(Property::class)->findBy([
            'statut' => StatutAnnonceImmobiliere::PUBLIEE,
        ]);
        $properties = array_values(array_filter(
            $properties,
            static function (Property $property) use ($agencyKeysById): bool {
                $agencyId = $property->getUser()?->getId();

                return null !== $agencyId && isset($agencyKeysById[$agencyId]);
            },
        ));

        if (count($properties) < $requiredCount) {
            throw new \RuntimeException(sprintf(
                'Au moins %d annonces publiées liées aux agences fixtures sont nécessaires pour charger les boosts de biens.',
                $requiredCount,
            ));
        }

        $boostIndex = 1;

        foreach (BillingFixtureData::PROPERTY_BOOST_STATUS_COUNTS as $status => $count) {
            for ($index = 0; $index < $count; ++$index) {
                $property = $properties[$boostIndex - 1];
                $agency = $property->getUser();

                if (!$agency instanceof User || null === $agency->getId()) {
                    throw new \LogicException('Chaque transaction de boost doit être liée à une agence persistée.');
                }

                $agencyKey = $agencyKeysById[$agency->getId()];
                [, $endsAt] = BillingFixtureData::propertyBoostSchedule($boostIndex, $status);
                [$sourcePack, $sourcePeriod, $sourcePayment] = $this->boostSource($agencyKey, $boostIndex);

                $transaction = (new BoosterTransaction())
                    ->setAgency($agency)
                    ->setProperty($property)
                    ->setQuantity(-1)
                    ->setType(BoosterTransactionType::PROPERTY_BOOST)
                    ->setBoosterPack($sourcePack)
                    ->setSubscriptionPeriod($sourcePeriod)
                    ->setPayment($sourcePayment)
                    ->setExpiresAt($endsAt)
                    ->setIdempotencyKey(sprintf('fixture-property-boost-%03d', $boostIndex))
                    ->setDescription('Utilisation fixture d’un boost sur une annonce.');

                $manager->persist($transaction);
                $this->addReference(self::PROPERTY_BOOST_TRANSACTION_REFERENCE_PREFIX.$boostIndex, $transaction);
                ++$boostIndex;
            }
        }
    }

    /**
     * @return array{0: ?BoosterPack, 1: ?AgencySubscriptionPeriod, 2: ?Payment}
     */
    private function boostSource(string $agencyKey, int $boostIndex): array
    {
        $agencyPosition = array_search($agencyKey, array_keys(BillingFixtureData::agencyReferences()), true);
        $planCode = is_int($agencyPosition) ? BillingFixtureData::agencyPlanCode($agencyPosition) : 'free';

        if (0 === $boostIndex % 2 && 'free' !== $planCode) {
            return [
                null,
                $this->getReference(
                    BillingFixtureData::subscriptionPeriodReference($agencyKey),
                    AgencySubscriptionPeriod::class,
                ),
                $this->getReference(
                    BillingFixtureData::subscriptionPaymentReference($agencyKey),
                    Payment::class,
                ),
            ];
        }

        $packCodes = array_keys(BillingFixtureData::BOOSTER_PRICES);
        $packCode = $packCodes[$boostIndex % count($packCodes)];

        return [
            $this->getReference(BoosterPackFixtures::BOOSTER_PACK_REFERENCE_PREFIX.$packCode, BoosterPack::class),
            null,
            $this->getReference(BillingFixtureData::boosterPaymentReference($agencyKey), Payment::class),
        ];
    }

    private function createAdministrativeTransactions(ObjectManager $manager): void
    {
        $agency = $this->getReference(UserFixtures::USER_AGENCE_REFERENCE, User::class);
        $payment = $this->getReference(BillingFixtureData::boosterPaymentReference('main'), Payment::class);

        $transactions = [
            'admin_credit' => [2, BoosterTransactionType::ADMIN_CREDIT, null, 'Crédit manuel fixture.'],
            'admin_debit' => [-1, BoosterTransactionType::ADMIN_DEBIT, null, 'Débit manuel fixture.'],
            'refund' => [-1, BoosterTransactionType::REFUND, $payment, 'Remboursement de boost fixture.'],
            'expiration' => [-1, BoosterTransactionType::EXPIRATION, null, 'Expiration de boost fixture.'],
        ];

        foreach ($transactions as $key => [$quantity, $type, $transactionPayment, $description]) {
            $transaction = (new BoosterTransaction())
                ->setAgency($agency)
                ->setQuantity($quantity)
                ->setType($type)
                ->setPayment($transactionPayment)
                ->setExpiresAt('expiration' === $key ? new \DateTimeImmutable('-1 day') : null)
                ->setIdempotencyKey('fixture-'.$key.'-main')
                ->setDescription($description);

            $manager->persist($transaction);
            $this->addReference(self::BOOSTER_TRANSACTION_REFERENCE_PREFIX.$key.'_main', $transaction);
        }
    }
}
