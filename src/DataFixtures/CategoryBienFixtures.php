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

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTranslation;
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
                    'es' => 'Casa',
                    'de' => 'Haus',
                    'pt' => 'Moradia',
                    'it' => 'Casa',
                    'nl' => 'Huis',
                    'hu' => 'Ház',
                ],
            ],
            [
                'icone' => 'icon-building-2',
                'translations' => [
                    'fr' => 'Appartement',
                    'en' => 'Apartment',
                    'es' => 'Piso',
                    'de' => 'Wohnung',
                    'pt' => 'Apartamento',
                    'it' => 'Appartamento',
                    'nl' => 'Appartement',
                    'hu' => 'Lakás',
                ],
            ],
            [
                'icone' => 'icon-landmark',
                'translations' => [
                    'fr' => 'Villa',
                    'en' => 'Villa',
                    'es' => 'Villa',
                    'de' => 'Villa',
                    'pt' => 'Vivenda',
                    'it' => 'Villa',
                    'nl' => 'Villa',
                    'hu' => 'Villa',
                ],
            ],
            [
                'icone' => 'icon-wallet',
                'translations' => [
                    'fr' => 'Fond de commerce',
                    'en' => 'Business assets',
                    'es' => 'Traspaso de negocio',
                    'de' => 'Gewerbebetrieb',
                    'pt' => 'Trespasse',
                    'it' => 'Attività commerciale',
                    'nl' => 'Handelszaak',
                    'hu' => 'Üzletátruházás',
                ],
            ],
            [
                'icone' => 'icon-wallet',
                'translations' => [
                    'fr' => 'Bureaux',
                    'en' => 'Offices',
                    'es' => 'Oficinas',
                    'de' => 'Büros',
                    'pt' => 'Escritórios',
                    'it' => 'Uffici',
                    'nl' => 'Kantoren',
                    'hu' => 'Irodák',
                ],
            ],
            [
                'icone' => 'icon-store',
                'translations' => [
                    'fr' => 'Local commercial',
                    'en' => 'Commercial premises',
                    'es' => 'Local comercial',
                    'de' => 'Gewerbefläche',
                    'pt' => 'Loja comercial',
                    'it' => 'Locale commerciale',
                    'nl' => 'Winkelruimte',
                    'hu' => 'Üzlethelyiség',
                ],
            ],
            [
                'icone' => 'icon-hammer',
                'translations' => [
                    'fr' => 'Terrain',
                    'en' => 'Land',
                    'es' => 'Terreno',
                    'de' => 'Grundstück',
                    'pt' => 'Terreno',
                    'it' => 'Terreno',
                    'nl' => 'Grond',
                    'hu' => 'Telek',
                ],
            ],
            [
                'icone' => 'icon-truck',
                'translations' => [
                    'fr' => 'Ferme',
                    'en' => 'Farm',
                    'es' => 'Granja',
                    'de' => 'Bauernhof',
                    'pt' => 'Quinta',
                    'it' => 'Fattoria',
                    'nl' => 'Boerderij',
                    'hu' => 'Tanya',
                ],
            ],
            [
                'icone' => 'icon-circle-parking',
                'translations' => [
                    'fr' => 'Parking/Garage/Box',
                    'en' => 'Parking/Garage/Box',
                    'es' => 'Parking/Garaje/Trastero',
                    'de' => 'Parkplatz/Garage/Box',
                    'pt' => 'Estacionamento/Garagem/Box',
                    'it' => 'Parcheggio/Garage/Box',
                    'nl' => 'Parkeerplaats/Garage/Box',
                    'hu' => 'Parkoló/Garázs/Box',
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $referenceSlug = mb_strtolower(
                $this->slugger->slug($categoryData['translations']['fr'])->toString()
            );
            $translation = $manager->getRepository(CategoryBienTranslation::class)->findOneBy([
                'locale' => 'fr',
                'slug' => $referenceSlug,
            ]);
            $category = $translation?->getTranslatable();
            if (!$category instanceof CategoryBien) {
                $category = new CategoryBien();
                $manager->persist($category);
            }
            $category->setIcone($categoryData['icone']);

            foreach ($categoryData['translations'] as $locale => $name) {
                $slug = mb_strtolower(
                    $this->slugger->slug($name)->toString()
                );

                $category->translate($locale)->setName($name);
                $category->translate($locale)->setSlug($slug);
            }

            $category->mergeNewTranslations();

            $this->addReference(
                self::CATEGORY_BIEN_REFERENCE_PREFIX.$referenceSlug,
                $category
            );
        }

        $manager->flush();
    }
}
