<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Booster\PropertyBoost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class PropertyBoostFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROPERTY_BOOST_REFERENCE_PREFIX = 'property_boost_';

    public function load(ObjectManager $manager): void
    {
        $boostIndex = 1;

        foreach (BillingFixtureData::PROPERTY_BOOST_STATUS_COUNTS as $statusValue => $count) {
            $status = PropertyBoostStatus::from($statusValue);

            for ($index = 0; $index < $count; ++$index) {
                $transaction = $this->getReference(
                    BoosterTransactionFixtures::PROPERTY_BOOST_TRANSACTION_REFERENCE_PREFIX.$boostIndex,
                    BoosterTransaction::class,
                );
                $property = $transaction->getProperty();

                if (null === $property) {
                    throw new \LogicException('Chaque boost de bien doit être lié à une transaction de propriété.');
                }

                [$startsAt, $endsAt, $canceledAt] = BillingFixtureData::propertyBoostSchedule($boostIndex, $statusValue);

                $boost = (new PropertyBoost())
                    ->setProperty($property)
                    ->setAgency($transaction->getAgency())
                    ->setBoosterTransaction($transaction)
                    ->setStatus($status)
                    ->setStartsAt($startsAt)
                    ->setEndsAt($endsAt)
                    ->setCanceledAt($canceledAt);

                $manager->persist($boost);
                $this->addReference(self::PROPERTY_BOOST_REFERENCE_PREFIX.$boostIndex, $boost);
                ++$boostIndex;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BoosterTransactionFixtures::class,
        ];
    }
}
