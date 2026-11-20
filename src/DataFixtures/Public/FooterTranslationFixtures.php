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

namespace App\DataFixtures\Public;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class FooterTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'footer.tab.locality' => 'Par localité',
                'footer.tab.property_type' => 'Par type de bien',
                'footer.tab.features' => 'Par caractéristiques',
                'footer.feature.pool' => 'Avec piscine',
                'footer.feature.garden' => 'Avec jardin',
                'footer.feature.garage' => 'Avec garage',
                'footer.feature.elevator' => 'Avec ascenseur',
                'footer.feature.balcony' => 'Avec balcon',
                'footer.feature.terrace' => 'Avec terrasse',
                'footer.feature.furnished' => 'Meublé',
                'footer.feature.parking' => 'Avec parking',
                'footer.feature.cellar' => 'Avec cave',
                'footer.feature.new' => 'Neuf',
                'footer.feature.sea_view' => 'Avec vue mer',
                'footer.feature.air_conditioning' => 'Avec climatisation',
                'footer.feature.fireplace' => 'Avec cheminée',
                'footer.feature.renovated' => 'Rénové',
                'footer.feature.dressing' => 'Avec dressing',
                'footer.feature.transport' => 'Proche transports',
                'footer.feature.alarm' => 'Avec alarme',
                'footer.feature.open_view' => 'Avec vue dégagée',
                'footer.company.title' => 'Entreprise',
                'footer.company.about' => 'Qui sommes-nous ?',
                'footer.company.careers' => 'Recrutement',
                'footer.company.press' => 'Espace presse',
                'footer.support.title' => 'Assistance',
                'footer.support.help' => 'Besoin d’aide ?',
                'footer.support.safety' => 'Assistance sécurité',
                'footer.support.cancellation' => 'Options d’annulation',
                'footer.discover.title' => 'Découvrir',
                'footer.discover.cities' => 'Toutes les villes',
                'footer.discover.departments' => 'Tous les départements',
                'footer.discover.regions' => 'Toutes les régions',
                'footer.app.title' => 'App mobile',
                'footer.app.subtitle' => 'Découvrez l’application',
                'footer.app.app_store' => 'Télécharger sur l\'App Store',
                'footer.app.google_play' => 'Télécharger sur Google Play',
                'footer.copyright' => '© 2026 Boolts, Inc. Confidentialité - Conditions générales - Fonctionnement du site - Infos sur l’entreprise',
                'footer.current_language' => 'Français',
            ],
            'en' => [
                'footer.tab.locality' => 'By location',
                'footer.tab.property_type' => 'By property type',
                'footer.tab.features' => 'By features',
                'footer.feature.pool' => 'With pool',
                'footer.feature.garden' => 'With garden',
                'footer.feature.garage' => 'With garage',
                'footer.feature.elevator' => 'With elevator',
                'footer.feature.balcony' => 'With balcony',
                'footer.feature.terrace' => 'With terrace',
                'footer.feature.furnished' => 'Furnished',
                'footer.feature.parking' => 'With parking',
                'footer.feature.cellar' => 'With cellar',
                'footer.feature.new' => 'New build',
                'footer.feature.sea_view' => 'With sea view',
                'footer.feature.air_conditioning' => 'With air conditioning',
                'footer.feature.fireplace' => 'With fireplace',
                'footer.feature.renovated' => 'Renovated',
                'footer.feature.dressing' => 'With walk-in closet',
                'footer.feature.transport' => 'Near public transport',
                'footer.feature.alarm' => 'With alarm',
                'footer.feature.open_view' => 'With open view',
                'footer.company.title' => 'Company',
                'footer.company.about' => 'About us',
                'footer.company.careers' => 'Careers',
                'footer.company.press' => 'Press',
                'footer.support.title' => 'Support',
                'footer.support.help' => 'Need help?',
                'footer.support.safety' => 'Safety support',
                'footer.support.cancellation' => 'Cancellation options',
                'footer.discover.title' => 'Discover',
                'footer.discover.cities' => 'All cities',
                'footer.discover.departments' => 'All departments',
                'footer.discover.regions' => 'All regions',
                'footer.app.title' => 'Mobile app',
                'footer.app.subtitle' => 'Discover the app',
                'footer.app.app_store' => 'Download on the App Store',
                'footer.app.google_play' => 'Get it on Google Play',
                'footer.copyright' => '© 2026 Boolts, Inc. Privacy - Terms and conditions - How the site works - Company information',
                'footer.current_language' => 'English',
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
                $translation->setPage('footer');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
