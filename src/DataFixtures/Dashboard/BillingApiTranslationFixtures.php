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

final class BillingApiTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'api.error.invalid_csrf' => 'Jeton CSRF invalide.',
                'api.error.unauthenticated' => 'Utilisateur non authentifié.',
                'api.error.invalid_json' => 'Requête JSON invalide.',
                'billing.payment_method.invalid_setup_intent' => 'SetupIntent invalide.',
                'billing.payment_method.profile_not_found' => 'Profil de facturation introuvable.',
                'billing.payment_method.not_validated' => 'La carte n’est pas validée. Statut Stripe : %status%.',
                'billing.payment_method.setup_intent_not_owned' => 'Ce SetupIntent ne vous appartient pas.',
                'billing.payment_method.stripe_not_found' => 'Moyen de paiement Stripe introuvable.',
                'billing.payment_method.not_a_card' => 'Le moyen de paiement obtenu n’est pas une carte.',
                'billing.payment_method.no_fingerprint' => 'Stripe n’a retourné aucune empreinte.',
                'billing.payment_method.already_saved' => 'Cette carte est déjà enregistrée.',
                'billing.payment_method.already_saved_last4' => 'Cette carte est déjà enregistrée et se termine par %last4%.',
                'billing.payment_method.saved' => 'Le moyen de paiement a bien été enregistré.',
                'billing.payment_method.not_found' => 'Moyen de paiement introuvable.',
                'billing.payment_method.already_deleted' => 'Cette carte est déjà supprimée.',
                'billing.payment_method.deleted' => 'Le moyen de paiement a bien été supprimé.',
                'billing.subscription.invalid_plan' => 'Forfait invalide.',
                'billing.subscription.currency_unavailable' => 'Ce forfait n’est pas proposé dans votre devise.',
                'billing.subscription.free_plan' => 'Ce forfait gratuit ne nécessite pas de paiement Stripe.',
                'billing.subscription.profile_not_found' => 'Profil de facturation ou client Stripe introuvable.',
                'billing.subscription.no_card' => 'Aucune carte bancaire valide n’est disponible pour cet achat.',
                'billing.subscription.active' => 'Votre abonnement est actif.',
                'billing.subscription.create_error' => 'Impossible de créer l’abonnement.',
                'billing.subscription.current_price_not_found' => 'Le prix de l’abonnement actuel est introuvable.',
                'billing.subscription.already_current' => 'C’est déjà votre forfait actuel.',
                'billing.subscription.schedule_error' => 'Impossible de programmer le changement de forfait.',
                'billing.subscription.downgrade_scheduled' => 'Votre passage au forfait %plan% sera effectif le %date%. Aucun paiement aujourd’hui : il sera prélevé à cette date lors du renouvellement.',
                'billing.subscription.end_of_period' => 'à la fin de votre période en cours',
                'billing.subscription.must_be_active' => 'L’abonnement actuel doit être actif pour changer de forfait.',
                'billing.subscription.same_currency' => 'La devise du nouveau forfait doit être identique.',
                'billing.subscription.higher_only' => 'Vous pouvez uniquement choisir un forfait d’un montant supérieur.',
                'billing.subscription.new_plan_active' => 'Votre nouveau forfait est actif.',
                'billing.subscription.change_error' => 'Impossible de changer de forfait.',
            ],
            'en' => [
                'api.error.invalid_csrf' => 'Invalid CSRF token.',
                'api.error.unauthenticated' => 'User not authenticated.',
                'api.error.invalid_json' => 'Invalid JSON request.',
                'billing.payment_method.invalid_setup_intent' => 'Invalid SetupIntent.',
                'billing.payment_method.profile_not_found' => 'Billing profile not found.',
                'billing.payment_method.not_validated' => 'The card is not validated. Stripe status: %status%.',
                'billing.payment_method.setup_intent_not_owned' => 'This SetupIntent does not belong to you.',
                'billing.payment_method.stripe_not_found' => 'Stripe payment method not found.',
                'billing.payment_method.not_a_card' => 'The payment method obtained is not a card.',
                'billing.payment_method.no_fingerprint' => 'Stripe returned no fingerprint.',
                'billing.payment_method.already_saved' => 'This card is already saved.',
                'billing.payment_method.already_saved_last4' => 'This card is already saved and ends with %last4%.',
                'billing.payment_method.saved' => 'The payment method has been saved.',
                'billing.payment_method.not_found' => 'Payment method not found.',
                'billing.payment_method.already_deleted' => 'This card has already been deleted.',
                'billing.payment_method.deleted' => 'The payment method has been deleted.',
                'billing.subscription.invalid_plan' => 'Invalid plan.',
                'billing.subscription.currency_unavailable' => 'This plan is not offered in your currency.',
                'billing.subscription.free_plan' => 'This free plan does not require a Stripe payment.',
                'billing.subscription.profile_not_found' => 'Billing profile or Stripe customer not found.',
                'billing.subscription.no_card' => 'No valid bank card is available for this purchase.',
                'billing.subscription.active' => 'Your subscription is active.',
                'billing.subscription.create_error' => 'Unable to create the subscription.',
                'billing.subscription.current_price_not_found' => 'The current subscription price could not be found.',
                'billing.subscription.already_current' => 'This is already your current plan.',
                'billing.subscription.schedule_error' => 'Unable to schedule the plan change.',
                'billing.subscription.downgrade_scheduled' => 'Your switch to the %plan% plan will take effect on %date%. No payment today: it will be charged on that date at renewal.',
                'billing.subscription.end_of_period' => 'at the end of your current period',
                'billing.subscription.must_be_active' => 'The current subscription must be active to change plans.',
                'billing.subscription.same_currency' => 'The new plan’s currency must be the same.',
                'billing.subscription.higher_only' => 'You can only choose a higher-priced plan.',
                'billing.subscription.new_plan_active' => 'Your new plan is active.',
                'billing.subscription.change_error' => 'Unable to change plans.',
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
                $translation->setPage('billing_api');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
