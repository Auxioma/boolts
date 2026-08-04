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

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class AgencyProfileDailyVisitFixtures extends Fixture implements DependentFixtureInterface
{
    private const FIXTURE_SEED = 20260727;
    private const DAYS_OF_HISTORY = 180;
    private const BATCH_SIZE = 200;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(self::FIXTURE_SEED);

        if (!$manager instanceof EntityManagerInterface) {
            throw new \LogicException('Le manager doit être une instance de EntityManagerInterface.');
        }

        $nativeConnection = $manager->getConnection()->getNativeConnection();

        if (!$nativeConnection instanceof \PDO) {
            throw new \LogicException('La fixture des visites quotidiennes nécessite une connexion PDO native.');
        }

        $agencies = [UserFixtures::USER_AGENCE_REFERENCE, UserFixtures::USER_MOHCINE_REFERENCE];
        $rows = [];
        $today = new \DateTimeImmutable('today');

        for ($index = 1; $index <= 50; ++$index) {
            $agencies[] = UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$index;
        }

        foreach ($agencies as $agencyIndex => $reference) {
            /** @var User $agency */
            $agency = $this->getReference($reference, User::class);
            $agencyId = $agency->getId();

            if (null === $agencyId) {
                throw new \LogicException(\sprintf('L’agence de référence "%s" doit être persistée.', $reference));
            }

            $total = 0;

            for ($day = 0; $day < self::DAYS_OF_HISTORY; ++$day) {
                if ($faker->boolean(22)) {
                    continue;
                }

                $visits = $faker->numberBetween(2, 24 + ($agencyIndex % 12) * 4);
                $rows[] = [
                    'agency_id' => $agencyId,
                    'viewed_on' => $today->modify(\sprintf('-%d days', $day))->format('Y-m-d'),
                    'visits' => $visits,
                ];
                $total += $visits;

                if (\count($rows) >= self::BATCH_SIZE) {
                    $this->insertBatch($nativeConnection, $rows);
                    $rows = [];
                }
            }

            $agency->setVisitAgency($total);
        }

        if ([] !== $rows) {
            $this->insertBatch($nativeConnection, $rows);
        }

        $manager->flush();
    }

    /**
     * @param list<array{agency_id: int, viewed_on: string, visits: int}> $rows
     */
    private function insertBatch(\PDO $connection, array $rows): void
    {
        $placeholders = [];
        $parameters = [];

        foreach ($rows as $row) {
            $placeholders[] = '(?, ?, ?)';
            $parameters[] = $row['agency_id'];
            $parameters[] = $row['viewed_on'];
            $parameters[] = $row['visits'];
        }

        $statement = $connection->prepare(\sprintf(
            'INSERT INTO agency_profile_daily_visit (agency_id, viewed_on, visits) VALUES %s',
            implode(', ', $placeholders)
        ));

        if (false === $statement) {
            throw new \RuntimeException('Impossible de préparer l’insertion des visites quotidiennes.');
        }

        $statement->execute($parameters);
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
