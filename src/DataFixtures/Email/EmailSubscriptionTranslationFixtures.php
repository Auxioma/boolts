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

final class EmailSubscriptionTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'email.common.hello' => 'Bonjour,',
                'email.common.signature' => 'À bientôt,<br>L’équipe Boolts',
                'email.subscription.downgraded_to_free.text' => 'Votre compte est repassé sur le forfait gratuit.',
                'email.subscription.downgraded_to_free.data' => 'Vos données sont conservées. Les annonces au-delà des limites du forfait gratuit ont été suspendues automatiquement.',
                'email.subscription.payment_definitively_failed.text' => 'Après plusieurs tentatives, nous n’avons pas pu renouveler votre abonnement.',
                'email.subscription.payment_definitively_failed.stopped' => 'Votre abonnement payant a donc été arrêté et votre compte est repassé sur le forfait gratuit.',
                'email.subscription.payment_definitively_failed.retry' => 'Vous pouvez à tout moment mettre à jour votre moyen de paiement et souscrire à nouveau à un forfait.',
                'email.subscription.payment_failed_first_attempt.text' => 'Nous n’avons pas pu effectuer le prélèvement correspondant au renouvellement de votre abonnement.',
                'email.subscription.payment_failed_first_attempt.grace' => 'Votre abonnement reste temporairement actif pendant la période de régularisation. Nous allons effectuer de nouvelles tentatives de paiement.',
                'email.subscription.update_payment_method' => 'Mettre à jour mon moyen de paiement',
                'email.subscription.payment_recovered.text' => 'Votre paiement a bien été régularisé.',
                'email.subscription.payment_recovered.active' => 'Votre abonnement est de nouveau actif.',
                'email.subscription.payment_recovered.active_until' => 'Votre abonnement est de nouveau actif jusqu’au %date%.',
                'email.subscription.payment_retry_failed.text' => 'Une nouvelle tentative de renouvellement de votre abonnement a échoué.',
                'email.subscription.payment_retry_failed.attempt' => 'Tentative %attempt% / %max%. Votre forfait reste temporairement actif pendant la période de régularisation.',
                'email.subscription.ended.text' => 'Votre abonnement payant est terminé.',
                'email.subscription.ended.free' => 'Votre compte est désormais sur le forfait gratuit. Vos données sont conservées, seules les limites du forfait gratuit s’appliquent.',
                'email.subscription.period.annual' => 'annuelle',
                'email.subscription.period.monthly' => 'mensuelle',
                'email.subscription.period.annual_cap' => 'Annuelle',
                'email.subscription.period.monthly_cap' => 'Mensuelle',
                'email.subscription.per_year' => '/ an',
                'email.subscription.per_month' => '/ mois',
                'email.subscription.purchased.intro' => 'Nous vous confirmons l’achat de votre abonnement %plan% en formule %period%.',
                'email.subscription.purchased.summary' => 'Récapitulatif de votre achat :',
                'email.subscription.label.plan' => 'Forfait',
                'email.subscription.label.period' => 'Périodicité',
                'email.subscription.label.amount_paid' => 'Montant réglé',
                'email.subscription.label.payment_reference' => 'Référence de paiement',
                'email.subscription.label.current_period' => 'Période en cours',
                'email.subscription.label.next_renewal' => 'Prochain renouvellement',
                'email.subscription.label.property_limit' => 'Annonces incluses',
                'email.subscription.label.included_boosts' => 'Crédits Boost inclus par période',
                'email.subscription.label.previous_plan' => 'Ancien forfait',
                'email.subscription.label.new_plan' => 'Nouveau forfait',
                'email.subscription.label.new_amount' => 'Nouveau montant',
                'email.subscription.label.effective' => 'Prise d’effet',
                'email.subscription.label.current_plan' => 'Forfait actuel',
                'email.subscription.label.property_limit_effective' => 'Annonces incluses (à compter de la prise d’effet)',
                'email.subscription.label.included_boosts_effective' => 'Crédits Boost inclus par période (à compter de la prise d’effet)',
                'email.subscription.label.cancelled_plan' => 'Forfait résilié',
                'email.subscription.label.requested_at' => 'Demande enregistrée le',
                'email.subscription.label.ends_at' => 'Fin de l’abonnement',
                'email.subscription.period_range' => 'du %start% au %end%',
                'email.subscription.period_from' => 'du %start%',
                'email.subscription.purchased.renewal_monthly' => 'Votre abonnement est actif immédiatement. Il se renouvellera automatiquement chaque mois.',
                'email.subscription.purchased.renewal_yearly' => 'Votre abonnement est actif immédiatement. Il se renouvellera automatiquement chaque année.',
                'email.subscription.purchased.renewal_monthly_from' => 'Votre abonnement est actif immédiatement. Il se renouvellera automatiquement chaque mois, à compter du %date%.',
                'email.subscription.purchased.renewal_yearly_from' => 'Votre abonnement est actif immédiatement. Il se renouvellera automatiquement chaque année, à compter du %date%.',
                'email.subscription.manage_intro' => 'Vous pouvez gérer votre abonnement et votre moyen de paiement depuis votre espace :',
                'email.subscription.manage_link' => 'Gérer mon abonnement',
                'email.subscription.upgraded.intro' => 'Nous vous confirmons le changement de votre abonnement vers %plan% en formule %period%.',
                'email.subscription.upgraded.intro_from' => 'Nous vous confirmons le changement de votre abonnement de %previous% vers %plan% en formule %period%.',
                'email.subscription.upgraded.active' => 'Votre nouveau forfait est actif immédiatement.',
                'email.subscription.immediate' => 'Immédiate',
                'email.subscription.upgraded.proration' => 'Le prorata éventuel entre votre ancien et votre nouveau forfait est géré automatiquement et apparaîtra sur votre prochaine facture.',
                'email.subscription.downgrade.intro' => 'Nous avons bien enregistré votre demande de passage vers %plan% en formule %period%.',
                'email.subscription.downgrade.intro_from' => 'Nous avons bien enregistré votre demande de passage du forfait %previous% vers %plan% en formule %period%.',
                'email.subscription.downgrade.not_immediate' => 'S’agissant d’un forfait d’un montant inférieur, le changement <strong>n’est pas immédiat</strong> : aucun paiement n’est effectué aujourd’hui. Il prendra effet à la fin de votre période en cours, au renouvellement de votre abonnement. D’ici là, vous conservez votre forfait actuel et l’ensemble de ses avantages.',
                'email.subscription.downgrade.not_immediate_on' => 'S’agissant d’un forfait d’un montant inférieur, le changement <strong>n’est pas immédiat</strong> : aucun paiement n’est effectué aujourd’hui. Il prendra effet le %date%, au renouvellement de votre abonnement. D’ici là, vous conservez votre forfait actuel et l’ensemble de ses avantages.',
                'email.subscription.end_of_period' => 'Fin de la période en cours',
                'email.subscription.downgrade.cancel_intro' => 'Vous pouvez annuler ce changement tant qu’il n’a pas pris effet, depuis votre espace :',
                'email.subscription.cancel.intro' => 'Nous avons bien enregistré votre demande de résiliation.',
                'email.subscription.cancel.intro_plan' => 'Nous avons bien enregistré votre demande de résiliation du forfait %plan%.',
                'email.subscription.cancel.not_immediate' => 'Votre résiliation <strong>n’est pas immédiate</strong> : votre abonnement prendra fin à l’issue de la période déjà réglée, au terme de la période que vous avez déjà payée. D’ici là, vous conservez votre forfait actuel et l’ensemble de ses avantages. Aucun nouveau renouvellement ne sera facturé après cette date.',
                'email.subscription.cancel.not_immediate_on' => 'Votre résiliation <strong>n’est pas immédiate</strong> : votre abonnement prendra fin le %date%, au terme de la période que vous avez déjà payée. D’ici là, vous conservez votre forfait actuel et l’ensemble de ses avantages. Aucun nouveau renouvellement ne sera facturé après cette date.',
                'email.subscription.cancel.undo_intro' => 'Vous pouvez annuler cette résiliation tant qu’elle n’a pas pris effet, depuis votre espace :',
                'email.subscription.paid' => 'payant',
                'email.subscription.subject.subscription_purchased' => 'Récapitulatif de votre achat d’abonnement',
                'email.subscription.subject.subscription_upgraded' => 'Votre changement de forfait est confirmé',
                'email.subscription.subject.subscription_downgrade_scheduled' => 'Votre changement de forfait est programmé',
                'email.subscription.subject.payment_failed_first_attempt' => 'Échec du renouvellement de votre abonnement',
                'email.subscription.subject.payment_retry_failed' => 'Nouvelle tentative de renouvellement échouée',
                'email.subscription.subject.payment_recovered' => 'Votre abonnement est de nouveau actif',
                'email.subscription.subject.payment_definitively_failed' => 'Votre abonnement n’a pas pu être renouvelé',
                'email.subscription.subject.subscription_cancel_requested' => 'Votre résiliation est programmée',
                'email.subscription.subject.subscription_ended' => 'Votre abonnement payant est terminé',
                'email.subscription.subject.downgraded_to_free' => 'Votre compte est repassé sur le forfait gratuit',
            ],
            'en' => [
                'email.common.hello' => 'Hello,',
                'email.common.signature' => 'See you soon,<br>The Boolts team',
                'email.subscription.downgraded_to_free.text' => 'Your account has been switched back to the free plan.',
                'email.subscription.downgraded_to_free.data' => 'Your data has been kept. Listings beyond the free plan limits have been suspended automatically.',
                'email.subscription.payment_definitively_failed.text' => 'After several attempts, we were unable to renew your subscription.',
                'email.subscription.payment_definitively_failed.stopped' => 'Your paid subscription has therefore been stopped and your account has been switched back to the free plan.',
                'email.subscription.payment_definitively_failed.retry' => 'You can update your payment method and subscribe to a plan again at any time.',
                'email.subscription.payment_failed_first_attempt.text' => 'We were unable to collect the payment for the renewal of your subscription.',
                'email.subscription.payment_failed_first_attempt.grace' => 'Your subscription remains temporarily active during the grace period. We will make new payment attempts.',
                'email.subscription.update_payment_method' => 'Update my payment method',
                'email.subscription.payment_recovered.text' => 'Your payment has been settled.',
                'email.subscription.payment_recovered.active' => 'Your subscription is active again.',
                'email.subscription.payment_recovered.active_until' => 'Your subscription is active again until %date%.',
                'email.subscription.payment_retry_failed.text' => 'A new attempt to renew your subscription has failed.',
                'email.subscription.payment_retry_failed.attempt' => 'Attempt %attempt% / %max%. Your plan remains temporarily active during the grace period.',
                'email.subscription.ended.text' => 'Your paid subscription has ended.',
                'email.subscription.ended.free' => 'Your account is now on the free plan. Your data has been kept; only the free plan limits apply.',
                'email.subscription.period.annual' => 'annual',
                'email.subscription.period.monthly' => 'monthly',
                'email.subscription.period.annual_cap' => 'Annual',
                'email.subscription.period.monthly_cap' => 'Monthly',
                'email.subscription.per_year' => '/ year',
                'email.subscription.per_month' => '/ month',
                'email.subscription.purchased.intro' => 'We confirm the purchase of your %plan% subscription on the %period% plan.',
                'email.subscription.purchased.summary' => 'Summary of your purchase:',
                'email.subscription.label.plan' => 'Plan',
                'email.subscription.label.period' => 'Billing period',
                'email.subscription.label.amount_paid' => 'Amount paid',
                'email.subscription.label.payment_reference' => 'Payment reference',
                'email.subscription.label.current_period' => 'Current period',
                'email.subscription.label.next_renewal' => 'Next renewal',
                'email.subscription.label.property_limit' => 'Included listings',
                'email.subscription.label.included_boosts' => 'Boost credits included per period',
                'email.subscription.label.previous_plan' => 'Previous plan',
                'email.subscription.label.new_plan' => 'New plan',
                'email.subscription.label.new_amount' => 'New amount',
                'email.subscription.label.effective' => 'Effective date',
                'email.subscription.label.current_plan' => 'Current plan',
                'email.subscription.label.property_limit_effective' => 'Included listings (from the effective date)',
                'email.subscription.label.included_boosts_effective' => 'Boost credits included per period (from the effective date)',
                'email.subscription.label.cancelled_plan' => 'Cancelled plan',
                'email.subscription.label.requested_at' => 'Request registered on',
                'email.subscription.label.ends_at' => 'End of subscription',
                'email.subscription.period_range' => 'from %start% to %end%',
                'email.subscription.period_from' => 'from %start%',
                'email.subscription.purchased.renewal_monthly' => 'Your subscription is active immediately. It will renew automatically every month.',
                'email.subscription.purchased.renewal_yearly' => 'Your subscription is active immediately. It will renew automatically every year.',
                'email.subscription.purchased.renewal_monthly_from' => 'Your subscription is active immediately. It will renew automatically every month, starting on %date%.',
                'email.subscription.purchased.renewal_yearly_from' => 'Your subscription is active immediately. It will renew automatically every year, starting on %date%.',
                'email.subscription.manage_intro' => 'You can manage your subscription and payment method from your account:',
                'email.subscription.manage_link' => 'Manage my subscription',
                'email.subscription.upgraded.intro' => 'We confirm the change of your subscription to %plan% on the %period% plan.',
                'email.subscription.upgraded.intro_from' => 'We confirm the change of your subscription from %previous% to %plan% on the %period% plan.',
                'email.subscription.upgraded.active' => 'Your new plan is active immediately.',
                'email.subscription.immediate' => 'Immediate',
                'email.subscription.upgraded.proration' => 'Any proration between your previous and new plan is handled automatically and will appear on your next invoice.',
                'email.subscription.downgrade.intro' => 'We have registered your request to switch to %plan% on the %period% plan.',
                'email.subscription.downgrade.intro_from' => 'We have registered your request to switch from the %previous% plan to %plan% on the %period% plan.',
                'email.subscription.downgrade.not_immediate' => 'As this is a lower-priced plan, the change <strong>is not immediate</strong>: no payment is made today. It will take effect at the end of your current period, when your subscription renews. Until then, you keep your current plan and all its benefits.',
                'email.subscription.downgrade.not_immediate_on' => 'As this is a lower-priced plan, the change <strong>is not immediate</strong>: no payment is made today. It will take effect on %date%, when your subscription renews. Until then, you keep your current plan and all its benefits.',
                'email.subscription.end_of_period' => 'End of the current period',
                'email.subscription.downgrade.cancel_intro' => 'You can cancel this change from your account as long as it has not taken effect:',
                'email.subscription.cancel.intro' => 'We have registered your cancellation request.',
                'email.subscription.cancel.intro_plan' => 'We have registered your cancellation request for the %plan% plan.',
                'email.subscription.cancel.not_immediate' => 'Your cancellation <strong>is not immediate</strong>: your subscription will end at the end of the period you have already paid for. Until then, you keep your current plan and all its benefits. No further renewal will be charged after that date.',
                'email.subscription.cancel.not_immediate_on' => 'Your cancellation <strong>is not immediate</strong>: your subscription will end on %date%, at the end of the period you have already paid for. Until then, you keep your current plan and all its benefits. No further renewal will be charged after that date.',
                'email.subscription.cancel.undo_intro' => 'You can undo this cancellation from your account as long as it has not taken effect:',
                'email.subscription.paid' => 'paid',
                'email.subscription.subject.subscription_purchased' => 'Summary of your subscription purchase',
                'email.subscription.subject.subscription_upgraded' => 'Your plan change is confirmed',
                'email.subscription.subject.subscription_downgrade_scheduled' => 'Your plan change is scheduled',
                'email.subscription.subject.payment_failed_first_attempt' => 'Your subscription renewal failed',
                'email.subscription.subject.payment_retry_failed' => 'New renewal attempt failed',
                'email.subscription.subject.payment_recovered' => 'Your subscription is active again',
                'email.subscription.subject.payment_definitively_failed' => 'Your subscription could not be renewed',
                'email.subscription.subject.subscription_cancel_requested' => 'Your cancellation is scheduled',
                'email.subscription.subject.subscription_ended' => 'Your paid subscription has ended',
                'email.subscription.subject.downgraded_to_free' => 'Your account has been switched back to the free plan',
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
                $translation->setPage('email_subscription');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
