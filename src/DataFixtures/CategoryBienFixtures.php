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

use App\Entity\CategoryBien;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryBienFixtures extends Fixture
{
    public const CATEGORY_BIEN_REFERENCE_PREFIX = 'category_bien_';

    public function __construct(
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [
            [
                'icone' => 'icon-house',
                'translations' => [
                    'fr' => 'Maison',
                    'en' => 'House',
                ],
            ],
            [
                'icone' => 'icon-building-2',
                'translations' => [
                    'fr' => 'Appartement',
                    'en' => 'Apartment',
                ],
            ],
            [
                'icone' => 'icon-landmark',
                'translations' => [
                    'fr' => 'Villa',
                    'en' => 'Villa',
                ],
            ],
            [
                'icone' => 'icon-wallet',
                'translations' => [
                    'fr' => 'Fond de commerce',
                    'en' => 'Business assets',
                ],
            ],
            [
                'icone' => 'icon-wallet',
                'translations' => [
                    'fr' => 'Bureaux',
                    'en' => 'Offices',
                ],
            ],
            [
                'icone' => 'icon-store',
                'translations' => [
                    'fr' => 'Local commercial',
                    'en' => 'Commercial premises',
                ],
            ],
            [
                'icone' => 'icon-hammer',
                'translations' => [
                    'fr' => 'Terrain',
                    'en' => 'Land',
                ],
            ],
            [
                'icone' => 'icon-truck',
                'translations' => [
                    'fr' => 'Ferme',
                    'en' => 'Farm',
                ],
            ],
            [
                'icone' => 'icon-circle-parking',
                'translations' => [
                    'fr' => 'Parking/Garage/Box',
                    'en' => 'Parking/Garage/Box',
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = new CategoryBien();
            $category->setIcone($categoryData['icone']);

            foreach ($categoryData['translations'] as $locale => $name) {
                $slug = mb_strtolower(
                    $this->slugger->slug($name)->toString()
                );

                $category->translate($locale)->setName($name);
                $category->translate($locale)->setSlug($slug);
            }

            $category->mergeNewTranslations();

            $manager->persist($category);

            $referenceSlug = mb_strtolower(
                $this->slugger->slug($categoryData['translations']['fr'])->toString()
            );

            $this->addReference(
                self::CATEGORY_BIEN_REFERENCE_PREFIX.$referenceSlug,
                $category
            );
        }

        $manager->flush();
    }
}
