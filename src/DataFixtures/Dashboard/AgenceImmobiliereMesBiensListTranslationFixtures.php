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

final class AgenceImmobiliereMesBiensListTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'agency_listings.page_title' => 'Mes biens immobiliers',
                'agency_listings.title' => 'Mes biens',
                'agency_listings.status.publiee' => 'Publiée',
                'agency_listings.status.depubliee' => 'Pause',
                'agency_listings.status.pending' => 'En attente',
                'agency_listings.status.refusee' => 'Refusée',
                'agency_listings.status.suspendue' => 'Suspendue',
                'agency_listings.status.boosted' => 'Boostée',
                'agency_listings.filters' => 'Filtres',
                'agency_listings.sort' => 'Tri :',
                'agency_listings.sort.aria_label' => 'Trier les annonces',
                'agency_listings.sort.updated_desc' => 'Récente → ancienne',
                'agency_listings.sort.updated_asc' => 'Ancienne → récente',
                'agency_listings.add_listing' => 'Déposer une annonce',
                'agency_listings.search.placeholder' => 'Rechercher un bien, une référence, une ville ou un pays',
                'agency_listings.search.submit' => 'Rechercher',
                'agency_listings.bulk.select_all' => 'Tout sélectionner',
                'agency_listings.bulk.pause' => 'Pause',
                'agency_listings.boost' => 'Booster',
                'agency_listings.reactivate' => 'Réactiver',
                'agency_listings.delete' => 'Supprimer',
                'agency_listings.draft.untitled' => 'Brouillon #%number%',
                'agency_listings.draft.updated_at' => 'Modifié le %date% à %time%',
                'agency_listings.draft.edit' => 'Modifier le brouillon',
                'agency_listings.draft.delete' => 'Supprimer le brouillon',
                'agency_listings.draft.delete_confirm' => 'Supprimer ce brouillon ?',
                'agency_listings.pending_selection_disabled' => 'Annonce en attente de validation : sélection impossible',
                'agency_listings.listing.view_detail' => 'Voir le détail de %title%',
                'agency_listings.listing.this_listing' => 'cette annonce',
                'agency_listings.listing.image_alt' => 'Annonce immobilière',
                'agency_listings.listing.untitled' => 'Annonce sans titre',
                'agency_listings.listing.no_location' => 'Localisation non renseignée',
                'agency_listings.listing.updated_at' => 'Modifiée le %date%',
                'agency_listings.listing.created_at' => 'Créée le %date%',
                'agency_listings.listing.edit' => 'Modifier l’annonce',
                'agency_listings.listing.more_actions' => 'Plus d’actions',
                'agency_listings.listing.pause' => 'Mettre en pause',
                'agency_listings.listing.delete_confirm' => 'Supprimer cette annonce ?',
                'agency_listings.boost_modal.title' => 'Vous allez dépenser 1 boost',
                'agency_listings.boost_modal.close' => 'Fermer',
                'agency_listings.boost_modal.remaining' => 'Boost restant :',
                'agency_listings.boost_modal.duration' => 'Durée de mise en avant :',
                'agency_listings.boost_modal.day' => 'jour',
                'agency_listings.boost_modal.days' => 'jours',
                'agency_listings.boost_modal.description' => 'Votre annonce sera boostée pendant %days% %dayLabel% à partir de l’activation.',
                'agency_listings.boost_modal.paused_notice' => 'Le boost continuera même si votre annonce est en pause.',
                'agency_listings.boost_modal.cancel' => 'Annuler',
                'agency_listings.boost_modal.submit' => 'Booster mon annonce',
                'agency_listings.boost_modal.empty' => 'Vous n’avez plus de boost disponible. Achetez un pack boost pour mettre cette annonce en avant.',
                'agency_listings.boost_modal.packs' => 'Voir les packs boost',
                'agency_listings.empty.title' => 'Vous n’avez pas encore de bien',
                'agency_listings.empty.description' => 'Déposez votre première annonce pour la retrouver ici.',
            ],
            'en' => [
                'agency_listings.page_title' => 'My properties',
                'agency_listings.title' => 'My properties',
                'agency_listings.status.publiee' => 'Published',
                'agency_listings.status.depubliee' => 'Paused',
                'agency_listings.status.pending' => 'Pending',
                'agency_listings.status.refusee' => 'Rejected',
                'agency_listings.status.suspendue' => 'Suspended',
                'agency_listings.status.boosted' => 'Boosted',
                'agency_listings.filters' => 'Filters',
                'agency_listings.sort' => 'Sort:',
                'agency_listings.sort.aria_label' => 'Sort listings',
                'agency_listings.sort.updated_desc' => 'Newest → oldest',
                'agency_listings.sort.updated_asc' => 'Oldest → newest',
                'agency_listings.add_listing' => 'Post a listing',
                'agency_listings.search.placeholder' => 'Search for a property, a reference, a city or a country',
                'agency_listings.search.submit' => 'Search',
                'agency_listings.bulk.select_all' => 'Select all',
                'agency_listings.bulk.pause' => 'Pause',
                'agency_listings.boost' => 'Boost',
                'agency_listings.reactivate' => 'Reactivate',
                'agency_listings.delete' => 'Delete',
                'agency_listings.draft.untitled' => 'Draft #%number%',
                'agency_listings.draft.updated_at' => 'Modified on %date% at %time%',
                'agency_listings.draft.edit' => 'Edit draft',
                'agency_listings.draft.delete' => 'Delete draft',
                'agency_listings.draft.delete_confirm' => 'Delete this draft?',
                'agency_listings.pending_selection_disabled' => 'Listing pending validation: selection unavailable',
                'agency_listings.listing.view_detail' => 'View details of %title%',
                'agency_listings.listing.this_listing' => 'this listing',
                'agency_listings.listing.image_alt' => 'Real estate listing',
                'agency_listings.listing.untitled' => 'Untitled listing',
                'agency_listings.listing.no_location' => 'Location not specified',
                'agency_listings.listing.updated_at' => 'Modified on %date%',
                'agency_listings.listing.created_at' => 'Created on %date%',
                'agency_listings.listing.edit' => 'Edit listing',
                'agency_listings.listing.more_actions' => 'More actions',
                'agency_listings.listing.pause' => 'Pause',
                'agency_listings.listing.delete_confirm' => 'Delete this listing?',
                'agency_listings.boost_modal.title' => 'You are about to spend 1 boost',
                'agency_listings.boost_modal.close' => 'Close',
                'agency_listings.boost_modal.remaining' => 'Boost remaining:',
                'agency_listings.boost_modal.duration' => 'Promotion duration:',
                'agency_listings.boost_modal.day' => 'day',
                'agency_listings.boost_modal.days' => 'days',
                'agency_listings.boost_modal.description' => 'Your listing will be boosted for %days% %dayLabel% from activation.',
                'agency_listings.boost_modal.paused_notice' => 'The boost will continue even if your listing is paused.',
                'agency_listings.boost_modal.cancel' => 'Cancel',
                'agency_listings.boost_modal.submit' => 'Boost my listing',
                'agency_listings.boost_modal.empty' => 'You have no boosts left. Buy a boost pack to promote this listing.',
                'agency_listings.boost_modal.packs' => 'View boost packs',
                'agency_listings.empty.title' => 'You do not have any properties yet',
                'agency_listings.empty.description' => 'Post your first listing to find it here.',
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
                $translation->setPage('agency_listings');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
