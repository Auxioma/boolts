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

use App\Entity\Langues;
use App\Entity\Devise;
use App\Entity\FuseauHoraire;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
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
        $defaultLanguage = $this->getReference(
            LanguesFixtures::LANGUES_REFERENCE_PREFIX.'fr',
            Langues::class,
        );
        $defaultCurrency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$defaultCurrency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les utilisateurs.');
        }
        $defaultTimeZone = $this->getReference(
            FuseauHoraireFixtures::FUSEAU_HORAIRE_REFERENCE_PREFIX.'Europe/Paris',
            FuseauHoraire::class,
        );

        $admin = $this->createUser(
            $manager,
            'auxioma.g@gmail.com',
            'Ogdo7251+Ogdo+',
            ['ROLE_ADMIN'],
            $defaultLanguage,
            $defaultCurrency,
            $defaultTimeZone,
        );
        $this->addReference(self::USER_ADMIN_REFERENCE, $admin);

        for ($i = 1; $i <= self::AGENCY_COUNT; ++$i) {
            $agency = $this->createUser(
                $manager,
                \sprintf('agence%02d@auxioma.eu', $i),
                'Boolts+0000',
                ['ROLE_AGENCE'],
                $defaultLanguage,
                $defaultCurrency,
                $defaultTimeZone,
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
        Langues $defaultLanguage,
        Devise $defaultCurrency,
        FuseauHoraire $defaultTimeZone,
    ): User {
        $user = FixtureEntityHelper::findOrCreate($manager, User::class, [
            'email' => $email,
        ]);
        $user
            ->setEmail($email)
            ->setRoles($roles)
            ->setIsVerified(true)
            ->setLangues($defaultLanguage)
            ->setDevise($defaultCurrency)
            ->setFuseauHoraire($defaultTimeZone);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);

        return $user;
    }

    public function getDependencies(): array
    {
        return [
            LanguesFixtures::class,
            PaysFixtures::class,
            FuseauHoraireFixtures::class,
        ];
    }
}
