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

final class AgenceImmobiliereOptionsTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency_options.page_title' => 'Options et abonnements',
                'agency_options.title' => 'Options et abonnements',
                'agency_options.choose_plan' => 'Choisissez votre abonnement',
                'agency_options.plan.current' => 'Abonnement actuel',
                'agency_options.plan.free' => 'Gratuit',
                'agency_options.plan.per_year' => '/an',
                'agency_options.plan.per_month' => '/mois',
                'agency_options.plan.subscribe' => 'Souscrire',
                'agency_options.plan.properties_unlimited' => 'Biens illimités',
                'agency_options.plan.properties' => '%count% biens',
                'agency_options.plan.included_boosts.one' => '%count% boost inclus',
                'agency_options.plan.included_boosts.other' => '%count% boosts inclus',
                'agency_options.plan.boost_duration' => 'Durée d’un boost : %days% jours',
                'agency_options.subscription.current' => 'Abonnement actuel',
                'agency_options.subscription.status.active' => 'Actif',
                'agency_options.subscription.status.canceled' => 'Résilié',
                'agency_options.subscription.status.pending' => 'En attente',
                'agency_options.subscription.plan' => 'Forfait',
                'agency_options.subscription.not_specified' => 'Non renseigné',
                'agency_options.subscription.billing_period' => 'Périodicité',
                'agency_options.subscription.billing_period.annual' => 'Annuelle',
                'agency_options.subscription.billing_period.monthly' => 'Mensuelle',
                'agency_options.subscription.next_renewal' => 'Prochain renouvellement',
                'agency_options.subscription.price' => 'Prix',
                'agency_options.subscription.update_payment_method' => 'Mettre à jour mon moyen de paiement',
                'agency_options.subscription.cancel' => 'Résilier mon abonnement',
                'agency_options.subscription.cancellation_scheduled' => 'Résiliation programmée',
                'agency_options.subscription.cancellation_info.before_date' => 'Votre abonnement est résilié. Il restera actif jusqu\'au',
                'agency_options.subscription.cancellation_info.after_date' => 'puis votre compte repassera automatiquement sur le forfait gratuit.',
                'agency_options.subscription.reactivate' => 'Annuler la résiliation',
                'agency_options.subscription.pending_change.before_plan' => 'Changement de forfait programmé pour',
                'agency_options.subscription.pending_change.on_date' => 'le',
                'agency_options.subscription.pending_change.end_of_period' => 'à la fin de votre période en cours',
                'agency_options.subscription.pending_change.before_amount' => 'Le paiement de',
                'agency_options.subscription.pending_change.after_amount' => 'sera effectué ce jour-là. D\'ici là, votre forfait actuel reste inchangé.',
                'agency_options.subscription.cancel_plan_change' => 'Annuler le changement programmé',
                'agency_options.billing_switch.aria_label' => 'Période de facturation',
                'agency_options.billing_switch.annual' => 'Annuel',
                'agency_options.billing_switch.monthly' => 'Mensuel',
                'agency_options.plans.empty.annual' => 'Aucun forfait annuel n’est disponible pour le moment.',
                'agency_options.plans.empty.monthly' => 'Aucun forfait mensuel n’est disponible pour le moment.',
                'agency_options.boosts.title' => 'Boostez vos annonces',
                'agency_options.boosts.description.one' => '%count% boost s’applique sur %count% annonce et dure %days% jours.',
                'agency_options.boosts.description.other' => '%count% boosts s’applique sur %count% annonces et dure %days% jours.',
                'agency_options.boosts.buy' => 'Acheter',
                'agency_options.boosts.empty' => 'Aucun pack boost n’est disponible pour le moment.',
                'agency_options.cancel_modal.title' => 'Résilier mon abonnement',
                'agency_options.cancel_modal.close' => 'Fermer',
                'agency_options.cancel_modal.question' => 'Êtes-vous certain de vouloir résilier votre abonnement ?',
                'agency_options.cancel_modal.description' => 'Votre abonnement restera actif jusqu\'à la fin de votre période de facturation actuelle.',
                'agency_options.cancel_modal.cancel' => 'Annuler',
                'agency_options.cancel_modal.confirm' => 'Confirmer la résiliation',
                'agency_options.flash.invalid_csrf' => 'Jeton de sécurité invalide.',
                'agency_options.flash.cancel_success' => 'Votre résiliation est programmée à la fin de la période déjà payée.',
                'agency_options.flash.cancel_error' => 'Impossible de programmer la résiliation de votre abonnement.',
                'agency_options.flash.reactivate_success' => 'Votre abonnement restera actif après la période en cours.',
                'agency_options.flash.reactivate_error' => 'Impossible de réactiver votre abonnement.',
                'agency_options.flash.no_plan_change' => 'Aucun changement de forfait n’est programmé.',
                'agency_options.flash.cancel_plan_change_success' => 'Le changement de forfait programmé a été annulé.',
                'agency_options.flash.cancel_plan_change_error' => 'Impossible d’annuler le changement de forfait programmé.',
                'agency_options.flash.no_billing_profile' => 'Aucun profil de facturation Stripe actif n’est disponible.',
                'agency_options.flash.customer_portal_error' => 'Impossible d’ouvrir le portail de paiement pour le moment.',
            ],
            'en' => [
                'agency_options.page_title' => 'Options and subscriptions',
                'agency_options.title' => 'Options and subscriptions',
                'agency_options.choose_plan' => 'Choose your subscription',
                'agency_options.plan.current' => 'Current subscription',
                'agency_options.plan.free' => 'Free',
                'agency_options.plan.per_year' => '/year',
                'agency_options.plan.per_month' => '/month',
                'agency_options.plan.subscribe' => 'Subscribe',
                'agency_options.plan.properties_unlimited' => 'Unlimited properties',
                'agency_options.plan.properties' => '%count% properties',
                'agency_options.plan.included_boosts.one' => '%count% boost included',
                'agency_options.plan.included_boosts.other' => '%count% boosts included',
                'agency_options.plan.boost_duration' => 'Boost duration: %days% days',
                'agency_options.subscription.current' => 'Current subscription',
                'agency_options.subscription.status.active' => 'Active',
                'agency_options.subscription.status.canceled' => 'Cancelled',
                'agency_options.subscription.status.pending' => 'Pending',
                'agency_options.subscription.plan' => 'Plan',
                'agency_options.subscription.not_specified' => 'Not specified',
                'agency_options.subscription.billing_period' => 'Billing period',
                'agency_options.subscription.billing_period.annual' => 'Annual',
                'agency_options.subscription.billing_period.monthly' => 'Monthly',
                'agency_options.subscription.next_renewal' => 'Next renewal',
                'agency_options.subscription.price' => 'Price',
                'agency_options.subscription.update_payment_method' => 'Update my payment method',
                'agency_options.subscription.cancel' => 'Cancel my subscription',
                'agency_options.subscription.cancellation_scheduled' => 'Cancellation scheduled',
                'agency_options.subscription.cancellation_info.before_date' => 'Your subscription has been cancelled. It will remain active until',
                'agency_options.subscription.cancellation_info.after_date' => 'then your account will automatically switch back to the free plan.',
                'agency_options.subscription.reactivate' => 'Undo cancellation',
                'agency_options.subscription.pending_change.before_plan' => 'Plan change scheduled to',
                'agency_options.subscription.pending_change.on_date' => 'on',
                'agency_options.subscription.pending_change.end_of_period' => 'at the end of your current period',
                'agency_options.subscription.pending_change.before_amount' => 'The payment of',
                'agency_options.subscription.pending_change.after_amount' => 'will be made on that day. Until then, your current plan remains unchanged.',
                'agency_options.subscription.cancel_plan_change' => 'Cancel the scheduled change',
                'agency_options.billing_switch.aria_label' => 'Billing period',
                'agency_options.billing_switch.annual' => 'Annual',
                'agency_options.billing_switch.monthly' => 'Monthly',
                'agency_options.plans.empty.annual' => 'No annual plan is available at the moment.',
                'agency_options.plans.empty.monthly' => 'No monthly plan is available at the moment.',
                'agency_options.boosts.title' => 'Boost your listings',
                'agency_options.boosts.description.one' => '%count% boost applies to %count% listing and lasts %days% days.',
                'agency_options.boosts.description.other' => '%count% boosts apply to %count% listings and last %days% days.',
                'agency_options.boosts.buy' => 'Buy',
                'agency_options.boosts.empty' => 'No boost pack is available at the moment.',
                'agency_options.cancel_modal.title' => 'Cancel my subscription',
                'agency_options.cancel_modal.close' => 'Close',
                'agency_options.cancel_modal.question' => 'Are you sure you want to cancel your subscription?',
                'agency_options.cancel_modal.description' => 'Your subscription will remain active until the end of your current billing period.',
                'agency_options.cancel_modal.cancel' => 'Cancel',
                'agency_options.cancel_modal.confirm' => 'Confirm cancellation',
                'agency_options.flash.invalid_csrf' => 'Invalid security token.',
                'agency_options.flash.cancel_success' => 'Your cancellation is scheduled for the end of the period already paid.',
                'agency_options.flash.cancel_error' => 'Unable to schedule the cancellation of your subscription.',
                'agency_options.flash.reactivate_success' => 'Your subscription will remain active after the current period.',
                'agency_options.flash.reactivate_error' => 'Unable to reactivate your subscription.',
                'agency_options.flash.no_plan_change' => 'No plan change is scheduled.',
                'agency_options.flash.cancel_plan_change_success' => 'The scheduled plan change has been cancelled.',
                'agency_options.flash.cancel_plan_change_error' => 'Unable to cancel the scheduled plan change.',
                'agency_options.flash.no_billing_profile' => 'No active Stripe billing profile is available.',
                'agency_options.flash.customer_portal_error' => 'Unable to open the payment portal at the moment.',
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
                $translation->setPage('agency_options');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
