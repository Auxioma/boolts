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

final class PropertyStatusTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'property.status.brouillon' => 'Brouillon',
                'property.status.pending' => 'En attente de validation',
                'property.status.a_corriger' => 'À corriger',
                'property.status.validate' => 'Validée',
                'property.status.refusee' => 'Refusée',
                'property.status.publiee' => 'Publiée',
                'property.status.depubliee' => 'Dépubliée',
                'property.status.suspendue' => 'Suspendue',
                'property.status.suspended_by_plan' => 'Suspendue par limitation de forfait',
                'property.status.expiree' => 'Expirée',
                'property.status.disponible' => 'Disponible',
                'property.status.sous_offre' => 'Sous offre',
                'property.status.offre_acceptee' => 'Offre acceptée',
                'property.status.compromis_signe' => 'Compromis signé',
                'property.status.vendue' => 'Vendue',
                'property.status.reservee' => 'Réservée',
                'property.status.dossier_en_cours' => 'Dossier en cours',
                'property.status.bail_signe' => 'Bail signé',
                'property.status.louee' => 'Louée',
                'property.status.archivee' => 'Archivée',
                'property.status.supprimee' => 'Supprimée',
            ],
            'en' => [
                'property.status.brouillon' => 'Draft',
                'property.status.pending' => 'Pending validation',
                'property.status.a_corriger' => 'To be corrected',
                'property.status.validate' => 'Validated',
                'property.status.refusee' => 'Rejected',
                'property.status.publiee' => 'Published',
                'property.status.depubliee' => 'Unpublished',
                'property.status.suspendue' => 'Suspended',
                'property.status.suspended_by_plan' => 'Suspended due to plan limit',
                'property.status.expiree' => 'Expired',
                'property.status.disponible' => 'Available',
                'property.status.sous_offre' => 'Under offer',
                'property.status.offre_acceptee' => 'Offer accepted',
                'property.status.compromis_signe' => 'Preliminary contract signed',
                'property.status.vendue' => 'Sold',
                'property.status.reservee' => 'Reserved',
                'property.status.dossier_en_cours' => 'Application in progress',
                'property.status.bail_signe' => 'Lease signed',
                'property.status.louee' => 'Rented',
                'property.status.archivee' => 'Archived',
                'property.status.supprimee' => 'Deleted',
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
                $translation->setPage('property_status');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
