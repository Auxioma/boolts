<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 */

namespace App\DataFixtures\Dashboard;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class BoostBalanceTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'boost_balance.title' => 'Boosts forfait : %plan% - Boosts indépendants : %independent%',
                'boost_balance.count' => 'Boost : %count%',
                'boost_balance.detail' => '(forfait %plan% + indépendants %independent%)',
                'boost_balance.label' => 'Boost :',
                'boost_balance.listings.count' => 'Annonces : %count%',
                'boost_balance.listings.remaining' => 'Annonces restantes :',
                'boost_balance.listings.unlimited' => 'Illimité',
            ],
            'en' => [
                'boost_balance.title' => 'Plan boosts: %plan% - Independent boosts: %independent%',
                'boost_balance.count' => 'Boost: %count%',
                'boost_balance.detail' => '(plan %plan% + independent %independent%)',
                'boost_balance.label' => 'Boost:',
                'boost_balance.listings.count' => 'Listings: %count%',
                'boost_balance.listings.remaining' => 'Remaining listings:',
                'boost_balance.listings.unlimited' => 'Unlimited',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = FixtureEntityHelper::findOrCreate($manager, Translation::class, [
                    'translationKey' => $key,
                    'locale' => $locale,
                ]);
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('boost_balance');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
