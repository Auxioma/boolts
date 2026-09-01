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
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_ADMIN_REFERENCE = 'user_admin';
    public const USER_AGENCE_REFERENCE_PREFIX = 'user_agence_';
    public const AGENCY_COUNT = 20;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUser(
            $manager,
            'auxioma.g@gmail.com',
            'Ogdo7251+Ogdo+',
            ['ROLE_ADMIN'],
        );
        $this->addReference(self::USER_ADMIN_REFERENCE, $admin);

        for ($i = 1; $i <= self::AGENCY_COUNT; ++$i) {
            $agency = $this->createUser(
                $manager,
                \sprintf('agence%02d@auxioma.eu', $i),
                'Boolts+0000',
                ['ROLE_AGENCE'],
            );
            $this->addReference(self::USER_AGENCE_REFERENCE_PREFIX.$i, $agency);
        }

        $manager->flush();
    }

    /**
     * @param list<string> $roles
     */
    private function createUser(
        ObjectManager $manager,
        string $email,
        string $password,
        array $roles,
    ): User {
        $user = new User();
        $user
            ->setEmail($email)
            ->setRoles($roles)
            ->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);

        return $user;
    }
}
