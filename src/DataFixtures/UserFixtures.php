<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Visiteur
        $visiteur = new User();
        $visiteur
            ->setEmail('visiteur@visiteur.visiteur')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
        ;

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
        ;

        $agence->setPassword(
            $this->passwordHasher->hashPassword($agence, '0000')
        );

        $manager->persist($agence);

        // Admin
        $admin = new User();
        $admin
            ->setEmail('admin@admin.admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
        ;

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, '0000')
        );

        $manager->persist($admin);

        // Génération d'agences
        for ($i = 1; $i <= 50; ++$i) {
            $agence = new User();

            $agence
                ->setEmail(\sprintf('agence%d@boolts.test', $i))
                ->setRoles(['ROLE_AGENCE'])
                ->setIsVerified(true)
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
            ;

            $agence->setPassword(
                $this->passwordHasher->hashPassword($agence, '0000')
            );

            $manager->persist($agence);
        }

        $manager->flush();
    }
}
