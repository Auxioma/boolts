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

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Intl\Countries;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $visiteur = new User()
            ->setEmail('visiteur@visiteur.visiteur')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPassword($this->passwordHasher->hashPassword($visiteur, 'Test1234!'))
        ;
        $manager->persist($visiteur);

        $agence = new User()
            ->setEmail('agence@agence.agence')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPassword($this->passwordHasher->hashPassword($agence, 'Test1234!'))
        ;
        $manager->persist($agence);

        $admin = new User()
            ->setEmail('admin@admin.admin')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPassword($this->passwordHasher->hashPassword($admin, 'Test1234!'))
        ;
        $manager->persist($admin);

        $countries = Countries::getNames('fr');
        foreach ($countries as $countryCode => $countryName) {
            for ($i = 1; $i <= 2; ++$i) {
                $agence = new User();

                $agence
                    ->setEmail(sprintf('agence%d.%s@boolts.test', $i, mb_strtolower($countryCode)))
                    ->setRoles(['ROLE_AGENCE']) // ou ['ROLE_USER'] si tu n'as pas encore ROLE_AGENCE
                    ->setIsVerified(true)
                    ->setNom($faker->lastName())
                    ->setPrenom($faker->firstName())
                    ->setPays($countryName)
                ;

                $agence->setPassword($this->passwordHasher->hashPassword($agence, 'Test1234!'));

                $manager->persist($agence);
            }
        }

        

        $manager->flush();
    }
}
