<?php

namespace App\DataFixtures;

use App\Entity\Pays;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class PaysFixtures extends Fixture
{
    public const PAYS_REFERENCE_PREFIX = 'pays_';

    public function load(ObjectManager $manager): void
    {
        $paysEurope = [
            ['nom' => 'Albanie', 'iso' => 'AL'],
            ['nom' => 'Allemagne', 'iso' => 'DE'],
            ['nom' => 'Andorre', 'iso' => 'AD'],
            ['nom' => 'Autriche', 'iso' => 'AT'],
            ['nom' => 'Belgique', 'iso' => 'BE'],
            ['nom' => 'France', 'iso' => 'FR'],
            ['nom' => 'Italie', 'iso' => 'IT'],
            ['nom' => 'Espagne', 'iso' => 'ES'],
            ['nom' => 'Suisse', 'iso' => 'CH'],
            ['nom' => 'Portugal', 'iso' => 'PT'],
        ];

        foreach ($paysEurope as $data) {
            $pays = new Pays();
            $pays
                ->setNom($data['nom'])
                ->setIso($data['iso']);

            $manager->persist($pays);

            $this->addReference(
                self::PAYS_REFERENCE_PREFIX.$data['iso'],
                $pays
            );
        }

        $manager->flush();
    }
}
