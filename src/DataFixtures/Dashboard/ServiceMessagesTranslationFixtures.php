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

final class ServiceMessagesTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'boost.error.not_owner' => 'Vous ne pouvez pas booster cette annonce.',
                'boost.error.not_eligible' => 'Seules les annonces publiées ou en pause peuvent être boostées.',
                'boost.error.already_boosted' => 'Cette annonce est déjà boostée.',
                'boost.error.no_balance' => 'Vous n’avez plus de boost disponible. Achetez un pack boost pour en obtenir.',
                'billing.plan_change.current_price_not_found' => 'Le tarif de l’abonnement actuel est introuvable.',
                'billing.plan_change.must_be_active' => 'L’abonnement actuel doit être actif pour programmer un changement de forfait.',
                'billing.plan_change.cancel_scheduled' => 'Une résiliation est déjà programmée ; annulez-la avant de changer de forfait.',
                'billing.plan_change.use_cancellation' => 'Utilisez la résiliation pour repasser sur l’offre gratuite.',
                'billing.plan_change.same_currency' => 'La devise du nouveau forfait doit être identique.',
                'billing.plan_change.upgrade_immediate' => 'Le passage à un forfait de montant supérieur ou égal est immédiat, pas différé.',
                'billing.plan_change.period_end_unknown' => 'La date de fin de période de l’abonnement est inconnue.',
                'billing.plan_change.none_scheduled' => 'Aucun changement de forfait n’est programmé.',
                'messaging.error.empty' => 'Le message est vide.',
                'messaging.error.too_long' => 'Le message ne doit pas dépasser %max% caractères.',
                'messaging.error.image_formats' => 'Formats acceptés : JPG, PNG, WEBP ou GIF.',
                'messaging.error.document_formats' => 'Formats acceptés : images (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument ou texte.',
                'auth.error.professional_account' => 'Ce compte est un compte professionnel. Merci de vous connecter depuis votre espace pro.',
            ],
            'en' => [
                'boost.error.not_owner' => 'You cannot boost this listing.',
                'boost.error.not_eligible' => 'Only published or paused listings can be boosted.',
                'boost.error.already_boosted' => 'This listing is already boosted.',
                'boost.error.no_balance' => 'You have no boosts left. Buy a boost pack to get more.',
                'billing.plan_change.current_price_not_found' => 'The current subscription price could not be found.',
                'billing.plan_change.must_be_active' => 'The current subscription must be active to schedule a plan change.',
                'billing.plan_change.cancel_scheduled' => 'A cancellation is already scheduled; cancel it before changing plans.',
                'billing.plan_change.use_cancellation' => 'Use cancellation to switch back to the free plan.',
                'billing.plan_change.same_currency' => 'The new plan’s currency must be the same.',
                'billing.plan_change.upgrade_immediate' => 'Switching to a plan of equal or higher price is immediate, not deferred.',
                'billing.plan_change.period_end_unknown' => 'The subscription period end date is unknown.',
                'billing.plan_change.none_scheduled' => 'No plan change is scheduled.',
                'messaging.error.empty' => 'The message is empty.',
                'messaging.error.too_long' => 'The message must not exceed %max% characters.',
                'messaging.error.image_formats' => 'Accepted formats: JPG, PNG, WEBP or GIF.',
                'messaging.error.document_formats' => 'Accepted formats: images (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument or text.',
                'auth.error.professional_account' => 'This is a professional account. Please sign in from your professional area.',
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
                $translation->setPage('service_messages');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
