<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\DataFixtures;

use App\Entity\Pays;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $paysReferences = [
            'FR',
            'BE',
            'CH',
            'ES',
            'IT',
            'DE',
            'PT',
        ];

        // Visiteur
        $visiteur = new User();
        $visiteur
            ->setEmail('visiteur@visiteur.visiteur')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPays($this->getReference(PaysFixtures::PAYS_REFERENCE_PREFIX.'FR', Pays::class));

        $visiteur->setPassword(
            $this->passwordHasher->hashPassword($visiteur, '0000')
        );

        $manager->persist($visiteur);

        // Agence
        $agence = new User();
        $agence
            ->setEmail('agence@agence.agence')
            ->setRoles(['ROLE_AGENCE'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPays($this->getReference(PaysFixtures::PAYS_REFERENCE_PREFIX.'FR', Pays::class));

        $agence->setPassword(
            $this->passwordHasher->hashPassword($agence, '0000')
        );

        $manager->persist($agence);

        // mohcine
        $mohcine = new User();
        $mohcine
            ->setEmail('mohcine.elafia@gmail.com')
            ->setRoles(['ROLE_AGENCE'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPays($this->getReference(PaysFixtures::PAYS_REFERENCE_PREFIX.'FR', Pays::class));

        $mohcine->setPassword(
            $this->passwordHasher->hashPassword($mohcine, '0000')
        );

        $manager->persist($mohcine);

        // Admin
        $admin = new User();
        $admin
            ->setEmail('admin@admin.admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPays($this->getReference(PaysFixtures::PAYS_REFERENCE_PREFIX.'FR', Pays::class));

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, '0000')
        );

        $manager->persist($admin);

        // Génération d'agences
        for ($i = 1; $i <= 50; ++$i) {
            $iso = $faker->randomElement($paysReferences);

            $agence = new User();
            $agence
                ->setEmail(\sprintf('agence%d@boolts.test', $i))
                ->setRoles(['ROLE_AGENCE'])
                ->setIsVerified(true)
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setPays($this->getReference(PaysFixtures::PAYS_REFERENCE_PREFIX.$iso, Pays::class));

            $agence->setPassword(
                $this->passwordHasher->hashPassword($agence, '0000')
            );

            $manager->persist($agence);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaysFixtures::class,
        ];
    }
}
