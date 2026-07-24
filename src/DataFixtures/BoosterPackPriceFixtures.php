<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Booster\BoosterPack;
use App\Entity\Booster\BoosterPackPrice;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class BoosterPackPriceFixtures extends Fixture implements DependentFixtureInterface
{
    private const PRICES = [
        'boost-1' => 2499,
        'boost-5' => 9999,
        'boost-20' => 29999,
    ];

    public function load(ObjectManager $manager): void
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les prix des packs boost.');
        }

        foreach (self::PRICES as $packCode => $amountMinor) {
            $pack = $this->getReference(
                BoosterPackFixtures::BOOSTER_PACK_REFERENCE_PREFIX.$packCode,
                BoosterPack::class,
            );

            $price = new BoosterPackPrice();
            $price
                ->setBoosterPack($pack)
                ->setCurrency($currency)
                ->setAmountMinor($amountMinor)
                ->setIsActive(true);

            $manager->persist($price);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaysFixtures::class,
            BoosterPackFixtures::class,
        ];
    }
}
