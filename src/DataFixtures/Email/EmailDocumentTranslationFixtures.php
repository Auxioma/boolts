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

final class EmailDocumentTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.common.legal' => 'Boolts est une marque déposée. Instagram et Facebook sont des marques de Meta Inc., déposées aux États-Unis et dans d’autres pays.',
                'email.common.received' => 'Vous avez reçu ce message parce que vous vous êtes inscrit sur notre site le %date%.',
                'email.common.support.text' => 'Vous avez des soucis pour vous connecter ?',
                'email.common.support.contact' => 'Contactez notre support :',
                'email.common.unsubscribe_text' => 'Vous ne souhaitez plus recevoir d’e-mail de notre part ?',
                'email.common.unsubscribe_link' => 'Cliquez ici.',
                'email.document.rejected.subject' => 'Un ou plusieurs de vos documents n’ont pas été acceptés',
                'email.document.rejected.text' => 'Bonjour %name%, après vérification, un ou plusieurs de vos documents ne répondent pas aux critères nécessaires. Consultez votre tableau de bord pour connaître les motifs du refus et effectuer les modifications demandées.',
                'email.document.go_dashboard' => 'Aller à mon tableau de bord',
                'email.document.deletion.subject' => 'Votre compte Boolts sera bientôt supprimé',
                'email.document.deletion.title.one' => 'Votre compte sera supprimé dans %count% jour',
                'email.document.deletion.title.other' => 'Votre compte sera supprimé dans %count% jours',
                'email.document.deletion.text' => 'Bonjour %name%, vos documents obligatoires n’ont pas tous été transmis ou certains doivent être remplacés. Sans action de votre part, votre compte Boolts sera supprimé le %date%.',
                'email.document.deletion.cta' => 'Envoyer mes documents',
                'email.document.deletion.subject_days.one' => 'Votre compte Boolts sera supprimé dans %count% jour',
                'email.document.deletion.subject_days.other' => 'Votre compte Boolts sera supprimé dans %count% jours',
                'email.document.validated.subject' => 'Vos documents ont été validés',
                'email.document.validated.meta_title' => 'Vos documents ont été validés — Boolts',
                'email.document.validated.aria' => 'Confirmation de validation des documents',
                'email.document.validated.title' => 'Vos documents ont bien été validés,<br>vous êtes bien inscrit sur %link% !',
                'email.document.validated.checked' => 'Vos documents ont été vérifiés et acceptés par nos équipes.',
                'email.document.validated.active' => 'Votre compte est désormais actif et vous pouvez dès maintenant profiter de l’ensemble de nos fonctionnalités. Nous sommes ravis de vous accueillir parmi nous.',
                'email.document.validated.start' => 'Pour commencer, vous pouvez vous connecter à votre espace et personnaliser votre profil afin de tirer le meilleur de l’expérience Boolts.',
                'email.document.validated.help' => 'Si vous avez la moindre question ou besoin d’assistance, notre équipe est disponible pour vous aider.',
                'email.document.validated.welcome' => 'Bienvenue à bord, et merci de votre confiance.',
                'email.document.validated.cta' => 'Découvrir mon tableau de bord',
                'email.common.socials' => 'Réseaux sociaux',
                'email.document.admin.subject' => 'Nouveaux documents en attente de validation',
                'email.document.admin.text' => 'De nouveaux documents sont en attente de validation dans l’administration du site internet.',
                'email.document.admin.attached' => 'Les fichiers transmis sont joints à cet e-mail.',
                'email.document.admin.agency' => 'Agence',
                'email.document.admin.unknown_agency' => 'Agence inconnue',
                'email.document.admin.agency_email' => 'E-mail agence',
                'email.document.admin.to_validate' => 'Documents à valider',
                'email.document.admin.document' => 'Document',
                'email.document.admin.file' => 'Fichier',
                'email.document.admin.submitted_at' => 'Transmis le',
                'email.document.admin.unknown_document' => 'Document non identifié',
                'email.document.admin.check' => 'Vous pouvez le contrôler depuis l’administration Boolts.',
            ],
            'en' => [
                'email.common.legal' => 'Boolts is a registered trademark. Instagram and Facebook are trademarks of Meta Inc., registered in the U.S. and other countries.',
                'email.common.received' => 'You received this message because you registered on our website on %date%.',
                'email.common.support.text' => 'Having trouble signing in?',
                'email.common.support.contact' => 'Contact our support:',
                'email.common.unsubscribe_text' => 'Do you no longer wish to receive emails from us?',
                'email.common.unsubscribe_link' => 'Click here.',
                'email.document.rejected.subject' => 'One or more of your documents were not accepted',
                'email.document.rejected.text' => 'Hello %name%, after review, one or more of your documents do not meet the required criteria. Check your dashboard to see the reasons for the rejection and make the requested changes.',
                'email.document.go_dashboard' => 'Go to my dashboard',
                'email.document.deletion.subject' => 'Your Boolts account will soon be deleted',
                'email.document.deletion.title.one' => 'Your account will be deleted in %count% day',
                'email.document.deletion.title.other' => 'Your account will be deleted in %count% days',
                'email.document.deletion.text' => 'Hello %name%, not all of your mandatory documents have been submitted, or some need to be replaced. Without action on your part, your Boolts account will be deleted on %date%.',
                'email.document.deletion.cta' => 'Send my documents',
                'email.document.deletion.subject_days.one' => 'Your Boolts account will be deleted in %count% day',
                'email.document.deletion.subject_days.other' => 'Your Boolts account will be deleted in %count% days',
                'email.document.validated.subject' => 'Your documents have been validated',
                'email.document.validated.meta_title' => 'Your documents have been validated — Boolts',
                'email.document.validated.aria' => 'Document validation confirmation',
                'email.document.validated.title' => 'Your documents have been validated,<br>you are now registered on %link%!',
                'email.document.validated.checked' => 'Your documents have been checked and accepted by our teams.',
                'email.document.validated.active' => 'Your account is now active and you can start enjoying all of our features right away. We are delighted to welcome you.',
                'email.document.validated.start' => 'To get started, you can sign in to your account and customize your profile to get the most out of the Boolts experience.',
                'email.document.validated.help' => 'If you have any questions or need assistance, our team is available to help you.',
                'email.document.validated.welcome' => 'Welcome aboard, and thank you for your trust.',
                'email.document.validated.cta' => 'Discover my dashboard',
                'email.common.socials' => 'Social networks',
                'email.document.admin.subject' => 'New documents awaiting validation',
                'email.document.admin.text' => 'New documents are awaiting validation in the website administration.',
                'email.document.admin.attached' => 'The submitted files are attached to this email.',
                'email.document.admin.agency' => 'Agency',
                'email.document.admin.unknown_agency' => 'Unknown agency',
                'email.document.admin.agency_email' => 'Agency email',
                'email.document.admin.to_validate' => 'Documents to validate',
                'email.document.admin.document' => 'Document',
                'email.document.admin.file' => 'File',
                'email.document.admin.submitted_at' => 'Submitted on',
                'email.document.admin.unknown_document' => 'Unidentified document',
                'email.document.admin.check' => 'You can review it from the Boolts administration.',
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
                $translation->setPage('email_document');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
