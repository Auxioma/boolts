<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AgencyProfileDailyVisit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class AgencyProfileDailyVisitFixtures extends Fixture implements DependentFixtureInterface
{
    private const FIXTURE_SEED = 20260727;
    private const DAYS_OF_HISTORY = 180;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(self::FIXTURE_SEED);
        $agencies = [UserFixtures::USER_AGENCE_REFERENCE, UserFixtures::USER_MOHCINE_REFERENCE];

        for ($index = 1; $index <= 50; ++$index) {
            $agencies[] = UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$index;
        }

        foreach ($agencies as $agencyIndex => $reference) {
            /** @var User $agency */
            $agency = $this->getReference($reference, User::class);
            $total = 0;

            for ($day = 0; $day < self::DAYS_OF_HISTORY; ++$day) {
                if ($faker->boolean(22)) {
                    continue;
                }

                $visits = $faker->numberBetween(2, 24 + ($agencyIndex % 12) * 4);
                $visit = new AgencyProfileDailyVisit();
                $visit
                    ->setAgency($agency)
                    ->setViewedOn((new \DateTimeImmutable('today'))->modify(sprintf('-%d days', $day)))
                    ->setVisits($visits);

                $manager->persist($visit);
                $total += $visits;
            }

            $agency->setVisitAgency($total);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
