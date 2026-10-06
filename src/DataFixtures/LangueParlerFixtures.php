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

use App\Data\LanguageCatalog;
use App\Entity\LangueParler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LangueParlerFixtures extends Fixture
{
    public const LANGUE_PARLER_REFERENCE_PREFIX = 'langue_parler_';

    public function load(ObjectManager $manager): void
    {
        foreach (LanguageCatalog::LANGUAGES as $code => ['name' => $name]) {
            $langueParler = FixtureEntityHelper::findOrCreate($manager, LangueParler::class, [
                'code' => $code,
            ]);

            $langueParler
                ->setCode($code)
                ->setName($name);

            $manager->persist($langueParler);

            $this->addReference(
                self::LANGUE_PARLER_REFERENCE_PREFIX.$code,
                $langueParler
            );
        }

        $manager->flush();
    }
}
