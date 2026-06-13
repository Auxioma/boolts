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
    public const USER_VISITEUR_REFERENCE = 'user_visiteur';
    public const USER_AGENCE_REFERENCE = 'user_agence';
    public const USER_MOHCINE_REFERENCE = 'user_mohcine';
    public const USER_ADMIN_REFERENCE = 'user_admin';

    public const USER_AGENCE_REFERENCE_PREFIX = 'user_agence_';

    /**
     * Références génériques utilisées par d'autres fixtures.
     *
     * Exemple :
     * user_1
     * user_2
     * user_3
     */
    public const USER_REFERENCE_PREFIX = 'user_';

    /**
     * Total :
     * 1 visiteur
     * 1 agence principale
     * 1 Mohcine
     * 1 admin
     * 50 agences générées
     *
     * Donc : 54 utilisateurs.
     */
    public const USER_COUNT = 54;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $userReferenceIndex = 1;

        $paysReferences = [
            'FR',
            'BE',
            'CH',
            'ES',
            'IT',
            'DE',
            'PT',
        ];

        /*
        |--------------------------------------------------------------------------
        | Visiteur
        |--------------------------------------------------------------------------
        */

        $visiteur = new User();
        $visiteur
            ->setEmail('visiteur@visiteur.visiteur')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPays(
                $this->getReference(
                    PaysFixtures::PAYS_REFERENCE_PREFIX.'FR',
                    Pays::class
                )
            );

        $visiteur->setPassword(
            $this->passwordHasher->hashPassword($visiteur, '0000')
        );

        $manager->persist($visiteur);

        $this->addReference(
            self::USER_VISITEUR_REFERENCE,
            $visiteur
        );

        $this->addReference(
            self::USER_REFERENCE_PREFIX.$userReferenceIndex,
            $visiteur
        );

        ++$userReferenceIndex;

        /*
        |--------------------------------------------------------------------------
        | Agence principale
        |--------------------------------------------------------------------------
        */

        $agence = new User();
        $agence
            ->setEmail('agence@agence.agence')
            ->setRoles(['ROLE_AGENCE'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setTelephone($faker->phoneNumber())
            ->setAdresse($faker->streetAddress())
            ->setAdresseComplement($faker->optional(0.4)->secondaryAddress())
            ->setCodePostal($faker->postcode())
            ->setVille($faker->city())
            ->setEntreprise('toto')
            ->setDescription(
                $faker->paragraphs(3, true)
            )
            ->setNumeroContact($faker->phoneNumber())
            ->setAdresseContact($faker->streetAddress())
            ->setAdresseComplementContact($faker->optional(0.4)->secondaryAddress())
            ->setCodePostalContact($faker->postcode())
            ->setVilleContact($faker->city())
            ->setPaysContact('France')
            ->setWhatsApp($faker->phoneNumber())
            ->setPays(
                $this->getReference(
                    PaysFixtures::PAYS_REFERENCE_PREFIX.'FR',
                    Pays::class
                )
            );

        $agence->setPassword(
            $this->passwordHasher->hashPassword($agence, '0000')
        );

        $manager->persist($agence);

        $this->addReference(
            self::USER_AGENCE_REFERENCE,
            $agence
        );

        $this->addReference(
            self::USER_REFERENCE_PREFIX.$userReferenceIndex,
            $agence
        );

        ++$userReferenceIndex;

        /*
        |--------------------------------------------------------------------------
        | Mohcine
        |--------------------------------------------------------------------------
        */

        $mohcine = new User();
        $mohcine
            ->setEmail('mohcine.elafia@gmail.com')
            ->setRoles(['ROLE_AGENCE'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setTelephone($faker->phoneNumber())
            ->setAdresse($faker->streetAddress())
            ->setAdresseComplement($faker->optional(0.4)->secondaryAddress())
            ->setCodePostal($faker->postcode())
            ->setVille($faker->city())
            ->setEntreprise('Agence Mohcine')
            ->setDescription(
                $faker->paragraphs(3, true)
            )
            ->setNumeroContact($faker->phoneNumber())
            ->setAdresseContact($faker->streetAddress())
            ->setAdresseComplementContact($faker->optional(0.4)->secondaryAddress())
            ->setCodePostalContact($faker->postcode())
            ->setVilleContact($faker->city())
            ->setPaysContact('France')
            ->setWhatsApp($faker->phoneNumber())
            ->setPays(
                $this->getReference(
                    PaysFixtures::PAYS_REFERENCE_PREFIX.'FR',
                    Pays::class
                )
            );

        $mohcine->setPassword(
            $this->passwordHasher->hashPassword($mohcine, '0000')
        );

        $manager->persist($mohcine);

        $this->addReference(
            self::USER_MOHCINE_REFERENCE,
            $mohcine
        );

        $this->addReference(
            self::USER_REFERENCE_PREFIX.$userReferenceIndex,
            $mohcine
        );

        ++$userReferenceIndex;

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $admin = new User();
        $admin
            ->setEmail('admin@admin.admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsVerified(true)
            ->setNom($faker->lastName())
            ->setPrenom($faker->firstName())
            ->setPays(
                $this->getReference(
                    PaysFixtures::PAYS_REFERENCE_PREFIX.'FR',
                    Pays::class
                )
            );

        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, '0000')
        );

        $manager->persist($admin);

        $this->addReference(
            self::USER_ADMIN_REFERENCE,
            $admin
        );

        $this->addReference(
            self::USER_REFERENCE_PREFIX.$userReferenceIndex,
            $admin
        );

        ++$userReferenceIndex;

        /*
        |--------------------------------------------------------------------------
        | Génération des agences
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 50; ++$i) {
            $iso = $faker->randomElement($paysReferences);

            $agence = new User();
            $agence
                ->setEmail(\sprintf('agence%d@boolts.test', $i))
                ->setRoles(['ROLE_AGENCE'])
                ->setIsVerified(true)
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setTelephone($faker->phoneNumber())
                ->setAdresse($faker->streetAddress())
                ->setAdresseComplement($faker->optional(0.4)->secondaryAddress())
                ->setCodePostal($faker->postcode())
                ->setVille($faker->city())
                ->setEntreprise(\sprintf('Agence immobilière %d', $i))
                ->setDescription(
                    $faker->paragraphs(3, true)
                )
                ->setNumeroContact($faker->phoneNumber())
                ->setAdresseContact($faker->streetAddress())
                ->setAdresseComplementContact($faker->optional(0.4)->secondaryAddress())
                ->setCodePostalContact($faker->postcode())
                ->setVilleContact($faker->city())
                ->setPaysContact('France')
                ->setWhatsApp($faker->phoneNumber())
                ->setPays(
                    $this->getReference(
                        PaysFixtures::PAYS_REFERENCE_PREFIX.$iso,
                        Pays::class
                    )
                );

            $agence->setPassword(
                $this->passwordHasher->hashPassword($agence, '0000')
            );

            $manager->persist($agence);

            /*
             * Référence spéciale utilisée dans PropertyFixtures.
             *
             * Exemple :
             * user_agence_1
             * user_agence_2
             */
            $this->addReference(
                self::USER_AGENCE_REFERENCE_PREFIX.$i,
                $agence
            );

            /*
             * Référence générique utilisée dans PropertyViewFixtures.
             *
             * Exemple :
             * user_5
             * user_6
             */
            $this->addReference(
                self::USER_REFERENCE_PREFIX.$userReferenceIndex,
                $agence
            );

            ++$userReferenceIndex;
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
