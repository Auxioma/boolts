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

class LanguesFixtures extends Fixture
{
    public const LANGUES_REFERENCE_PREFIX = 'langues_';

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
        foreach (self::LANGUAGES as $iso => $name) {
            $langue = new Langues();

            $langue
                ->setIso($iso)
                ->setName($name);

            $manager->persist($langue);

            $this->addReference(
                self::LANGUES_REFERENCE_PREFIX.$iso,
                $langue
            );
        }

        $manager->flush();
    }
}
