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

final class DashboardMessagesTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'mes_biens.flash.paused' => 'Annonce mise en pause',
                'mes_biens.flash.paused_plural' => 'Annonces mises en pause',
                'mes_biens.flash.reactivated' => 'Annonce réactivée',
                'mes_biens.flash.reactivated_plural' => 'Annonces réactivées',
                'mes_biens.flash.deleted' => 'Annonce supprimée',
                'mes_biens.flash.deleted_plural' => 'Annonces supprimées',
                'mes_biens.flash.boosted' => 'Annonce boostée',
                'mes_biens.flash.republished_boosted' => 'L’annonce a été republiée et boostée jusqu’au %date%.',
                'mes_biens.flash.boosted_until' => 'L’annonce est boostée jusqu’au %date%.',
                'mes_biens.flash.select_one' => 'Sélectionnez au moins une annonce.',
                'mes_biens.flash.none_valid' => 'Aucune annonce valide sélectionnée.',
                'mes_biens.flash.unknown_action' => 'Action inconnue.',
                'billing.boost.invalid_card' => 'Carte bancaire invalide.',
                'billing.boost.price_zero' => 'Le prix du pack boost doit être supérieur à zéro.',
                'billing.boost.not_confirmed' => 'Le paiement Stripe n’a pas été confirmé.',
                'billing.boost.added' => 'Votre pack boost a été ajouté.',
                'billing.boost.error' => 'Impossible de payer le pack boost.',
                'agency_dashboard.documents.not_found' => 'Le document demandé est introuvable.',
                'agency_dashboard.documents.invalid_file' => 'Le fichier transmis est invalide.',
                'agency_dashboard.documents.select_file' => 'Veuillez sélectionner un fichier valide.',
                'agency_dashboard.documents.unsupported_format' => 'Le format du document n’est pas pris en charge',
                'agency_dashboard.documents.too_large' => 'Le document est trop volumineux',
                'agency_dashboard.documents.sent' => 'Votre document a été envoyé et sera vérifié.',
                'agency_dashboard.statistics.invalid_period' => 'Période de statistiques invalide.',
                'agency_dashboard.statistics.dates_required' => 'Les dates de début et de fin sont obligatoires.',
                'agency_dashboard.statistics.invalid_dates' => 'Les dates fournies sont invalides.',
                'agency_dashboard.statistics.custom_range' => 'La période personnalisée doit être comprise entre aujourd’hui et les 12 derniers mois.',
                'agency_dashboard.statistics.chart.profile_views' => 'Vues du profil',
                'agency_dashboard.statistics.chart.published' => 'Annonces publiées',
                'agency_dashboard.statistics.chart.views' => 'Vues des annonces',
                'api.error.not_logged_in' => 'Utilisateur non connecté.',
                'profile.api.saved' => 'Les modifications ont été effectuées avec succès !',
                'profile.api.error' => 'Un problème est survenu avec les modifications. Réessayez.',
                'profile.api.invalid_password' => 'Mot de passe invalide.',
                'profile.api.fill_both' => 'Veuillez remplir les deux champs.',
                'profile.api.password_length' => 'Le mot de passe doit contenir au moins 12 caractères.',
                'profile.api.invalid_hours' => 'Les horaires sont invalides.',
                'profile.api.hours_required' => 'Veuillez renseigner au moins un horaire.',
                'profile.api.hours_saved' => 'Les horaires ont été enregistrés avec succès !',
                'profile.api.invalid_languages' => 'Les langues sélectionnées sont invalides.',
                'profile.api.languages_saved' => 'Les langues parlées ont été enregistrées avec succès !',
                'profile.api.invalid_address' => 'Adresse invalide.',
                'profile.api.invalid_form' => 'Formulaire invalide.',
                'api.error.invalid_data' => 'Données invalides.',
                'favorites.agency_forbidden' => 'Une agence ne peut pas ajouter un bien en favoris.',
                'common.error.retry' => 'Un problème est survenu. Réessayez.',
            ],
            'en' => [
                'mes_biens.flash.paused' => 'Listing paused',
                'mes_biens.flash.paused_plural' => 'Listings paused',
                'mes_biens.flash.reactivated' => 'Listing reactivated',
                'mes_biens.flash.reactivated_plural' => 'Listings reactivated',
                'mes_biens.flash.deleted' => 'Listing deleted',
                'mes_biens.flash.deleted_plural' => 'Listings deleted',
                'mes_biens.flash.boosted' => 'Listing boosted',
                'mes_biens.flash.republished_boosted' => 'The listing has been republished and boosted until %date%.',
                'mes_biens.flash.boosted_until' => 'The listing is boosted until %date%.',
                'mes_biens.flash.select_one' => 'Select at least one listing.',
                'mes_biens.flash.none_valid' => 'No valid listing selected.',
                'mes_biens.flash.unknown_action' => 'Unknown action.',
                'billing.boost.invalid_card' => 'Invalid bank card.',
                'billing.boost.price_zero' => 'The boost pack price must be greater than zero.',
                'billing.boost.not_confirmed' => 'The Stripe payment was not confirmed.',
                'billing.boost.added' => 'Your boost pack has been added.',
                'billing.boost.error' => 'Unable to pay for the boost pack.',
                'agency_dashboard.documents.not_found' => 'The requested document could not be found.',
                'agency_dashboard.documents.invalid_file' => 'The submitted file is invalid.',
                'agency_dashboard.documents.select_file' => 'Please select a valid file.',
                'agency_dashboard.documents.unsupported_format' => 'The document format is not supported',
                'agency_dashboard.documents.too_large' => 'The document is too large',
                'agency_dashboard.documents.sent' => 'Your document has been sent and will be reviewed.',
                'agency_dashboard.statistics.invalid_period' => 'Invalid statistics period.',
                'agency_dashboard.statistics.dates_required' => 'Start and end dates are required.',
                'agency_dashboard.statistics.invalid_dates' => 'The dates provided are invalid.',
                'agency_dashboard.statistics.custom_range' => 'The custom period must be between today and the last 12 months.',
                'agency_dashboard.statistics.chart.profile_views' => 'Profile views',
                'agency_dashboard.statistics.chart.published' => 'Published listings',
                'agency_dashboard.statistics.chart.views' => 'Listing views',
                'api.error.not_logged_in' => 'User not logged in.',
                'profile.api.saved' => 'Your changes have been saved!',
                'profile.api.error' => 'There was a problem with your changes. Please try again.',
                'profile.api.invalid_password' => 'Invalid password.',
                'profile.api.fill_both' => 'Please fill in both fields.',
                'profile.api.password_length' => 'The password must contain at least 12 characters.',
                'profile.api.invalid_hours' => 'The opening hours are invalid.',
                'profile.api.hours_required' => 'Please enter at least one opening hour.',
                'profile.api.hours_saved' => 'Opening hours have been saved!',
                'profile.api.invalid_languages' => 'The selected languages are invalid.',
                'profile.api.languages_saved' => 'Spoken languages have been saved!',
                'profile.api.invalid_address' => 'Invalid address.',
                'profile.api.invalid_form' => 'Invalid form.',
                'api.error.invalid_data' => 'Invalid data.',
                'favorites.agency_forbidden' => 'An agency cannot add a property to favorites.',
                'common.error.retry' => 'Something went wrong. Please try again.',
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
                $translation->setPage('dashboard_messages');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
