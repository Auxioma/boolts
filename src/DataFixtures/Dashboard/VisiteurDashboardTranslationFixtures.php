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

namespace App\DataFixtures\Dashboard;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VisiteurDashboardTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'visitor.favorites.title' => 'Mes favoris',
                'visitor.favorites.empty' => 'Vous n’avez encore aucun bien en favoris.',
                'visitor.favorites.login_required' => 'Connectez-vous pour consulter vos favoris.',
                'messaging.tab.listing' => 'Annonce',
                'messaging.tab.agencies' => 'Agences',
                'dashboard.more' => 'Plus',
                'dashboard.bottom_bar.aria_label' => 'Navigation du dashboard',
            ],
            'en' => [
                'visitor.favorites.title' => 'My favorites',
                'visitor.favorites.empty' => 'You don’t have any favorite properties yet.',
                'visitor.favorites.login_required' => 'Sign in to view your favorites.',
                'messaging.tab.listing' => 'Listing',
                'messaging.tab.agencies' => 'Agencies',
                'dashboard.more' => 'More',
                'dashboard.bottom_bar.aria_label' => 'Dashboard navigation',
            ],
            'es' => [
                'visitor.favorites.title' => 'Mis favoritos',
                'visitor.favorites.empty' => 'Aún no tienes ningún inmueble en favoritos.',
                'visitor.favorites.login_required' => 'Inicia sesión para ver tus favoritos.',
                'messaging.tab.listing' => 'Anuncio',
                'messaging.tab.agencies' => 'Agencias',
                'dashboard.more' => 'Más',
                'dashboard.bottom_bar.aria_label' => 'Navegación del panel',
            ],
            'de' => [
                'visitor.favorites.title' => 'Meine Favoriten',
                'visitor.favorites.empty' => 'Sie haben noch keine Lieblingsimmobilien.',
                'visitor.favorites.login_required' => 'Melden Sie sich an, um Ihre Favoriten zu sehen.',
                'messaging.tab.listing' => 'Anzeige',
                'messaging.tab.agencies' => 'Agenturen',
                'dashboard.more' => 'Mehr',
                'dashboard.bottom_bar.aria_label' => 'Dashboard-Navigation',
            ],
            'pt' => [
                'visitor.favorites.title' => 'Os meus favoritos',
                'visitor.favorites.empty' => 'Ainda não tem nenhum imóvel nos favoritos.',
                'visitor.favorites.login_required' => 'Inicie sessão para consultar os seus favoritos.',
                'messaging.tab.listing' => 'Anúncio',
                'messaging.tab.agencies' => 'Agências',
                'dashboard.more' => 'Mais',
                'dashboard.bottom_bar.aria_label' => 'Navegação do painel',
            ],
            'it' => [
                'visitor.favorites.title' => 'I miei preferiti',
                'visitor.favorites.empty' => 'Non hai ancora nessun immobile tra i preferiti.',
                'visitor.favorites.login_required' => 'Accedi per vedere i tuoi preferiti.',
                'messaging.tab.listing' => 'Annuncio',
                'messaging.tab.agencies' => 'Agenzie',
                'dashboard.more' => 'Altro',
                'dashboard.bottom_bar.aria_label' => 'Navigazione della dashboard',
            ],
            'nl' => [
                'visitor.favorites.title' => 'Mijn favorieten',
                'visitor.favorites.empty' => 'Je hebt nog geen favoriete woningen.',
                'visitor.favorites.login_required' => 'Log in om je favorieten te bekijken.',
                'messaging.tab.listing' => 'Advertentie',
                'messaging.tab.agencies' => 'Kantoren',
                'dashboard.more' => 'Meer',
                'dashboard.bottom_bar.aria_label' => 'Dashboardnavigatie',
            ],
            'hu' => [
                'visitor.favorites.title' => 'Kedvenceim',
                'visitor.favorites.empty' => 'Még nincs kedvenc ingatlana.',
                'visitor.favorites.login_required' => 'Jelentkezzen be kedvencei megtekintéséhez.',
                'messaging.tab.listing' => 'Hirdetés',
                'messaging.tab.agencies' => 'Ügynökségek',
                'dashboard.more' => 'Továbbiak',
                'dashboard.bottom_bar.aria_label' => 'Irányítópult navigáció',
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
                $translation->setPage('dashboard_visiteur');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
