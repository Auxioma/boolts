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
            'es' => [
                'agency_notifications.page_title' => 'Notificaciones',
                'agency_notifications.title' => 'Notificaciones',
                'agency_notifications.filter.all' => 'Todas',
                'agency_notifications.filter.unread' => 'No leídas',
                'agency_notifications.empty' => 'No tienes notificaciones.',
                'agency_notifications.message.account_pending_documents' => 'Tu cuenta está pendiente de validación. Sube los documentos solicitados.',
                'agency_notifications.message.documents_rejected' => 'Uno o varios de tus documentos no han sido aceptados. Vuelve a subirlos.',
                'agency_notifications.message.account_validated' => 'Tu cuenta ha sido validada.',
                'agency_notifications.message.plan_activated' => 'Tu suscripción %plan% se ha activado.',
                'agency_notifications.message.boost_added.one' => 'Se ha añadido %count% crédito Boost a tu cuenta.',
                'agency_notifications.message.boost_added.other' => 'Se han añadido %count% créditos Boost a tu cuenta.',
                'agency_notifications.message.boost_low_balance.one' => 'Tu saldo de créditos Boost es bajo (queda %count%).',
                'agency_notifications.message.boost_low_balance.other' => 'Tu saldo de créditos Boost es bajo (quedan %count%).',
                'agency_notifications.message.boost_active' => 'El Boost de «%name%» ya está activo.',
                'agency_notifications.message.boost_active_untitled' => 'El Boost de tu anuncio ya está activo.',
                'agency_notifications.message.property_pending' => 'El anuncio %property% está pendiente de aprobación.',
                'agency_notifications.message.property_pending_untitled' => 'El anuncio está pendiente de aprobación.',
                'agency_notifications.message.property_accepted' => 'El anuncio %property% ha sido aceptado.',
                'agency_notifications.message.property_accepted_untitled' => 'El anuncio ha sido aceptado.',
                'agency_notifications.message.property_refused' => 'El anuncio %property% ha sido rechazado. Modifícalo.',
                'agency_notifications.message.property_refused_untitled' => 'El anuncio ha sido rechazado. Modifícalo.',
            ],
            'de' => [
                'agency_notifications.page_title' => 'Benachrichtigungen',
                'agency_notifications.title' => 'Benachrichtigungen',
                'agency_notifications.filter.all' => 'Alle',
                'agency_notifications.filter.unread' => 'Ungelesen',
                'agency_notifications.empty' => 'Sie haben keine Benachrichtigungen.',
                'agency_notifications.message.account_pending_documents' => 'Ihr Konto wartet auf Freigabe. Laden Sie die angeforderten Dokumente hoch.',
                'agency_notifications.message.documents_rejected' => 'Eines oder mehrere Ihrer Dokumente wurden nicht akzeptiert. Laden Sie die Dokumente erneut hoch.',
                'agency_notifications.message.account_validated' => 'Ihr Konto wurde freigegeben.',
                'agency_notifications.message.plan_activated' => 'Ihr Abonnement %plan% wurde aktiviert.',
                'agency_notifications.message.boost_added.one' => '%count% Boost-Guthaben wurde Ihrem Konto hinzugefügt.',
                'agency_notifications.message.boost_added.other' => '%count% Boost-Guthaben wurden Ihrem Konto hinzugefügt.',
                'agency_notifications.message.boost_low_balance.one' => 'Ihr Boost-Guthaben ist niedrig (%count% verbleibend).',
                'agency_notifications.message.boost_low_balance.other' => 'Ihr Boost-Guthaben ist niedrig (%count% verbleibend).',
                'agency_notifications.message.boost_active' => 'Der Boost für „%name%“ ist jetzt aktiv.',
                'agency_notifications.message.boost_active_untitled' => 'Der Boost Ihrer Anzeige ist jetzt aktiv.',
                'agency_notifications.message.property_pending' => 'Die Anzeige %property% wartet auf Freigabe.',
                'agency_notifications.message.property_pending_untitled' => 'Die Anzeige wartet auf Freigabe.',
                'agency_notifications.message.property_accepted' => 'Die Anzeige %property% wurde angenommen.',
                'agency_notifications.message.property_accepted_untitled' => 'Die Anzeige wurde angenommen.',
                'agency_notifications.message.property_refused' => 'Die Anzeige %property% wurde abgelehnt. Bearbeiten Sie sie.',
                'agency_notifications.message.property_refused_untitled' => 'Die Anzeige wurde abgelehnt. Bearbeiten Sie sie.',
            ],
            'pt' => [
                'agency_notifications.page_title' => 'Notificações',
                'agency_notifications.title' => 'Notificações',
                'agency_notifications.filter.all' => 'Todas',
                'agency_notifications.filter.unread' => 'Não lidas',
                'agency_notifications.empty' => 'Não tem notificações.',
                'agency_notifications.message.account_pending_documents' => 'A sua conta aguarda validação. Carregue os documentos solicitados.',
                'agency_notifications.message.documents_rejected' => 'Um ou mais dos seus documentos não foram aceites. Volte a carregar os documentos.',
                'agency_notifications.message.account_validated' => 'A sua conta foi validada.',
                'agency_notifications.message.plan_activated' => 'A sua subscrição %plan% foi ativada.',
                'agency_notifications.message.boost_added.one' => 'Foi adicionado %count% crédito Boost à sua conta.',
                'agency_notifications.message.boost_added.other' => 'Foram adicionados %count% créditos Boost à sua conta.',
                'agency_notifications.message.boost_low_balance.one' => 'O seu saldo de créditos Boost é baixo (resta %count%).',
                'agency_notifications.message.boost_low_balance.other' => 'O seu saldo de créditos Boost é baixo (restam %count%).',
                'agency_notifications.message.boost_active' => 'O Boost de “%name%” está agora ativo.',
                'agency_notifications.message.boost_active_untitled' => 'O Boost do seu anúncio está agora ativo.',
                'agency_notifications.message.property_pending' => 'O anúncio %property% aguarda aprovação.',
                'agency_notifications.message.property_pending_untitled' => 'O anúncio aguarda aprovação.',
                'agency_notifications.message.property_accepted' => 'O anúncio %property% foi aceite.',
                'agency_notifications.message.property_accepted_untitled' => 'O anúncio foi aceite.',
                'agency_notifications.message.property_refused' => 'O anúncio %property% foi recusado. Edite-o.',
                'agency_notifications.message.property_refused_untitled' => 'O anúncio foi recusado. Edite-o.',
            ],
            'it' => [
                'agency_notifications.page_title' => 'Notifiche',
                'agency_notifications.title' => 'Notifiche',
                'agency_notifications.filter.all' => 'Tutte',
                'agency_notifications.filter.unread' => 'Non lette',
                'agency_notifications.empty' => 'Non hai notifiche.',
                'agency_notifications.message.account_pending_documents' => 'Il tuo account è in attesa di convalida. Carica i documenti richiesti.',
                'agency_notifications.message.documents_rejected' => 'Uno o più dei tuoi documenti non sono stati accettati. Caricali di nuovo.',
                'agency_notifications.message.account_validated' => 'Il tuo account è stato convalidato.',
                'agency_notifications.message.plan_activated' => 'Il tuo abbonamento %plan% è stato attivato.',
                'agency_notifications.message.boost_added.one' => '%count% credito Boost è stato aggiunto al tuo account.',
                'agency_notifications.message.boost_added.other' => '%count% crediti Boost sono stati aggiunti al tuo account.',
                'agency_notifications.message.boost_low_balance.one' => 'Il tuo saldo di crediti Boost è basso (%count% rimanente).',
                'agency_notifications.message.boost_low_balance.other' => 'Il tuo saldo di crediti Boost è basso (%count% rimanenti).',
                'agency_notifications.message.boost_active' => 'Il Boost di “%name%” è ora attivo.',
                'agency_notifications.message.boost_active_untitled' => 'Il Boost del tuo annuncio è ora attivo.',
                'agency_notifications.message.property_pending' => 'L’annuncio %property% è in attesa di approvazione.',
                'agency_notifications.message.property_pending_untitled' => 'L’annuncio è in attesa di approvazione.',
                'agency_notifications.message.property_accepted' => 'L’annuncio %property% è stato accettato.',
                'agency_notifications.message.property_accepted_untitled' => 'L’annuncio è stato accettato.',
                'agency_notifications.message.property_refused' => 'L’annuncio %property% è stato rifiutato. Modificalo.',
                'agency_notifications.message.property_refused_untitled' => 'L’annuncio è stato rifiutato. Modificalo.',
            ],
            'nl' => [
                'agency_notifications.page_title' => 'Meldingen',
                'agency_notifications.title' => 'Meldingen',
                'agency_notifications.filter.all' => 'Alle',
                'agency_notifications.filter.unread' => 'Ongelezen',
                'agency_notifications.empty' => 'Je hebt geen meldingen.',
                'agency_notifications.message.account_pending_documents' => 'Je account wacht op validatie. Upload de gevraagde documenten.',
                'agency_notifications.message.documents_rejected' => 'Een of meer van je documenten zijn niet geaccepteerd. Upload de documenten opnieuw.',
                'agency_notifications.message.account_validated' => 'Je account is gevalideerd.',
                'agency_notifications.message.plan_activated' => 'Je abonnement %plan% is geactiveerd.',
                'agency_notifications.message.boost_added.one' => 'Er is %count% Boost-tegoed aan je account toegevoegd.',
                'agency_notifications.message.boost_added.other' => 'Er zijn %count% Boost-tegoeden aan je account toegevoegd.',
                'agency_notifications.message.boost_low_balance.one' => 'Je Boost-tegoed is laag (nog %count% over).',
                'agency_notifications.message.boost_low_balance.other' => 'Je Boost-tegoed is laag (nog %count% over).',
                'agency_notifications.message.boost_active' => 'De Boost voor “%name%” is nu actief.',
                'agency_notifications.message.boost_active_untitled' => 'De Boost van je advertentie is nu actief.',
                'agency_notifications.message.property_pending' => 'De advertentie %property% wacht op goedkeuring.',
                'agency_notifications.message.property_pending_untitled' => 'De advertentie wacht op goedkeuring.',
                'agency_notifications.message.property_accepted' => 'De advertentie %property% is geaccepteerd.',
                'agency_notifications.message.property_accepted_untitled' => 'De advertentie is geaccepteerd.',
                'agency_notifications.message.property_refused' => 'De advertentie %property% is afgewezen. Pas ze aan.',
                'agency_notifications.message.property_refused_untitled' => 'De advertentie is afgewezen. Pas ze aan.',
            ],
            'hu' => [
                'agency_notifications.page_title' => 'Értesítések',
                'agency_notifications.title' => 'Értesítések',
                'agency_notifications.filter.all' => 'Összes',
                'agency_notifications.filter.unread' => 'Olvasatlan',
                'agency_notifications.empty' => 'Nincs értesítése.',
                'agency_notifications.message.account_pending_documents' => 'Fiókja jóváhagyásra vár. Töltse fel a kért dokumentumokat.',
                'agency_notifications.message.documents_rejected' => 'Egy vagy több dokumentumát nem fogadtuk el. Töltse fel újra a dokumentumokat.',
                'agency_notifications.message.account_validated' => 'Fiókját jóváhagytuk.',
                'agency_notifications.message.plan_activated' => '%plan% előfizetése aktiválva.',
                'agency_notifications.message.boost_added.one' => '%count% kiemelési kreditet jóváírtunk fiókjában.',
                'agency_notifications.message.boost_added.other' => '%count% kiemelési kreditet jóváírtunk fiókjában.',
                'agency_notifications.message.boost_low_balance.one' => 'Kiemelési kreditegyenlege alacsony (%count% maradt).',
                'agency_notifications.message.boost_low_balance.other' => 'Kiemelési kreditegyenlege alacsony (%count% maradt).',
                'agency_notifications.message.boost_active' => 'A(z) „%name%” kiemelése mostantól aktív.',
                'agency_notifications.message.boost_active_untitled' => 'Hirdetése kiemelése mostantól aktív.',
                'agency_notifications.message.property_pending' => 'A(z) %property% hirdetés jóváhagyásra vár.',
                'agency_notifications.message.property_pending_untitled' => 'A hirdetés jóváhagyásra vár.',
                'agency_notifications.message.property_accepted' => 'A(z) %property% hirdetést elfogadtuk.',
                'agency_notifications.message.property_accepted_untitled' => 'A hirdetést elfogadtuk.',
                'agency_notifications.message.property_refused' => 'A(z) %property% hirdetést elutasítottuk. Módosítsa.',
                'agency_notifications.message.property_refused_untitled' => 'A hirdetést elutasítottuk. Módosítsa.',
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
