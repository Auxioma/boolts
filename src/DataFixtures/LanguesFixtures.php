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

use App\Entity\Langues;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class LanguesFixtures extends Fixture
{
    public const LANGUE_FRANCAIS = 'langue_francais';
    public const LANGUE_ANGLAIS = 'langue_anglais';

    public function load(ObjectManager $manager): void
    {
        $langues = [
            ['name' => 'Français', 'iso' => 'fr'],
            ['name' => 'Anglais', 'iso' => 'en'],
            ['name' => 'Allemand', 'iso' => 'de'],
            ['name' => 'Espagnol', 'iso' => 'es'],
            ['name' => 'Italien', 'iso' => 'it'],
            ['name' => 'Portugais', 'iso' => 'pt'],
            ['name' => 'Néerlandais', 'iso' => 'nl'],
            ['name' => 'Polonais', 'iso' => 'pl'],
            ['name' => 'Roumain', 'iso' => 'ro'],
            ['name' => 'Grec', 'iso' => 'el'],
            ['name' => 'Suédois', 'iso' => 'sv'],
            ['name' => 'Danois', 'iso' => 'da'],
            ['name' => 'Finnois', 'iso' => 'fi'],
            ['name' => 'Norvégien', 'iso' => 'no'],
            ['name' => 'Tchèque', 'iso' => 'cs'],
            ['name' => 'Slovaque', 'iso' => 'sk'],
            ['name' => 'Slovène', 'iso' => 'sl'],
            ['name' => 'Croate', 'iso' => 'hr'],
            ['name' => 'Serbe', 'iso' => 'sr'],
            ['name' => 'Bulgare', 'iso' => 'bg'],
            ['name' => 'Hongrois', 'iso' => 'hu'],
            ['name' => 'Estonien', 'iso' => 'et'],
            ['name' => 'Letton', 'iso' => 'lv'],
            ['name' => 'Lituanien', 'iso' => 'lt'],
            ['name' => 'Irlandais', 'iso' => 'ga'],
            ['name' => 'Maltais', 'iso' => 'mt'],
            ['name' => 'Islandais', 'iso' => 'is'],
            ['name' => 'Ukrainien', 'iso' => 'uk'],
            ['name' => 'Russe', 'iso' => 'ru'],
            ['name' => 'Turc', 'iso' => 'tr'],
            ['name' => 'Albanais', 'iso' => 'sq'],
            ['name' => 'Macédonien', 'iso' => 'mk'],
            ['name' => 'Bosnien', 'iso' => 'bs'],
        ];

        foreach ($langues as $data) {
            $langue = new Langues();
            $langue->setName($data['name']);
            $langue->setIso($data['iso']);

            $manager->persist($langue);

            if ('fr' === $data['iso']) {
                $this->addReference(self::LANGUE_FRANCAIS, $langue);
            }

            if ('en' === $data['iso']) {
                $this->addReference(self::LANGUE_ANGLAIS, $langue);
            }
        }

        $manager->flush();
    }
}
