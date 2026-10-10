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

use App\Entity\Devise;
use App\Entity\FuseauHoraire;
use App\Entity\HoraireOuverture;
use App\Entity\LangueParler;
use App\Entity\Langues;
use App\Entity\Pays;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_ADMIN_REFERENCE = 'user_admin';
    public const USER_AGENCE_REFERENCE_PREFIX = 'user_agence_';

    /**
     * Agences réelles (coordonnées publiques relevées sur leurs sites officiels en octobre 2026).
     *
     * Horaires : [ouverture matin, fermeture matin, ouverture après-midi, fermeture après-midi],
     * un jour absent est fermé. Seuls ceux de BARNES sont publiés, les autres sont fictifs.
     *
     * @var list<array<string, mixed>>
     */
    private const AGENCIES = [
        [
            'entreprise' => 'BARNES Pied-à-terre Saint-Honoré',
            'adresse' => '120-122 rue du Faubourg Saint-Honoré',
            'codePostal' => '75008',
            'ville' => 'Paris',
            'pays' => 'FR',
            'telephone' => '+33 1 85 34 70 69',
            'langue' => 'fr',
            'fuseauHoraire' => 'Europe/Paris',
            'languesParlees' => ['fr'],
            'description' => 'Agence BARNES spécialisée dans l’immobilier de prestige au cœur du 8e arrondissement de Paris. Ouverte du lundi au samedi de 9h à 19h.',
            'horaires' => [
                'lundi' => ['09:00', '13:00', '14:00', '19:00'],
                'mardi' => ['09:00', '13:00', '14:00', '19:00'],
                'mercredi' => ['09:00', '13:00', '14:00', '19:00'],
                'jeudi' => ['09:00', '13:00', '14:00', '19:00'],
                'vendredi' => ['09:00', '13:00', '14:00', '19:00'],
                'samedi' => ['09:00', '13:00', '14:00', '19:00'],
            ],
        ],
        [
            'entreprise' => 'Kensington Morocco',
            'adresse' => '67 Rue Ibn Khaldoun',
            'adresseComplement' => '1er étage, Immeuble El Pacha',
            'codePostal' => '40000',
            'ville' => 'Marrakech',
            'pays' => 'MA',
            'telephone' => '+212 5 24 42 22 29',
            'langue' => 'fr',
            'fuseauHoraire' => 'Africa/Casablanca',
            'languesParlees' => ['ar', 'fr', 'en'],
            'horaires' => [
                'lundi' => ['09:00', '12:30', '14:30', '18:30'],
                'mardi' => ['09:00', '12:30', '14:30', '18:30'],
                'mercredi' => ['09:00', '12:30', '14:30', '18:30'],
                'jeudi' => ['09:00', '12:30', '14:30', '18:30'],
                'vendredi' => ['09:00', '12:00', '15:00', '18:30'],
                'samedi' => ['09:30', '13:00', null, null],
            ],
            'description' => 'Agence immobilière de luxe implantée à Marrakech, spécialisée dans la vente et la location de riads, villas et appartements de prestige au Maroc.',
        ],
        [
            'entreprise' => 'The Corcoran Group',
            'adresse' => '590 Madison Avenue',
            'codePostal' => '10022',
            'ville' => 'New York',
            'pays' => 'US',
            'telephone' => '+1 212-355-3550',
            'langue' => 'en',
            'fuseauHoraire' => 'America/New_York',
            'languesParlees' => [],
            'horaires' => [
                'lundi' => ['09:00', '12:00', '13:00', '18:00'],
                'mardi' => ['09:00', '12:00', '13:00', '18:00'],
                'mercredi' => ['09:00', '12:00', '13:00', '18:00'],
                'jeudi' => ['09:00', '12:00', '13:00', '18:00'],
                'vendredi' => ['09:00', '12:00', '13:00', '18:00'],
                'samedi' => ['10:00', '12:00', '13:00', '17:00'],
                'dimanche' => ['11:00', '12:00', '13:00', '16:00'],
            ],
            'description' => 'Réseau immobilier résidentiel de référence à New York, dont le siège est situé dans l’IBM Building sur Madison Avenue, à Manhattan.',
        ],
    ];

    public const AGENCY_COUNT = 3;

    private const AVATAR_DIRECTORY = __DIR__.'/Data/User/avatars';

    private const OPENING_DAYS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

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
        $avatars = ['admin' => $admin];

        foreach (self::AGENCIES as $index => $data) {
            $i = $index + 1;
            $pays = $this->getReference(PaysFixtures::PAYS_REFERENCE_PREFIX.$data['pays'], Pays::class);
            $devise = $pays->getDevise();

            if (!$devise instanceof Devise) {
                throw new \RuntimeException(\sprintf('Aucune devise pour le pays %s.', $data['pays']));
            }

            $agency = $this->createUser(
                $manager,
                \sprintf('agence%02d@auxioma.eu', $i),
                'Boolts+0000',
                ['ROLE_AGENCE'],
                $this->getReference(LanguesFixtures::LANGUES_REFERENCE_PREFIX.$data['langue'], Langues::class),
                $devise,
                $this->getReference(
                    FuseauHoraireFixtures::FUSEAU_HORAIRE_REFERENCE_PREFIX.$data['fuseauHoraire'],
                    FuseauHoraire::class,
                ),
            );
            $agency
                ->setEntreprise($data['entreprise'])
                ->setPays($pays)
                ->setCodePostal($data['codePostal'])
                ->setTelephone($data['telephone'])
                ->setEmailContact($agency->getEmail())
                ->setNumeroContact($this->randomMobile($data['pays']))
                ->setWhatsApp($this->randomMobile($data['pays']))
                ->setCodePostalContact($data['codePostal']);

            foreach ($agency->getLangueParlers() as $langueParler) {
                $agency->removeLangueParler($langueParler);
            }

            foreach ($data['languesParlees'] as $code) {
                $agency->addLangueParler($this->getReference(
                    LangueParlerFixtures::LANGUE_PARLER_REFERENCE_PREFIX.$code,
                    LangueParler::class,
                ));
            }

            foreach (['fr', 'en'] as $locale) {
                $agency->translate($locale)
                    ->setAdresse($data['adresse'])
                    ->setAdresseComplement($data['adresseComplement'] ?? null)
                    ->setVille($data['ville'])
                    ->setDescription($data['description'])
                    ->setAdresseContact($data['adresse'])
                    ->setAdresseComplementContact($data['adresseComplement'] ?? null)
                    ->setVilleContact($data['ville'])
                    ->setPaysContact($pays->getNom());
            }
            $agency->mergeNewTranslations();

            if ($agency->getHoraireOuvertures()->isEmpty()) {
                $this->addOpeningHours($manager, $agency, $data['horaires']);
            }

            $this->addReference(self::USER_AGENCE_REFERENCE_PREFIX.$i, $agency);
            $avatars[\sprintf('agence%02d', $i)] = $agency;
        }

        /*
         * L'identifiant de l'utilisateur est nécessaire à l'AvatarDirectoryNamer
         * pour ranger l'avatar dans public/avatars/<id>/.
         */
        $manager->flush();

        foreach ($avatars as $name => $user) {
            $this->attachAvatar($user, $name);
        }

        $manager->flush();
    }

    /**
     * @param array<string, array{?string, ?string, ?string, ?string}> $horaires
     */
    private function addOpeningHours(ObjectManager $manager, User $agency, array $horaires): void
    {
        $time = static fn (?string $value): ?\DateTime => null === $value ? null : new \DateTime($value);

        foreach (self::OPENING_DAYS as $day) {
            [$ouvertureMatin, $fermetureMatin, $ouvertureApresMidi, $fermetureApresMidi] = $horaires[$day] ?? [null, null, null, null];

            $horaireOuverture = (new HoraireOuverture())
                ->setJour($day)
                ->setIsOpen(isset($horaires[$day]))
                ->setOuvertureMatin($time($ouvertureMatin))
                ->setFermetureMatin($time($fermetureMatin))
                ->setOuvertureApresMidi($time($ouvertureApresMidi))
                ->setFermetureApresMidi($time($fermetureApresMidi));

            $agency->addHoraireOuverture($horaireOuverture);
            $manager->persist($horaireOuverture);
        }
    }

    /**
     * Numéro de mobile fictif au format du pays (les 555-01xx américains sont réservés à la fiction).
     */
    private function randomMobile(string $pays): string
    {
        $digits = static fn (int $count): string => implode(' ', str_split(
            str_pad((string) random_int(0, 10 ** $count - 1), $count, '0', \STR_PAD_LEFT),
            2,
        ));

        return match ($pays) {
            'FR' => '+33 '.random_int(6, 7).' '.$digits(8),
            'MA' => '+212 '.random_int(6, 7).' '.$digits(8),
            'US' => \sprintf('+1 212-555-01%02d', random_int(0, 99)),
            default => throw new \RuntimeException(\sprintf('Format de mobile inconnu pour le pays %s.', $pays)),
        };
    }

    private function attachAvatar(User $user, string $name): void
    {
        if (null !== $user->getImageName()) {
            return;
        }

        $path = self::AVATAR_DIRECTORY.'/'.$name.'.jpg';

        if (!is_file($path)) {
            throw new \RuntimeException(\sprintf('Avatar de fixture introuvable : %s', $path));
        }

        // VichUploader copie un ReplacingFile, le fichier source reste intact.
        $user->setImageFile(new ReplacingFile($path));
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
            LangueParlerFixtures::class,
            PaysFixtures::class,
            FuseauHoraireFixtures::class,
        ];
    }
}
