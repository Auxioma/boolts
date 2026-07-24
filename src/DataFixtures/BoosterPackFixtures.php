<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Booster\BoosterPack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class BoosterPackFixtures extends Fixture
{
    public const BOOSTER_PACK_REFERENCE_PREFIX = 'booster_pack_';

    private const PACKS = [
        [
            'code' => 'boost-1',
            'name' => '1 boost',
            'description' => '1 boost s’applique sur 1 annonce et dure 15 jours.',
            'boostQuantity' => 1,
            'boostDurationDays' => 15,
            'position' => 1,
        ],
        [
            'code' => 'boost-5',
            'name' => '5 boosts',
            'description' => '5 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
            'boostQuantity' => 5,
            'boostDurationDays' => 15,
            'position' => 2,
        ],
        [
            'code' => 'boost-20',
            'name' => '20 boosts',
            'description' => '20 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
            'boostQuantity' => 20,
            'boostDurationDays' => 15,
            'position' => 3,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PACKS as $data) {
            $pack = new BoosterPack();
            $pack
                ->setCode($data['code'])
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setBoostQuantity($data['boostQuantity'])
                ->setBoostDurationDays($data['boostDurationDays'])
                ->setIsActive(true)
                ->setPosition($data['position']);

            $manager->persist($pack);
            $this->addReference(self::BOOSTER_PACK_REFERENCE_PREFIX.$data['code'], $pack);
        }

        $manager->flush();
    }
}
