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

use App\Entity\LangueParler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LangueParlerFixtures extends Fixture
{
    public const LANGUE_PARLER_REFERENCE_PREFIX = 'langue_parler_';

    private const LANGUAGES = [
        'fr' => 'Français',
        'en' => 'Anglais',
        'es' => 'Espagnol',
        'de' => 'Allemand',
        'it' => 'Italien',
        'pt' => 'Portugais',
        'nl' => 'Néerlandais',
        'pl' => 'Polonais',
        'ru' => 'Russe',
        'be' => 'Biélorusse',
        'ar' => 'Arabe',
        'zh' => 'Chinois',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::LANGUAGES as $code => $name) {
            $langueParler = new LangueParler();

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
