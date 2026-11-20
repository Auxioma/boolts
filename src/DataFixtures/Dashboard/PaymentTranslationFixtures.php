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

final class PaymentTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'checkout.title' => 'Récapitulatif de votre achat',
                'checkout.article' => 'Article',
                'checkout.payment_method' => 'Moyen de paiement',
                'checkout.card' => 'Carte bancaire',
                'checkout.expiration' => 'Date d’expiration : %date%',
                'checkout.edit' => 'Modifier',
                'checkout.add_payment_method' => 'Ajouter un mode de paiement',
                'checkout.use_payment_method' => 'Utiliser cette méthode de paiement',
                'checkout.total' => 'Montant total :',
                'checkout.period.year' => 'an',
                'checkout.period.month' => 'mois',
                'checkout.subscription_item' => '1 x Abonnement %plan%',
                'checkout.downgrade.title' => 'Changement de forfait',
                'checkout.downgrade.current' => 'Forfait actuel',
                'checkout.downgrade.new' => 'Nouveau forfait',
                'checkout.downgrade.what_happens' => 'Que va-t-il se passer ?',
                'checkout.downgrade.on_date' => 'Le %date%,',
                'checkout.downgrade.end_of_period' => 'À la fin de votre période en cours,',
                'checkout.downgrade.new_plan' => 'votre nouveau forfait',
                'checkout.downgrade.billed' => 'prendra effet et vous serez facturé de',
                'checkout.downgrade.until' => 'Jusqu’à cette date, vous continuerez de profiter des avantages de votre forfait',
                'checkout.downgrade.legal' => 'En confirmant ce changement de forfait, vous acceptez les conditions générales de vente de Boolts. Veuillez consulter notre notice de protection de vos informations personnelles, notre notice cookies et notre notice sur les annonces publicitaires basées sur vos centres d’intérêt.',
                'checkout.amount' => 'Montant :',
                'checkout.downgrade.charged' => 'Le montant sera prélevé',
                'checkout.downgrade.charged_on' => 'le %date%.',
                'checkout.downgrade.charged_end' => 'à la fin de votre période en cours.',
                'checkout.change_plan' => 'Changer de forfait',
                'checkout.subscribe' => 'S’abonner',
                'checkout.boost_item' => '1 x Pack boost %name%',
                'checkout.buy_boost' => 'Acheter le pack boost',
                'payment_modal.close' => 'Fermer',
                'payment_modal.holder_placeholder' => 'Saisissez le nom du titulaire',
                'payment_modal.title' => 'Indiquez les informations de votre carte',
                'payment_modal.holder' => 'Nom du titulaire',
                'payment_modal.number' => 'Numéro de la carte',
                'payment_modal.expiry' => 'Date d’expiration',
                'payment_modal.cvc' => 'Code de sécurité',
                'payment_modal.country' => 'Pays',
                'payment_modal.default' => 'Définir comme moyen de paiement par défaut',
                'payment_modal.cancel' => 'Annuler',
                'payment_modal.submit' => 'Ajouter',
                'payment_methods.title' => 'Moyens de paiement',
                'payment_methods.saved' => 'Cartes enregistrées',
                'payment_methods.empty' => 'Aucun moyen de paiement n’est enregistré.',
                'payment_methods.default_suffix' => '— Par défaut',
                'payment_methods.expiration' => 'Date d’expiration :',
                'payment_methods.actions' => 'Afficher les actions de la carte',
                'payment_methods.set_default' => 'Définir par défaut',
                'payment_methods.delete' => 'Supprimer',
                'payment_modal.number_aria' => 'Numéro de la carte bancaire',
                'payment_modal.expiry_aria' => 'Date d’expiration de la carte',
                'payment_modal.cvc_aria' => 'Code de sécurité de la carte',
            ],
            'en' => [
                'checkout.title' => 'Order summary',
                'checkout.article' => 'Item',
                'checkout.payment_method' => 'Payment method',
                'checkout.card' => 'Bank card',
                'checkout.expiration' => 'Expiration date: %date%',
                'checkout.edit' => 'Edit',
                'checkout.add_payment_method' => 'Add a payment method',
                'checkout.use_payment_method' => 'Use this payment method',
                'checkout.total' => 'Total amount:',
                'checkout.period.year' => 'year',
                'checkout.period.month' => 'month',
                'checkout.subscription_item' => '1 x %plan% subscription',
                'checkout.downgrade.title' => 'Plan change',
                'checkout.downgrade.current' => 'Current plan',
                'checkout.downgrade.new' => 'New plan',
                'checkout.downgrade.what_happens' => 'What will happen?',
                'checkout.downgrade.on_date' => 'On %date%,',
                'checkout.downgrade.end_of_period' => 'At the end of your current period,',
                'checkout.downgrade.new_plan' => 'your new plan',
                'checkout.downgrade.billed' => 'will take effect and you will be charged',
                'checkout.downgrade.until' => 'Until then, you will continue to enjoy the benefits of your plan',
                'checkout.downgrade.legal' => 'By confirming this plan change, you accept the Boolts terms and conditions of sale. Please read our personal data protection notice, our cookie notice and our notice on interest-based advertising.',
                'checkout.amount' => 'Amount:',
                'checkout.downgrade.charged' => 'The amount will be charged',
                'checkout.downgrade.charged_on' => 'on %date%.',
                'checkout.downgrade.charged_end' => 'at the end of your current period.',
                'checkout.change_plan' => 'Change plan',
                'checkout.subscribe' => 'Subscribe',
                'checkout.boost_item' => '1 x Boost pack %name%',
                'checkout.buy_boost' => 'Buy the boost pack',
                'payment_modal.close' => 'Close',
                'payment_modal.holder_placeholder' => 'Enter the cardholder name',
                'payment_modal.title' => 'Enter your card details',
                'payment_modal.holder' => 'Cardholder name',
                'payment_modal.number' => 'Card number',
                'payment_modal.expiry' => 'Expiration date',
                'payment_modal.cvc' => 'Security code',
                'payment_modal.country' => 'Country',
                'payment_modal.default' => 'Set as default payment method',
                'payment_modal.cancel' => 'Cancel',
                'payment_modal.submit' => 'Add',
                'payment_methods.title' => 'Payment methods',
                'payment_methods.saved' => 'Saved cards',
                'payment_methods.empty' => 'No payment method saved.',
                'payment_methods.default_suffix' => '— Default',
                'payment_methods.expiration' => 'Expiration date:',
                'payment_methods.actions' => 'Show card actions',
                'payment_methods.set_default' => 'Set as default',
                'payment_methods.delete' => 'Delete',
                'payment_modal.number_aria' => 'Bank card number',
                'payment_modal.expiry_aria' => 'Card expiration date',
                'payment_modal.cvc_aria' => 'Card security code',
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
                $translation->setPage('paiement');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
