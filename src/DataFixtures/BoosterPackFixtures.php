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
