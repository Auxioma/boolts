<?php

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
            ['name' => 'Maison', 'icone' => 'icon-house'],
            ['name' => 'Appartement', 'icone' => 'icon-building-2'],
            ['name' => 'Villa', 'icone' => 'icon-landmark'],
            ['name' => 'Fond de commerce', 'icone' => 'icon-wallet'],
            ['name' => 'Bureaux', 'icone' => 'icon-wallet'],
            ['name' => 'Local commercial', 'icone' => 'icon-store'],
            ['name' => 'Terrain', 'icone' => 'icon-hammer'],
            ['name' => 'Ferme', 'icone' => 'icon-truck'],
            ['name' => 'Parking/Garage/Box', 'icone' => 'icon-circle-parking'],
        ];

        foreach ($categories as $categoryData) {
            $category = new CategoryBien();

            $slug = strtolower(
                $this->slugger->slug($categoryData['name'])->toString()
            );

            $category
                ->setName($categoryData['name'])
                ->setSlug($slug)
                ->setIcone($categoryData['icone']);

            $manager->persist($category);

            $this->addReference(
                self::CATEGORY_BIEN_REFERENCE_PREFIX . $slug,
                $category
            );
        }

        $manager->flush();
    }
}
