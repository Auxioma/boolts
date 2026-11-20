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

namespace App\DataFixtures\Public;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class JsTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'js.upload.action' => 'Téléverser',
                'js.upload.sent' => 'Envoyé',
                'js.upload.in_progress' => 'Téléversement en cours…',
                'js.upload.server_error' => 'Le serveur n’a pas pu traiter ce document. Veuillez réessayer.',
                'js.upload.failed' => 'Le téléversement a échoué.',
                'js.upload.error' => 'Erreur de téléversement',
                'js.hours.all_closed' => 'Tous les jours sont fermés par défaut',
                'js.profile.languages_empty' => 'Définissez les langues parlées par l’agence',
                'js.geo.unsupported' => 'La géolocalisation n’est pas disponible sur ce navigateur.',
                'js.geo.denied' => 'Vous avez refusé l’accès à votre position.',
                'js.geo.unavailable' => 'Votre position est actuellement indisponible.',
                'js.geo.timeout' => 'La détection de votre position a expiré.',
                'js.geo.error' => 'Impossible de détecter votre position.',
                'js.geo.no_city' => 'Impossible de convertir votre position en ville.',
                'js.ai.generate' => 'Générer avec IA',
                'js.ai.error' => 'Impossible de générer la description.',
                'js.bulk.selected_one' => '%count% sélectionnée',
                'js.bulk.selected_other' => '%count% sélectionnées',
                'js.suggestions.reference' => 'Référence',
                'js.suggestions.title' => 'Titre',
                'js.suggestions.city' => 'Ville',
                'js.hours.open' => 'Ouvert',
                'js.hours.closed' => 'Fermé',
                'js.error.non_json' => 'Le serveur a retourné une réponse non JSON (%status%).',
                'js.payment.add_or_select_card' => 'Veuillez ajouter ou sélectionner une carte bancaire.',
                'js.payment.failed' => 'Le paiement n’a pas pu être effectué.',
                'js.payment.boost_bought' => 'Votre pack boost a été acheté.',
                'js.payment.subscribe' => 'S’abonner',
                'js.payment.select_card' => 'Veuillez sélectionner une carte bancaire.',
                'js.payment_method.stripe_key' => 'La clé publique Stripe est absente ou invalide.',
                'js.payment_method.stripe_not_loaded' => 'Stripe.js n’est pas chargé dans la page.',
                'js.payment_method.stripe_timeout' => 'Le chargement de Stripe.js a expiré.',
                'js.payment_method.stripe_missing_data' => 'Le serveur n’a pas retourné les informations Stripe attendues.',
                'js.payment_method.stripe_init' => 'Impossible d’initialiser Stripe.',
                'js.payment_method.stripe_form' => 'Le formulaire Stripe ne peut pas être chargé.',
                'js.payment_method.stripe_not_ready' => 'Stripe n’est pas encore prêt.',
                'js.payment_method.select_country' => 'Veuillez sélectionner le pays.',
                'js.payment_method.no_setup_intent' => 'Stripe n’a retourné aucun SetupIntent.',
                'js.payment_method.card_save' => 'Impossible d’enregistrer la carte.',
                'js.payment_method.method_save' => 'Le moyen de paiement n’a pas pu être enregistré.',
                'js.payment_method.method_delete' => 'Le moyen de paiement n’a pas pu être supprimé.',
                'js.payment_method.stripe_init_status' => 'Impossible d’initialiser Stripe (%status%).',
                'js.payment_method.not_validated' => 'La carte n’est pas validée. Statut : %status%.',
            ],
            'en' => [
                'js.upload.action' => 'Upload',
                'js.upload.sent' => 'Sent',
                'js.upload.in_progress' => 'Uploading…',
                'js.upload.server_error' => 'The server could not process this document. Please try again.',
                'js.upload.failed' => 'The upload failed.',
                'js.upload.error' => 'Upload error',
                'js.hours.all_closed' => 'All days are closed by default',
                'js.profile.languages_empty' => 'Set the languages spoken by the agency',
                'js.geo.unsupported' => 'Geolocation is not available in this browser.',
                'js.geo.denied' => 'You denied access to your location.',
                'js.geo.unavailable' => 'Your location is currently unavailable.',
                'js.geo.timeout' => 'Detecting your location timed out.',
                'js.geo.error' => 'Unable to detect your location.',
                'js.geo.no_city' => 'Unable to convert your location into a city.',
                'js.ai.generate' => 'Generate with AI',
                'js.ai.error' => 'Unable to generate the description.',
                'js.bulk.selected_one' => '%count% selected',
                'js.bulk.selected_other' => '%count% selected',
                'js.suggestions.reference' => 'Reference',
                'js.suggestions.title' => 'Title',
                'js.suggestions.city' => 'City',
                'js.hours.open' => 'Open',
                'js.hours.closed' => 'Closed',
                'js.error.non_json' => 'The server returned a non-JSON response (%status%).',
                'js.payment.add_or_select_card' => 'Please add or select a bank card.',
                'js.payment.failed' => 'The payment could not be completed.',
                'js.payment.boost_bought' => 'Your boost pack has been purchased.',
                'js.payment.subscribe' => 'Subscribe',
                'js.payment.select_card' => 'Please select a bank card.',
                'js.payment_method.stripe_key' => 'The Stripe public key is missing or invalid.',
                'js.payment_method.stripe_not_loaded' => 'Stripe.js is not loaded on the page.',
                'js.payment_method.stripe_timeout' => 'Loading Stripe.js timed out.',
                'js.payment_method.stripe_missing_data' => 'The server did not return the expected Stripe information.',
                'js.payment_method.stripe_init' => 'Unable to initialize Stripe.',
                'js.payment_method.stripe_form' => 'The Stripe form cannot be loaded.',
                'js.payment_method.stripe_not_ready' => 'Stripe is not ready yet.',
                'js.payment_method.select_country' => 'Please select the country.',
                'js.payment_method.no_setup_intent' => 'Stripe returned no SetupIntent.',
                'js.payment_method.card_save' => 'Unable to save the card.',
                'js.payment_method.method_save' => 'The payment method could not be saved.',
                'js.payment_method.method_delete' => 'The payment method could not be deleted.',
                'js.payment_method.stripe_init_status' => 'Unable to initialize Stripe (%status%).',
                'js.payment_method.not_validated' => 'The card is not validated. Status: %status%.',
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
                $translation->setPage('js');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
