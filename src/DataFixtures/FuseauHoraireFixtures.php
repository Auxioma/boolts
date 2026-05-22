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

use App\Entity\FuseauHoraire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FuseauHoraireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        foreach (\DateTimeZone::listIdentifiers() as $timezoneIdentifier) {
            $timezone = new \DateTimeZone($timezoneIdentifier);
            $date = $now->setTimezone($timezone);

            $offset = $timezone->getOffset($date);

            $hours = intdiv(abs($offset), 3600);
            $minutes = intdiv(abs($offset) % 3600, 60);
            $sign = $offset >= 0 ? '+' : '-';

            $utc = \sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);

            $city = $this->formatTimezoneName($timezoneIdentifier);

            $nom = \sprintf('(%s) %s', $utc, $city);

            $existingFuseauHoraire = $manager
                ->getRepository(FuseauHoraire::class)
                ->findOneBy(['nom' => $nom]);

            if ($existingFuseauHoraire instanceof FuseauHoraire) {
                continue;
            }

            $fuseauHoraire = new FuseauHoraire();
            $fuseauHoraire->setNom($nom);

            $manager->persist($fuseauHoraire);
        }

        $manager->flush();
    }

    private function formatTimezoneName(string $timezoneIdentifier): string
    {
        $parts = explode('/', $timezoneIdentifier);
        $name = end($parts);

        return str_replace('_', ' ', $name);
    }
}
