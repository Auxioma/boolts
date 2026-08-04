<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Booster\BoosterPack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class BoosterPackFixtures extends Fixture
{
    public const BOOSTER_PACK_REFERENCE_PREFIX = 'booster_pack_';

    public function load(ObjectManager $manager): void
    {
        foreach (BillingFixtureData::BOOSTER_PACKS as $data) {
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
