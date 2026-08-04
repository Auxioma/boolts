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
use Faker\Generator;

class PropertyViewFixtures extends Fixture implements DependentFixtureInterface
{
    public const PROPERTY_VIEW_REFERENCE_PREFIX = 'property_view_';

    private const MIN_VIEWS_PER_PROPERTY = 5;
    private const MAX_VIEWS_PER_PROPERTY = 80;
    private const MAX_DAYS_AGO = 45;

    /**
     * Nombre d'enregistrements insérés en une seule requête SQL native.
     *
     * Une taille volontairement limitée évite aussi les requêtes trop longues.
     */
    private const BATCH_SIZE = 200;

    private Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->seed(20260727);

        if (!$manager instanceof EntityManagerInterface) {
            throw new \LogicException('Le manager doit être une instance de EntityManagerInterface.');
        }

        $nativeConnection = $manager->getConnection()->getNativeConnection();

        if (!$nativeConnection instanceof \PDO) {
            throw new \LogicException('La fixture des vues nécessite une connexion PDO native.');
        }

        /** @var list<int|string> $propertyIds */
        $propertyIds = $nativeConnection
            ->query('SELECT id FROM property')
            ->fetchAll(\PDO::FETCH_COLUMN);

        $rows = [];

        $ownsTransaction = !$nativeConnection->inTransaction();

        if ($ownsTransaction) {
            $nativeConnection->beginTransaction();
        }

        try {
            foreach ($propertyIds as $propertyIndex => $propertyId) {
                ++$propertyIndex;

                $viewsCount = $this->faker->numberBetween(
                    self::MIN_VIEWS_PER_PROPERTY,
                    self::MAX_VIEWS_PER_PROPERTY
                );

                $usedViewKeys = [];

                for ($viewIndex = 1; $viewIndex <= $viewsCount; ++$viewIndex) {
                    $viewedAt = $this->randomViewedAt();

                    $user = $this->getRandomUser();

                    $visitorHash = $this->generateVisitorHash(
                        propertyIndex: $propertyIndex,
                        viewIndex: $viewIndex,
                        user: $user
                    );

                    $viewKey = $this->generateViewKey(
                        propertyIndex: $propertyIndex,
                        visitorHash: $visitorHash,
                        viewedAt: $viewedAt
                    );

                    if (isset($usedViewKeys[$viewKey])) {
                        continue;
                    }

                    $usedViewKeys[$viewKey] = true;

                    $rows[] = [
                        'property_id' => $propertyId,
                        'user_id' => $user?->getId(),
                        'view_key' => $viewKey,
                        'visitor_hash' => $visitorHash,
                        'viewed_at' => $viewedAt->format('Y-m-d H:i:s'),
                    ];

                    if (\count($rows) >= self::BATCH_SIZE) {
                        $this->insertBatch($nativeConnection, $rows);
                        $rows = [];
                    }
                }
            }

            if ([] !== $rows) {
                $this->insertBatch($nativeConnection, $rows);
            }

            if ($ownsTransaction) {
                $nativeConnection->commit();
            }
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $nativeConnection->inTransaction()) {
                $nativeConnection->rollBack();
            }

            throw $exception;
        }
    }

    private function insertBatch(\PDO $connection, array $rows): void
    {
        if ([] === $rows) {
            return;
        }

        $placeholders = [];
        $params = [];

        foreach ($rows as $row) {
            $placeholders[] = '(?, ?, ?, ?, ?)';

            $params[] = $row['property_id'];
            $params[] = $row['user_id'];
            $params[] = $row['view_key'];
            $params[] = $row['visitor_hash'];
            $params[] = $row['viewed_at'];
        }

        $sql = \sprintf(
            'INSERT INTO property_view 
                (property_id, user_id, view_key, visitor_hash, viewed_at) 
             VALUES %s',
            implode(', ', $placeholders)
        );

        $statement = $connection->prepare($sql);

        if (false === $statement) {
            throw new \RuntimeException('Impossible de préparer l’insertion des vues de biens.');
        }

        $statement->execute($params);
    }

    private function getRandomUser(): ?User
    {
        /*
         * 60% des vues sont anonymes.
         */
        if ($this->faker->numberBetween(1, 100) <= 60) {
            return null;
        }

        $userIndex = $this->faker->numberBetween(1, UserFixtures::USER_COUNT);

        $userReference = UserFixtures::USER_REFERENCE_PREFIX.$userIndex;

        if (!$this->hasReference($userReference, User::class)) {
            return null;
        }

        /** @var User $user */
        $user = $this->getReference($userReference, User::class);

        return $user;
    }

    private function generateVisitorHash(
        int $propertyIndex,
        int $viewIndex,
        ?User $user,
    ): string {
        if ($user instanceof User) {
            return hash('sha256', \sprintf(
                'connected_user_%d',
                $user->getId()
            ));
        }

        return hash('sha256', \sprintf(
            'anonymous_property_%d_view_%d_random_%d',
            $propertyIndex,
            $viewIndex,
            $this->faker->numberBetween(1000, 999999)
        ));
    }

    private function generateViewKey(
        int $propertyIndex,
        string $visitorHash,
        \DateTimeImmutable $viewedAt,
    ): string {
        return hash('sha256', \sprintf(
            'property_%d_visitor_%s_day_%s',
            $propertyIndex,
            $visitorHash,
            $viewedAt->format('Y-m-d')
        ));
    }

    private function randomViewedAt(): \DateTimeImmutable
    {
        $daysAgo = $this->faker->numberBetween(0, self::MAX_DAYS_AGO);
        $hoursAgo = $this->faker->numberBetween(0, 23);
        $minutesAgo = $this->faker->numberBetween(0, 59);
        $secondsAgo = $this->faker->numberBetween(0, 59);

        return new \DateTimeImmutable(\sprintf(
            '-%d days -%d hours -%d minutes -%d seconds',
            $daysAgo,
            $hoursAgo,
            $minutesAgo,
            $secondsAgo
        ));
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PropertyFixtures::class,
        ];
    }
}
