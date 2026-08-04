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

use App\Entity\FuseauHoraire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FuseauHoraireFixtures extends Fixture
{
    public const FUSEAU_HORAIRE_REFERENCE_PREFIX = 'fuseau_horaire_';

    private const TIMEZONES = [
        'Europe/Paris' => 'UTC+01:00',
        'Europe/London' => 'UTC+00:00',
        'Europe/Madrid' => 'UTC+01:00',
        'Europe/Rome' => 'UTC+01:00',
        'Europe/Brussels' => 'UTC+01:00',
        'Europe/Berlin' => 'UTC+01:00',
        'Europe/Amsterdam' => 'UTC+01:00',
        'Europe/Lisbon' => 'UTC+00:00',
        'Europe/Warsaw' => 'UTC+01:00',
        'Europe/Minsk' => 'UTC+03:00',

        'America/New_York' => 'UTC-05:00',
        'America/Los_Angeles' => 'UTC-08:00',
        'America/Toronto' => 'UTC-05:00',
        'America/Montreal' => 'UTC-05:00',
        'America/Mexico_City' => 'UTC-06:00',
        'America/Sao_Paulo' => 'UTC-03:00',

        'Africa/Casablanca' => 'UTC+01:00',
        'Africa/Algiers' => 'UTC+01:00',
        'Africa/Tunis' => 'UTC+01:00',
        'Africa/Dakar' => 'UTC+00:00',
        'Africa/Abidjan' => 'UTC+00:00',
        'Africa/Douala' => 'UTC+01:00',

        'Asia/Dubai' => 'UTC+04:00',
        'Asia/Shanghai' => 'UTC+08:00',
        'Asia/Tokyo' => 'UTC+09:00',
        'Asia/Seoul' => 'UTC+09:00',
        'Asia/Bangkok' => 'UTC+07:00',
        'Asia/Kolkata' => 'UTC+05:30',

        'Australia/Sydney' => 'UTC+10:00',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::TIMEZONES as $timezone => $utc) {
            $fuseauHoraire = new FuseauHoraire();

            $fuseauHoraire
                ->setNom($timezone)
                ->setUtc($utc);

            $manager->persist($fuseauHoraire);

            $this->addReference(
                self::FUSEAU_HORAIRE_REFERENCE_PREFIX.$timezone,
                $fuseauHoraire
            );
        }

        $manager->flush();
    }
}
