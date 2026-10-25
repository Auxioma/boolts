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

final class AgenceImmobiliereNotificationsTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency_notifications.page_title' => 'Notifications',
                'agency_notifications.title' => 'Notifications',
                'agency_notifications.filter.all' => 'Toutes',
                'agency_notifications.filter.unread' => 'Non lues',
                'agency_notifications.empty' => 'Vous n\'avez aucune notification.',
                'agency_notifications.message.account_pending_documents' => 'Votre compte est en attente de validation. Déposez les documents demandés.',
                'agency_notifications.message.documents_rejected' => 'Un ou plusieurs de vos documents n’ont pas été acceptés. Redéposez les documents.',
                'agency_notifications.message.account_validated' => 'Votre compte a été validé.',
                'agency_notifications.message.plan_activated' => 'Votre abonnement %plan% a été activé.',
                'agency_notifications.message.boost_added.one' => '%count% crédit Boost a été ajouté à votre compte.',
                'agency_notifications.message.boost_added.other' => '%count% crédits Boost ont été ajoutés à votre compte.',
                'agency_notifications.message.boost_low_balance.one' => 'Votre solde de crédits Boost est faible (%count% restant).',
                'agency_notifications.message.boost_low_balance.other' => 'Votre solde de crédits Boost est faible (%count% restants).',
                'agency_notifications.message.boost_active' => 'Le Boost de « %name% » est désormais actif.',
                'agency_notifications.message.boost_active_untitled' => 'Le Boost de votre annonce est désormais actif.',
                'agency_notifications.message.property_pending' => 'L’annonce %property% est en attente d’acceptation.',
                'agency_notifications.message.property_pending_untitled' => 'L’annonce est en attente d’acceptation.',
                'agency_notifications.message.property_accepted' => 'L’annonce %property% a été acceptée.',
                'agency_notifications.message.property_accepted_untitled' => 'L’annonce a été acceptée.',
                'agency_notifications.message.property_refused' => 'L’annonce %property% est refusée. Modifiez-la.',
                'agency_notifications.message.property_refused_untitled' => 'L’annonce est refusée. Modifiez-la.',
            ],
            'en' => [
                'agency_notifications.page_title' => 'Notifications',
                'agency_notifications.title' => 'Notifications',
                'agency_notifications.filter.all' => 'All',
                'agency_notifications.filter.unread' => 'Unread',
                'agency_notifications.empty' => 'You have no notifications.',
                'agency_notifications.message.account_pending_documents' => 'Your account is pending validation. Upload the requested documents.',
                'agency_notifications.message.documents_rejected' => 'One or more of your documents were not accepted. Upload the documents again.',
                'agency_notifications.message.account_validated' => 'Your account has been validated.',
                'agency_notifications.message.plan_activated' => 'Your %plan% subscription has been activated.',
                'agency_notifications.message.boost_added.one' => '%count% Boost credit has been added to your account.',
                'agency_notifications.message.boost_added.other' => '%count% Boost credits have been added to your account.',
                'agency_notifications.message.boost_low_balance.one' => 'Your Boost credit balance is low (%count% remaining).',
                'agency_notifications.message.boost_low_balance.other' => 'Your Boost credit balance is low (%count% remaining).',
                'agency_notifications.message.boost_active' => 'The Boost for “%name%” is now active.',
                'agency_notifications.message.boost_active_untitled' => 'The Boost for your listing is now active.',
                'agency_notifications.message.property_pending' => 'The listing %property% is pending approval.',
                'agency_notifications.message.property_pending_untitled' => 'The listing is pending approval.',
                'agency_notifications.message.property_accepted' => 'The listing %property% has been accepted.',
                'agency_notifications.message.property_accepted_untitled' => 'The listing has been accepted.',
                'agency_notifications.message.property_refused' => 'The listing %property% has been rejected. Edit it.',
                'agency_notifications.message.property_refused_untitled' => 'The listing has been rejected. Edit it.',
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
                $translation->setPage('agency_notifications');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
