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
            'es' => [
                'boost_balance.title' => 'Boosts del plan: %plan% - Boosts independientes: %independent%',
                'boost_balance.count' => 'Boost: %count%',
                'boost_balance.detail' => '(plan %plan% + independientes %independent%)',
                'boost_balance.label' => 'Boost:',
                'boost_balance.listings.count' => 'Anuncios: %count%',
                'boost_balance.listings.remaining' => 'Anuncios restantes:',
                'boost_balance.listings.unlimited' => 'Ilimitado',
            ],
            'de' => [
                'boost_balance.title' => 'Tarif-Boosts: %plan% - Separate Boosts: %independent%',
                'boost_balance.count' => 'Boost: %count%',
                'boost_balance.detail' => '(Tarif %plan% + separat %independent%)',
                'boost_balance.label' => 'Boost:',
                'boost_balance.listings.count' => 'Anzeigen: %count%',
                'boost_balance.listings.remaining' => 'Verbleibende Anzeigen:',
                'boost_balance.listings.unlimited' => 'Unbegrenzt',
            ],
            'pt' => [
                'boost_balance.title' => 'Boosts do plano: %plan% - Boosts independentes: %independent%',
                'boost_balance.count' => 'Boost: %count%',
                'boost_balance.detail' => '(plano %plan% + independentes %independent%)',
                'boost_balance.label' => 'Boost:',
                'boost_balance.listings.count' => 'Anúncios: %count%',
                'boost_balance.listings.remaining' => 'Anúncios restantes:',
                'boost_balance.listings.unlimited' => 'Ilimitado',
            ],
            'it' => [
                'boost_balance.title' => 'Boost del piano: %plan% - Boost indipendenti: %independent%',
                'boost_balance.count' => 'Boost: %count%',
                'boost_balance.detail' => '(piano %plan% + indipendenti %independent%)',
                'boost_balance.label' => 'Boost:',
                'boost_balance.listings.count' => 'Annunci: %count%',
                'boost_balance.listings.remaining' => 'Annunci rimanenti:',
                'boost_balance.listings.unlimited' => 'Illimitato',
            ],
            'nl' => [
                'boost_balance.title' => 'Abonnementsboosts: %plan% - Losse boosts: %independent%',
                'boost_balance.count' => 'Boost: %count%',
                'boost_balance.detail' => '(abonnement %plan% + los %independent%)',
                'boost_balance.label' => 'Boost:',
                'boost_balance.listings.count' => 'Advertenties: %count%',
                'boost_balance.listings.remaining' => 'Resterende advertenties:',
                'boost_balance.listings.unlimited' => 'Onbeperkt',
            ],
            'hu' => [
                'boost_balance.title' => 'Csomag kiemelései: %plan% - Külön kiemelések: %independent%',
                'boost_balance.count' => 'Kiemelés: %count%',
                'boost_balance.detail' => '(csomag %plan% + külön %independent%)',
                'boost_balance.label' => 'Kiemelés:',
                'boost_balance.listings.count' => 'Hirdetések: %count%',
                'boost_balance.listings.remaining' => 'Fennmaradó hirdetések:',
                'boost_balance.listings.unlimited' => 'Korlátlan',
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
