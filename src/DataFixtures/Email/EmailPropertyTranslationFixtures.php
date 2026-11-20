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

namespace App\DataFixtures\Email;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class EmailPropertyTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.common.hello_name' => 'Bonjour %name%,',
                'email.common.not_provided' => 'Non renseigné',
                'email.common.not_provided_f' => 'Non renseignée',
                'email.property.label.status' => 'Statut',
                'email.property.label.transaction' => 'Type de transaction',
                'email.property.label.type' => 'Type de bien',
                'email.property.label.city' => 'Ville',
                'email.property.view_online' => 'Voir l’annonce en ligne :',
                'email.property.view_listings' => 'Voir mes biens',
                'email.property.published.subject' => 'Votre annonce est publiée',
                'email.property.published.title' => 'Votre annonce est en ligne',
                'email.property.published.text' => 'Bonne nouvelle : votre annonce « %title% » a été validée et elle est désormais publiée sur Boolts.',
                'email.property.manage' => 'Vous pouvez gérer votre annonce depuis votre espace Mes biens :',
                'email.property.boost_activated.subject' => 'Le boost de votre annonce est activé',
                'email.property.boost_activated.title' => 'Votre annonce est boostée',
                'email.property.boost_activated.text' => 'Le boost de votre annonce « %title% » vient d’être activé. Elle sera mise en avant sur Boolts.',
                'email.property.boost_activated.text_until' => 'Le boost de votre annonce « %title% » vient d’être activé. Elle sera mise en avant sur Boolts jusqu’au %date%.',
                'email.property.label.boost_until' => 'Boost actif jusqu’au',
                'email.property.deleted.subject' => 'Votre annonce a été supprimée',
                'email.property.deleted.text' => 'Votre annonce « %title% » a été supprimée. Elle n’apparaît plus sur Boolts et ne peut plus être consultée par les visiteurs.',
                'email.property.deleted.manage' => 'Vous pouvez créer une nouvelle annonce depuis votre espace Mes biens :',
                'email.property.deleted.not_intended' => 'Si cette suppression n’est pas volontaire, contactez l’équipe Boolts au plus vite.',
                'email.property.paused.subject' => 'Votre annonce a été mise en pause',
                'email.property.paused.title' => 'Votre annonce est en pause',
                'email.property.paused.text' => 'Votre annonce « %title% » a été mise en pause. Elle n’est plus visible publiquement sur Boolts tant qu’elle n’a pas été réactivée.',
                'email.property.paused.manage' => 'Vous pouvez la réactiver à tout moment depuis votre espace Mes biens :',
                'email.property.reactivated.subject' => 'Votre annonce a été réactivée',
                'email.property.reactivated.title' => 'Votre annonce est de nouveau en ligne',
                'email.property.reactivated.text' => 'Votre annonce « %title% » a été réactivée et elle est de nouveau visible publiquement sur Boolts.',
                'email.property.submission_pending.subject' => 'Votre annonce a été envoyée pour validation',
                'email.property.submission_pending.title' => 'Votre annonce a bien été envoyée',
                'email.property.submission_pending.text' => 'Votre annonce « %title% » a été créée et elle est actuellement en cours d’analyse par Boolts.',
                'email.property.submission_pending.manage' => 'Vous pouvez suivre son statut depuis votre espace Mes biens :',
                'email.booster.subject' => 'Confirmation de votre achat — Pack boost %name%',
                'email.booster.title' => 'Votre achat est confirmé',
                'email.booster.intro' => 'Nous vous confirmons l’achat du <strong>pack boost %name%</strong>.',
                'email.booster.added.one' => '<strong>1 crédit Boost</strong> vient d’être ajouté à votre compte.',
                'email.booster.added.other' => '<strong>%count% crédits Boost</strong> viennent d’être ajoutés à votre compte.',
                'email.booster.label.pack' => 'Pack',
                'email.booster.label.credits' => 'Crédits Boost ajoutés',
                'email.booster.label.duration' => 'Durée de mise en avant par boost',
                'email.booster.label.amount' => 'Montant réglé',
                'email.booster.label.reference' => 'Référence de paiement',
                'email.booster.label.date' => 'Date d’achat',
                'email.booster.label.expires' => 'Crédits valables jusqu’au',
                'email.booster.days.one' => '%count% jour',
                'email.booster.days.other' => '%count% jours',
                'email.booster.use.one' => 'Vous pouvez dès à présent utiliser ce crédit pour booster une annonce depuis votre espace Mes biens :',
                'email.booster.use.other' => 'Vous pouvez dès à présent utiliser ces crédits pour booster vos annonces depuis votre espace Mes biens :',
                'email.booster.options' => 'Retrouvez le détail de vos achats et de votre solde de crédits Boost dans vos options :',
                'email.booster.options_link' => 'Mes options',
            ],
            'en' => [
                'email.common.hello_name' => 'Hello %name%,',
                'email.common.not_provided' => 'Not provided',
                'email.common.not_provided_f' => 'Not provided',
                'email.property.label.status' => 'Status',
                'email.property.label.transaction' => 'Transaction type',
                'email.property.label.type' => 'Property type',
                'email.property.label.city' => 'City',
                'email.property.view_online' => 'View the listing online:',
                'email.property.view_listings' => 'View my properties',
                'email.property.published.subject' => 'Your listing is published',
                'email.property.published.title' => 'Your listing is online',
                'email.property.published.text' => 'Good news: your listing “%title%” has been approved and is now published on Boolts.',
                'email.property.manage' => 'You can manage your listing from your My properties area:',
                'email.property.boost_activated.subject' => 'The boost for your listing is active',
                'email.property.boost_activated.title' => 'Your listing is boosted',
                'email.property.boost_activated.text' => 'The boost for your listing “%title%” has just been activated. It will be featured on Boolts.',
                'email.property.boost_activated.text_until' => 'The boost for your listing “%title%” has just been activated. It will be featured on Boolts until %date%.',
                'email.property.label.boost_until' => 'Boost active until',
                'email.property.deleted.subject' => 'Your listing has been deleted',
                'email.property.deleted.text' => 'Your listing “%title%” has been deleted. It no longer appears on Boolts and can no longer be viewed by visitors.',
                'email.property.deleted.manage' => 'You can create a new listing from your My properties area:',
                'email.property.deleted.not_intended' => 'If this deletion was not intentional, contact the Boolts team as soon as possible.',
                'email.property.paused.subject' => 'Your listing has been paused',
                'email.property.paused.title' => 'Your listing is paused',
                'email.property.paused.text' => 'Your listing “%title%” has been paused. It is no longer publicly visible on Boolts until it is reactivated.',
                'email.property.paused.manage' => 'You can reactivate it at any time from your My properties area:',
                'email.property.reactivated.subject' => 'Your listing has been reactivated',
                'email.property.reactivated.title' => 'Your listing is online again',
                'email.property.reactivated.text' => 'Your listing “%title%” has been reactivated and is publicly visible on Boolts again.',
                'email.property.submission_pending.subject' => 'Your listing has been submitted for review',
                'email.property.submission_pending.title' => 'Your listing has been submitted',
                'email.property.submission_pending.text' => 'Your listing “%title%” has been created and is currently being reviewed by Boolts.',
                'email.property.submission_pending.manage' => 'You can track its status from your My properties area:',
                'email.booster.subject' => 'Purchase confirmation — Boost pack %name%',
                'email.booster.title' => 'Your purchase is confirmed',
                'email.booster.intro' => 'We confirm the purchase of the <strong>%name% boost pack</strong>.',
                'email.booster.added.one' => '<strong>1 Boost credit</strong> has just been added to your account.',
                'email.booster.added.other' => '<strong>%count% Boost credits</strong> have just been added to your account.',
                'email.booster.label.pack' => 'Pack',
                'email.booster.label.credits' => 'Boost credits added',
                'email.booster.label.duration' => 'Featured duration per boost',
                'email.booster.label.amount' => 'Amount paid',
                'email.booster.label.reference' => 'Payment reference',
                'email.booster.label.date' => 'Purchase date',
                'email.booster.label.expires' => 'Credits valid until',
                'email.booster.days.one' => '%count% day',
                'email.booster.days.other' => '%count% days',
                'email.booster.use.one' => 'You can now use this credit to boost a listing from your My properties area:',
                'email.booster.use.other' => 'You can now use these credits to boost your listings from your My properties area:',
                'email.booster.options' => 'Find the details of your purchases and your Boost credit balance in your options:',
                'email.booster.options_link' => 'My options',
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
                $translation->setPage('email_property');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
