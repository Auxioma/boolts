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

final class MessagerieTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'messaging.conversations' => 'Conversations',
                'messaging.total' => '%count% au total',
                'messaging.filter.all' => 'Toutes',
                'messaging.filter.unread' => 'Non lues',
                'messaging.unread_aria' => '%count% message(s) non lu(s)',
                'messaging.search.contact' => 'Rechercher un contact...',
                'messaging.search.agency' => 'Rechercher une agence…',
                'messaging.search.listing' => 'Rechercher une annonce',
                'messaging.new_conversation' => 'Nouvelle conversation',
                'messaging.empty.agency_contacts' => 'Aucun message reçu depuis votre page agence pour le moment.',
                'messaging.empty.no_contact_match' => 'Aucun contact ne correspond.',
                'messaging.empty.visitor_agencies' => 'Aucune conversation. Contactez une agence depuis sa page.',
                'messaging.empty.no_agency_match' => 'Aucune agence ne correspond.',
                'messaging.empty.visitor_inbox' => 'Aucune conversation. Contactez une agence depuis la page d’un bien.',
                'messaging.empty.no_conversation_match' => 'Aucune conversation ne correspond.',
                'messaging.empty.agency_inbox' => 'Aucun message reçu sur vos annonces pour le moment.',
                'messaging.empty.no_listing_match' => 'Aucune annonce ne correspond.',
                'messaging.count.conversation.one' => '%count% conversation',
                'messaging.count.conversation.other' => '%count% conversations',
                'messaging.count.unread.one' => '%count% non lue',
                'messaging.count.unread.other' => '%count% non lues',
                'messaging.view_conversations' => 'Voir les conversations',
                'messaging.date.today' => 'Aujourd’hui, %time%',
                'messaging.date.yesterday' => 'Hier, %time%',
                'messaging.all_listings' => 'Toutes les annonces',
                'messaging.empty.none' => 'Aucune conversation.',
                'messaging.date.yesterday_short' => 'Hier',
                'messaging.thread.about' => 'À propos de : %title%',
                'messaging.thread.agency' => 'Agence immobilière',
                'messaging.thread.direct_contact' => 'Contact direct',
                'messaging.thread.image_alt' => 'Image envoyée',
                'messaging.thread.kb' => '%size% Ko',
                'messaging.thread.read' => 'Lu',
                'messaging.thread.sent' => 'Envoyé',
                'messaging.thread.empty_agency' => 'Aucun message pour le moment.',
                'messaging.thread.empty_visitor' => 'Écrivez votre premier message à l’agence.',
                'messaging.thread.remove_attachment' => 'Retirer la pièce jointe',
                'messaging.thread.attach' => 'Joindre une image ou un document',
                'messaging.thread.attach_title' => 'Joindre une image ou un document (PDF, Word, Excel…)',
                'messaging.thread.placeholder' => 'Écrivez votre message ici...',
                'messaging.thread.send' => 'Envoyer',
                'messaging.sort' => 'Trier',
                'messaging.delete_conversation' => 'Supprimer la conversation',
                'messaging.attach_image' => 'Joindre une image',
                'messaging.tab.contact' => 'Contact',
                'messaging.tab.customer_service' => 'Service client',
            ],
            'en' => [
                'messaging.conversations' => 'Conversations',
                'messaging.total' => '%count% in total',
                'messaging.filter.all' => 'All',
                'messaging.filter.unread' => 'Unread',
                'messaging.unread_aria' => '%count% unread message(s)',
                'messaging.search.contact' => 'Search for a contact...',
                'messaging.search.agency' => 'Search for an agency…',
                'messaging.search.listing' => 'Search for a listing',
                'messaging.new_conversation' => 'New conversation',
                'messaging.empty.agency_contacts' => 'No messages received from your agency page yet.',
                'messaging.empty.no_contact_match' => 'No matching contact.',
                'messaging.empty.visitor_agencies' => 'No conversations. Contact an agency from its page.',
                'messaging.empty.no_agency_match' => 'No matching agency.',
                'messaging.empty.visitor_inbox' => 'No conversations. Contact an agency from a property page.',
                'messaging.empty.no_conversation_match' => 'No matching conversation.',
                'messaging.empty.agency_inbox' => 'No messages received on your listings yet.',
                'messaging.empty.no_listing_match' => 'No matching listing.',
                'messaging.count.conversation.one' => '%count% conversation',
                'messaging.count.conversation.other' => '%count% conversations',
                'messaging.count.unread.one' => '%count% unread',
                'messaging.count.unread.other' => '%count% unread',
                'messaging.view_conversations' => 'View conversations',
                'messaging.date.today' => 'Today, %time%',
                'messaging.date.yesterday' => 'Yesterday, %time%',
                'messaging.all_listings' => 'All listings',
                'messaging.empty.none' => 'No conversations.',
                'messaging.date.yesterday_short' => 'Yesterday',
                'messaging.thread.about' => 'About: %title%',
                'messaging.thread.agency' => 'Real estate agency',
                'messaging.thread.direct_contact' => 'Direct contact',
                'messaging.thread.image_alt' => 'Sent image',
                'messaging.thread.kb' => '%size% KB',
                'messaging.thread.read' => 'Read',
                'messaging.thread.sent' => 'Sent',
                'messaging.thread.empty_agency' => 'No messages yet.',
                'messaging.thread.empty_visitor' => 'Write your first message to the agency.',
                'messaging.thread.remove_attachment' => 'Remove attachment',
                'messaging.thread.attach' => 'Attach an image or a document',
                'messaging.thread.attach_title' => 'Attach an image or a document (PDF, Word, Excel…)',
                'messaging.thread.placeholder' => 'Write your message here...',
                'messaging.thread.send' => 'Send',
                'messaging.sort' => 'Sort',
                'messaging.delete_conversation' => 'Delete conversation',
                'messaging.attach_image' => 'Attach an image',
                'messaging.tab.contact' => 'Contact',
                'messaging.tab.customer_service' => 'Customer service',
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
                $translation->setPage('messagerie');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
