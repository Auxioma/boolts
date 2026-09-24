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

final class AgenceImmobiliereDashboardTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency_dashboard.title' => 'Tableau de bord immobilier',
                'agency_dashboard.welcome' => 'Bienvenue,',
                'agency_dashboard.account_completion.title' => 'Terminez la création de votre compte pro !',
                'agency_dashboard.account_completion.description' => 'Veuillez déposer les derniers documents afin de valider la création de votre compte.',
                'agency_dashboard.account_completion.country' => 'Confirmez votre pays de domiciliation*',
                'agency_dashboard.continue' => 'Continuer',
                'agency_dashboard.document.formats' => '(Formats acceptés : images et PDF, maximum %maxFileSizeMb% Mo)',
                'agency_dashboard.document.upload' => 'Téléverser',
                'agency_dashboard.document.remove' => 'Supprimer',
                'agency_dashboard.document.sent' => 'Envoyé',
                'agency_dashboard.document.approved' => 'Document validé',
                'agency_dashboard.document.rejected' => 'Document refusé',
                'agency_dashboard.document.limit' => 'Vous avez atteint la limite de dépôt de document',
                'agency_dashboard.support' => 'Pour toute question, contactez le support via l’onglet “aide”.',
                'agency_dashboard.document.submit' => 'Envoyer les documents',
                'agency_dashboard.document.processing' => 'Nous traitons votre dossier.',
                'agency_dashboard.document.change' => 'Changez les documents déposés en cliquant',
                'agency_dashboard.document.here' => 'ici',
                'agency_dashboard.document.success' => 'Les documents ont bien été envoyés !',
                'agency_dashboard.statistics' => 'Statistiques',
                'agency_dashboard.period.7_days' => '7 jours',
                'agency_dashboard.period.30_days' => '30 jours',
                'agency_dashboard.period.12_months' => '12 mois',
                'agency_dashboard.period.maximum' => 'Maximum',
                'agency_dashboard.period.custom' => 'Personnalisé',
                'agency_dashboard.statistics.profile_views' => 'Vues du profil',
                'agency_dashboard.statistics.published_listings' => 'Annonces publiées',
                'agency_dashboard.statistics.listing_views' => 'Vues des annonces',
                'agency_dashboard.statistics.favorites' => 'Mis en favoris',
                'agency_dashboard.date.start' => 'Date de début',
                'agency_dashboard.date.end' => 'Date de fin',
                'agency_dashboard.notifications' => 'Notifications',
                'agency_dashboard.notifications.empty' => 'Aucune notification pour le moment.',
                'agency_dashboard.see_more' => 'Voir plus',
                'agency_dashboard.performance.title' => 'Performances des annonces',
                'agency_dashboard.filters' => 'Filtres',
                'agency_dashboard.sort' => 'Trier',
                'agency_dashboard.sort.aria_label' => 'Trier les résultats',
                'agency_dashboard.sort.created_desc' => 'Date d’ajout (récent → ancien)',
                'agency_dashboard.sort.created_asc' => 'Date d’ajout (ancien → récent)',
                'agency_dashboard.sort.views_desc' => 'Vues (plus → moins)',
                'agency_dashboard.sort.views_asc' => 'Vues (moins → plus)',
                'agency_dashboard.sort.favorites_desc' => 'Favoris (plus → moins)',
                'agency_dashboard.sort.favorites_asc' => 'Favoris (moins → plus)',
                'agency_dashboard.loading_listings' => 'Chargement des annonces...',
                'agency_dashboard.close' => 'Fermer',
                'agency_dashboard.cancel' => 'Annuler',
                'agency_dashboard.apply' => 'Appliquer cette période',
                'agency_dashboard.export' => 'Exporter',
                'agency_dashboard.boost' => 'Booster',
                'agency_dashboard.boost.listing_title' => 'Booster cette annonce',
                'agency_dashboard.boost.empty' => 'Vous n’avez plus de boost disponible. Achetez un pack boost pour mettre cette annonce en avant.',
                'agency_dashboard.boost.remaining' => 'Boosts restants',
                'agency_dashboard.boost.duration' => 'Durée de mise en avant',
                'agency_dashboard.boost.day' => 'jour',
                'agency_dashboard.boost.days' => 'jours',
                'agency_dashboard.boost.charged_on' => 'Boost décompté sur',
                'agency_dashboard.boost.subscription' => 'votre forfait',
                'agency_dashboard.boost.purchased' => 'vos boosts achetés',
                'agency_dashboard.boost.warning' => 'Cette action est définitive : le boost sera activé immédiatement et décompté de votre solde.',
                'agency_dashboard.boost.confirm' => 'Confirmer le boost',
                'agency_dashboard.boost.packs' => 'Voir les packs boost',
                'agency_dashboard.performance.empty' => 'Aucune annonce à afficher.',
                'agency_dashboard.performance.untitled' => 'Annonce sans titre',
                'agency_dashboard.performance.created_at' => 'Créée le',
                'agency_dashboard.sidebar.dashboard' => 'Dashboard',
                'agency_dashboard.sidebar.activity' => 'Mon activité',
                'agency_dashboard.sidebar.listings' => 'Mes biens',
                'agency_dashboard.sidebar.messaging' => 'Messagerie',
                'agency_dashboard.sidebar.account' => 'Mon compte',
                'agency_dashboard.sidebar.options' => 'Options & abonnements',
                'agency_dashboard.sidebar.invoices' => 'Mes factures',
                'agency_dashboard.sidebar.settings' => 'Paramètres',
                'agency_dashboard.sidebar.more_options' => 'Options supplémentaires',
                'agency_dashboard.sidebar.help' => 'Aide',
                'agency_dashboard.sidebar.logout' => 'Déconnexion',
            ],
            'en' => [
                'agency_dashboard.title' => 'Real estate dashboard',
                'agency_dashboard.welcome' => 'Welcome,',
                'agency_dashboard.account_completion.title' => 'Complete your professional account setup!',
                'agency_dashboard.account_completion.description' => 'Please upload the remaining documents to validate your account creation.',
                'agency_dashboard.account_completion.country' => 'Confirm your country of registration*',
                'agency_dashboard.continue' => 'Continue',
                'agency_dashboard.document.formats' => '(Accepted formats: images and PDF, maximum %maxFileSizeMb% MB)',
                'agency_dashboard.document.upload' => 'Upload',
                'agency_dashboard.document.remove' => 'Remove',
                'agency_dashboard.document.sent' => 'Sent',
                'agency_dashboard.document.approved' => 'Document approved',
                'agency_dashboard.document.rejected' => 'Document rejected',
                'agency_dashboard.document.limit' => 'You have reached the document submission limit',
                'agency_dashboard.support' => 'If you have any questions, contact support through the “Help” tab.',
                'agency_dashboard.document.submit' => 'Submit documents',
                'agency_dashboard.document.processing' => 'We are processing your application.',
                'agency_dashboard.document.change' => 'Change the submitted documents by clicking',
                'agency_dashboard.document.here' => 'here',
                'agency_dashboard.document.success' => 'The documents have been submitted successfully!',
                'agency_dashboard.statistics' => 'Statistics',
                'agency_dashboard.period.7_days' => '7 days',
                'agency_dashboard.period.30_days' => '30 days',
                'agency_dashboard.period.12_months' => '12 months',
                'agency_dashboard.period.maximum' => 'Maximum',
                'agency_dashboard.period.custom' => 'Custom',
                'agency_dashboard.statistics.profile_views' => 'Profile views',
                'agency_dashboard.statistics.published_listings' => 'Published listings',
                'agency_dashboard.statistics.listing_views' => 'Listing views',
                'agency_dashboard.statistics.favorites' => 'Added to favorites',
                'agency_dashboard.date.start' => 'Start date',
                'agency_dashboard.date.end' => 'End date',
                'agency_dashboard.notifications' => 'Notifications',
                'agency_dashboard.notifications.empty' => 'No notifications at the moment.',
                'agency_dashboard.see_more' => 'See more',
                'agency_dashboard.performance.title' => 'Listing performance',
                'agency_dashboard.filters' => 'Filters',
                'agency_dashboard.sort' => 'Sort',
                'agency_dashboard.sort.aria_label' => 'Sort results',
                'agency_dashboard.sort.created_desc' => 'Date added (newest → oldest)',
                'agency_dashboard.sort.created_asc' => 'Date added (oldest → newest)',
                'agency_dashboard.sort.views_desc' => 'Views (most → least)',
                'agency_dashboard.sort.views_asc' => 'Views (least → most)',
                'agency_dashboard.sort.favorites_desc' => 'Favorites (most → least)',
                'agency_dashboard.sort.favorites_asc' => 'Favorites (least → most)',
                'agency_dashboard.loading_listings' => 'Loading listings...',
                'agency_dashboard.close' => 'Close',
                'agency_dashboard.cancel' => 'Cancel',
                'agency_dashboard.apply' => 'Apply this period',
                'agency_dashboard.export' => 'Export',
                'agency_dashboard.boost' => 'Boost',
                'agency_dashboard.boost.listing_title' => 'Boost this listing',
                'agency_dashboard.boost.empty' => 'You have no boosts left. Buy a boost pack to promote this listing.',
                'agency_dashboard.boost.remaining' => 'Boosts remaining',
                'agency_dashboard.boost.duration' => 'Promotion duration',
                'agency_dashboard.boost.day' => 'day',
                'agency_dashboard.boost.days' => 'days',
                'agency_dashboard.boost.charged_on' => 'Boost charged to',
                'agency_dashboard.boost.subscription' => 'your plan',
                'agency_dashboard.boost.purchased' => 'your purchased boosts',
                'agency_dashboard.boost.warning' => 'This action is final: the boost will be activated immediately and deducted from your balance.',
                'agency_dashboard.boost.confirm' => 'Confirm boost',
                'agency_dashboard.boost.packs' => 'View boost packs',
                'agency_dashboard.performance.empty' => 'No listings to display.',
                'agency_dashboard.performance.untitled' => 'Untitled listing',
                'agency_dashboard.performance.created_at' => 'Created on',
                'agency_dashboard.sidebar.dashboard' => 'Dashboard',
                'agency_dashboard.sidebar.activity' => 'My activity',
                'agency_dashboard.sidebar.listings' => 'My listings',
                'agency_dashboard.sidebar.messaging' => 'Messages',
                'agency_dashboard.sidebar.account' => 'My account',
                'agency_dashboard.sidebar.options' => 'Plans & subscriptions',
                'agency_dashboard.sidebar.invoices' => 'My invoices',
                'agency_dashboard.sidebar.settings' => 'Settings',
                'agency_dashboard.sidebar.more_options' => 'More options',
                'agency_dashboard.sidebar.help' => 'Help',
                'agency_dashboard.sidebar.logout' => 'Sign out',
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
                $translation->setPage('agency_dashboard');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
