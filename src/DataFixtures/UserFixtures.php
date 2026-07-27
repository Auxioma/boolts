<?php

declare(strict_types=1);

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

use App\Entity\FuseauHoraire;
use App\Entity\LangueParler;
use App\Entity\Langues;
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
    public const USER_REFERENCE_PREFIX = 'user_';
    public const USER_COUNT = 54;

    private const COUNTRY_TIMEZONE = [
        'FR' => 'Europe/Paris',
        'BE' => 'Europe/Brussels',
        'CH' => 'Europe/Paris',
        'LU' => 'Europe/Paris',
        'MC' => 'Europe/Paris',

        'GB' => 'Europe/London',
        'IE' => 'Europe/London',

        'ES' => 'Europe/Madrid',
        'IT' => 'Europe/Rome',
        'DE' => 'Europe/Berlin',
        'NL' => 'Europe/Amsterdam',
        'PT' => 'Europe/Lisbon',
        'PL' => 'Europe/Warsaw',

        'BY' => 'Europe/Minsk',
        'RU' => 'Europe/Minsk',

        'US' => 'America/New_York',
        'CA' => 'America/Toronto',
        'MX' => 'America/Mexico_City',
        'BR' => 'America/Sao_Paulo',

        'MA' => 'Africa/Casablanca',
        'DZ' => 'Africa/Algiers',
        'TN' => 'Africa/Tunis',
        'SN' => 'Africa/Dakar',
        'CI' => 'Africa/Abidjan',
        'CM' => 'Africa/Douala',

        'AE' => 'Asia/Dubai',
        'CN' => 'Asia/Shanghai',
        'JP' => 'Asia/Tokyo',
        'KR' => 'Asia/Seoul',
        'TH' => 'Asia/Bangkok',
        'IN' => 'Asia/Kolkata',

        'AU' => 'Australia/Sydney',
    ];

    private const COUNTRY_DEFAULT_LANGUAGE = [
        'FR' => 'fr',
        'BE' => 'fr',
        'CH' => 'fr',
        'LU' => 'fr',
        'MC' => 'fr',

        'GB' => 'en',
        'IE' => 'en',
        'US' => 'en',
        'CA' => 'en',
        'AU' => 'en',

        'ES' => 'es',
        'MX' => 'es',

        'IT' => 'it',
        'DE' => 'de',
        'NL' => 'nl',
        'PT' => 'pt',
        'PL' => 'pl',

        'BY' => 'ru',
        'RU' => 'ru',

        'MA' => 'ar',
        'DZ' => 'ar',
        'TN' => 'ar',
        'AE' => 'ar',

        'CN' => 'zh',
    ];

    private const COUNTRY_SPOKEN_LANGUAGES = [
        'FR' => ['fr', 'en'],
        'BE' => ['fr', 'nl', 'en'],
        'CH' => ['fr', 'de', 'it', 'en'],
        'LU' => ['fr', 'de', 'en'],
        'MC' => ['fr', 'en'],

        'GB' => ['en'],
        'IE' => ['en'],
        'US' => ['en'],
        'CA' => ['en', 'fr'],
        'AU' => ['en'],

        'ES' => ['es', 'en'],
        'MX' => ['es', 'en'],

        'IT' => ['it', 'en'],
        'DE' => ['de', 'en'],
        'NL' => ['nl', 'en'],
        'PT' => ['pt', 'en'],
        'PL' => ['pl', 'en'],

        'BY' => ['ru', 'be', 'en'],
        'RU' => ['ru', 'en'],

        'MA' => ['ar', 'fr'],
        'DZ' => ['ar', 'fr'],
        'TN' => ['ar', 'fr'],
        'SN' => ['fr'],
        'CI' => ['fr'],
        'CM' => ['fr', 'en'],

        'AE' => ['ar', 'en'],
        'CN' => ['zh', 'en'],
        'JP' => ['en'],
        'KR' => ['en'],
        'TH' => ['en'],
        'IN' => ['en'],

        'BR' => ['pt', 'en'],
    ];

    private const AGENCY_ADDRESSES = [
        [
            'iso' => 'FR',
            'codePostal' => '75018',
            'fr' => [
                'adresse' => '12 Rue Lepic',
                'adresseComplement' => '2e étage',
                'ville' => 'Paris',
                'description' => 'Agence immobilière spécialisée dans les biens résidentiels à Paris et en Île-de-France.',
                'adresseContact' => '12 Rue Lepic',
                'adresseComplementContact' => 'Service commercial',
                'villeContact' => 'Paris',
                'paysContact' => 'France',
            ],
            'en' => [
                'adresse' => '12 Lepic Street',
                'adresseComplement' => '2nd floor',
                'ville' => 'Paris',
                'description' => 'Real estate agency specializing in residential properties in Paris and the Île-de-France region.',
                'adresseContact' => '12 Lepic Street',
                'adresseComplementContact' => 'Sales department',
                'villeContact' => 'Paris',
                'paysContact' => 'France',
            ],
        ],
        [
            'iso' => 'FR',
            'codePostal' => '69002',
            'fr' => [
                'adresse' => '25 Rue de la République',
                'adresseComplement' => null,
                'ville' => 'Lyon',
                'description' => 'Agence spécialisée dans la vente et la location de biens immobiliers à Lyon.',
                'adresseContact' => '25 Rue de la République',
                'adresseComplementContact' => null,
                'villeContact' => 'Lyon',
                'paysContact' => 'France',
            ],
            'en' => [
                'adresse' => '25 Republic Street',
                'adresseComplement' => null,
                'ville' => 'Lyon',
                'description' => 'Agency specializing in property sales and rentals in Lyon.',
                'adresseContact' => '25 Republic Street',
                'adresseComplementContact' => null,
                'villeContact' => 'Lyon',
                'paysContact' => 'France',
            ],
        ],
        [
            'iso' => 'US',
            'codePostal' => '10001',
            'fr' => [
                'adresse' => '350 5e Avenue',
                'adresseComplement' => 'Bureau 1200',
                'ville' => 'New York',
                'description' => 'Agence immobilière internationale spécialisée dans les biens haut de gamme à New York.',
                'adresseContact' => '350 5e Avenue',
                'adresseComplementContact' => 'Bureau 1200',
                'villeContact' => 'New York',
                'paysContact' => 'États-Unis',
            ],
            'en' => [
                'adresse' => '350 5th Avenue',
                'adresseComplement' => 'Suite 1200',
                'ville' => 'New York',
                'description' => 'International real estate agency specializing in premium properties in New York.',
                'adresseContact' => '350 5th Avenue',
                'adresseComplementContact' => 'Suite 1200',
                'villeContact' => 'New York',
                'paysContact' => 'United States',
            ],
        ],
        [
            'iso' => 'US',
            'codePostal' => '90028',
            'fr' => [
                'adresse' => '6801 Boulevard Hollywood',
                'adresseComplement' => null,
                'ville' => 'Los Angeles',
                'description' => 'Agence spécialisée dans les biens résidentiels et commerciaux à Los Angeles.',
                'adresseContact' => '6801 Boulevard Hollywood',
                'adresseComplementContact' => null,
                'villeContact' => 'Los Angeles',
                'paysContact' => 'États-Unis',
            ],
            'en' => [
                'adresse' => '6801 Hollywood Boulevard',
                'adresseComplement' => null,
                'ville' => 'Los Angeles',
                'description' => 'Agency specializing in residential and commercial properties in Los Angeles.',
                'adresseContact' => '6801 Hollywood Boulevard',
                'adresseComplementContact' => null,
                'villeContact' => 'Los Angeles',
                'paysContact' => 'United States',
            ],
        ],
        [
            'iso' => 'CA',
            'codePostal' => 'H2Y 1C6',
            'fr' => [
                'adresse' => '110 Rue Notre-Dame Ouest',
                'adresseComplement' => null,
                'ville' => 'Montréal',
                'description' => 'Agence immobilière basée à Montréal, spécialisée dans les biens urbains et familiaux.',
                'adresseContact' => '110 Rue Notre-Dame Ouest',
                'adresseComplementContact' => null,
                'villeContact' => 'Montréal',
                'paysContact' => 'Canada',
            ],
            'en' => [
                'adresse' => '110 Notre-Dame Street West',
                'adresseComplement' => null,
                'ville' => 'Montreal',
                'description' => 'Montreal-based real estate agency specializing in urban and family properties.',
                'adresseContact' => '110 Notre-Dame Street West',
                'adresseComplementContact' => null,
                'villeContact' => 'Montreal',
                'paysContact' => 'Canada',
            ],
        ],
        [
            'iso' => 'AU',
            'codePostal' => '2000',
            'fr' => [
                'adresse' => '1 Rue Macquarie',
                'adresseComplement' => null,
                'ville' => 'Sydney',
                'description' => 'Agence immobilière australienne spécialisée dans les biens situés à Sydney et en Nouvelle-Galles du Sud.',
                'adresseContact' => '1 Rue Macquarie',
                'adresseComplementContact' => null,
                'villeContact' => 'Sydney',
                'paysContact' => 'Australie',
            ],
            'en' => [
                'adresse' => '1 Macquarie Street',
                'adresseComplement' => null,
                'ville' => 'Sydney',
                'description' => 'Australian real estate agency specializing in properties located in Sydney and New South Wales.',
                'adresseContact' => '1 Macquarie Street',
                'adresseComplementContact' => null,
                'villeContact' => 'Sydney',
                'paysContact' => 'Australia',
            ],
        ],
    ];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(20260727);
        $userReferenceIndex = 1;

        $visiteur = $this->createUser(
            email: 'visiteur@example.com',
            roles: ['ROLE_USER'],
            password: '0000',
            nom: $faker->lastName(),
            prenom: $faker->firstName(),
            pays: $this->getPaysReference('FR')
        );

        $manager->persist($visiteur);
        $this->addReference(self::USER_VISITEUR_REFERENCE, $visiteur);
        $this->addReference(self::USER_REFERENCE_PREFIX.$userReferenceIndex, $visiteur);
        ++$userReferenceIndex;

        $agenceAddress = self::AGENCY_ADDRESSES[0];

        $agence = $this->createAgency(
            email: 'agence@agence.agence',
            entreprise: 'Cabinet des Docks Immobilier',
            password: 'agence',
            nom: $faker->lastName(),
            prenom: $faker->firstName(),
            telephone: $faker->phoneNumber(),
            addressData: $agenceAddress,
            pays: $this->getPaysReference($agenceAddress['iso'])
        );

        $manager->persist($agence);
        $this->addReference(self::USER_AGENCE_REFERENCE, $agence);
        $this->addReference(self::USER_REFERENCE_PREFIX.$userReferenceIndex, $agence);
        ++$userReferenceIndex;

        $mohcineAddress = self::AGENCY_ADDRESSES[1];

        $mohcine = $this->createAgency(
            email: 'mohcine@agence.example.com',
            entreprise: 'Agence Mohcine',
            password: '0000',
            nom: $faker->lastName(),
            prenom: $faker->firstName(),
            telephone: $faker->phoneNumber(),
            addressData: $mohcineAddress,
            pays: $this->getPaysReference($mohcineAddress['iso'])
        );

        $manager->persist($mohcine);
        $this->addReference(self::USER_MOHCINE_REFERENCE, $mohcine);
        $this->addReference(self::USER_REFERENCE_PREFIX.$userReferenceIndex, $mohcine);
        ++$userReferenceIndex;

        $admin = $this->createUser(
            email: 'admin@boolts.example.com',
            roles: ['ROLE_ADMIN'],
            password: '0000',
            nom: $faker->lastName(),
            prenom: $faker->firstName(),
            pays: $this->getPaysReference('FR')
        );

        $manager->persist($admin);
        $this->addReference(self::USER_ADMIN_REFERENCE, $admin);
        $this->addReference(self::USER_REFERENCE_PREFIX.$userReferenceIndex, $admin);
        ++$userReferenceIndex;

        for ($i = 1; $i <= 50; ++$i) {
            $addressData = $faker->randomElement(self::AGENCY_ADDRESSES);

            $agency = $this->createAgency(
                email: \sprintf('agence%d@agence.example.com', $i),
                entreprise: \sprintf('Agence Internationale %d', $i),
                password: '0000',
                nom: $faker->lastName(),
                prenom: $faker->firstName(),
                telephone: $faker->phoneNumber(),
                addressData: $addressData,
                pays: $this->getPaysReference($addressData['iso'])
            );

            $manager->persist($agency);

            $this->addReference(self::USER_AGENCE_REFERENCE_PREFIX.$i, $agency);
            $this->addReference(self::USER_REFERENCE_PREFIX.$userReferenceIndex, $agency);

            ++$userReferenceIndex;
        }

        $manager->flush();
    }

    private function createUser(
        string $email,
        array $roles,
        string $password,
        string $nom,
        string $prenom,
        Pays $pays,
    ): User {
        $user = new User();

        $iso = $pays->getIso() ?? 'FR';
        $defaultLanguageCode = self::COUNTRY_DEFAULT_LANGUAGE[$iso] ?? 'fr';
        $spokenLanguageCodes = self::COUNTRY_SPOKEN_LANGUAGES[$iso] ?? [$defaultLanguageCode, 'en'];

        $user
            ->setEmail($email)
            ->setRoles($roles)
            ->setIsVerified(true)
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setPays($pays)
            ->setDevise($pays->getDevise())
            ->setFuseauHoraire($this->getFuseauHoraireByCountryIso($iso))
            ->setLangues($this->getLanguesReference($defaultLanguageCode))
            ->setVisitAgency(0);

        foreach ($spokenLanguageCodes as $spokenLanguageCode) {
            $user->addLangueParler(
                $this->getLangueParlerReference($spokenLanguageCode)
            );
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        return $user;
    }

    private function createAgency(
        string $email,
        string $entreprise,
        string $password,
        string $nom,
        string $prenom,
        string $telephone,
        array $addressData,
        Pays $pays,
    ): User {
        $agency = $this->createUser(
            email: $email,
            roles: ['ROLE_AGENCE'],
            password: $password,
            nom: $nom,
            prenom: $prenom,
            pays: $pays
        );

        $agency
            ->setTelephone($telephone)
            ->setEntreprise($entreprise)
            ->setEmailContact($email)
            ->setNumeroContact($telephone)
            ->setWhatsApp($telephone)
            ->setCodePostal($addressData['codePostal'])
            ->setCodePostalContact($addressData['codePostal'])
            ->setVisitAgency($this->getAgencyVisitCount($email));

        $this->fillUserTranslation($agency, 'fr', $addressData['fr']);
        $this->fillUserTranslation($agency, 'en', $addressData['en']);

        $agency->mergeNewTranslations();

        return $agency;
    }

    private function getAgencyVisitCount(string $email): int
    {
        return 250 + (int) (crc32(mb_strtolower($email)) % 24_751);
    }

    private function fillUserTranslation(User $user, string $locale, array $data): void
    {
        $translation = $user->translate($locale);

        $translation->setAdresse($data['adresse']);
        $translation->setAdresseComplement($data['adresseComplement']);
        $translation->setVille($data['ville']);
        $translation->setDescription($data['description']);
        $translation->setAdresseContact($data['adresseContact']);
        $translation->setAdresseComplementContact($data['adresseComplementContact']);
        $translation->setVilleContact($data['villeContact']);
        $translation->setPaysContact($data['paysContact']);
    }

    private function getPaysReference(string $iso): Pays
    {
        return $this->getReference(
            PaysFixtures::PAYS_REFERENCE_PREFIX.$iso,
            Pays::class
        );
    }

    private function getFuseauHoraireByCountryIso(string $iso): FuseauHoraire
    {
        $timezone = self::COUNTRY_TIMEZONE[$iso] ?? 'Europe/Paris';

        return $this->getReference(
            FuseauHoraireFixtures::FUSEAU_HORAIRE_REFERENCE_PREFIX.$timezone,
            FuseauHoraire::class
        );
    }

    private function getLanguesReference(string $code): Langues
    {
        return $this->getReference(
            LanguesFixtures::LANGUES_REFERENCE_PREFIX.$code,
            Langues::class
        );
    }

    private function getLangueParlerReference(string $code): LangueParler
    {
        return $this->getReference(
            LangueParlerFixtures::LANGUE_PARLER_REFERENCE_PREFIX.$code,
            LangueParler::class
        );
    }

    public function getDependencies(): array
    {
        return [
            PaysFixtures::class,
            FuseauHoraireFixtures::class,
            LanguesFixtures::class,
            LangueParlerFixtures::class,
        ];
    }
}
