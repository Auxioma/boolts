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
            'es' => [
                'property.status.brouillon' => 'Borrador',
                'property.status.pending' => 'Pendiente de validación',
                'property.status.a_corriger' => 'Por corregir',
                'property.status.validate' => 'Validado',
                'property.status.refusee' => 'Rechazado',
                'property.status.publiee' => 'Publicado',
                'property.status.depubliee' => 'Despublicado',
                'property.status.suspendue' => 'Suspendido',
                'property.status.suspended_by_plan' => 'Suspendido por límite del plan',
                'property.status.expiree' => 'Caducado',
                'property.status.disponible' => 'Disponible',
                'property.status.sous_offre' => 'Con oferta',
                'property.status.offre_acceptee' => 'Oferta aceptada',
                'property.status.compromis_signe' => 'Contrato de arras firmado',
                'property.status.vendue' => 'Vendido',
                'property.status.reservee' => 'Reservado',
                'property.status.dossier_en_cours' => 'Solicitud en curso',
                'property.status.bail_signe' => 'Contrato de alquiler firmado',
                'property.status.louee' => 'Alquilado',
                'property.status.archivee' => 'Archivado',
                'property.status.supprimee' => 'Eliminado',
            ],
            'de' => [
                'property.status.brouillon' => 'Entwurf',
                'property.status.pending' => 'Wartet auf Freigabe',
                'property.status.a_corriger' => 'Zu korrigieren',
                'property.status.validate' => 'Freigegeben',
                'property.status.refusee' => 'Abgelehnt',
                'property.status.publiee' => 'Veröffentlicht',
                'property.status.depubliee' => 'Nicht mehr veröffentlicht',
                'property.status.suspendue' => 'Gesperrt',
                'property.status.suspended_by_plan' => 'Wegen Tariflimit gesperrt',
                'property.status.expiree' => 'Abgelaufen',
                'property.status.disponible' => 'Verfügbar',
                'property.status.sous_offre' => 'Angebot liegt vor',
                'property.status.offre_acceptee' => 'Angebot angenommen',
                'property.status.compromis_signe' => 'Vorvertrag unterzeichnet',
                'property.status.vendue' => 'Verkauft',
                'property.status.reservee' => 'Reserviert',
                'property.status.dossier_en_cours' => 'Bewerbung in Bearbeitung',
                'property.status.bail_signe' => 'Mietvertrag unterzeichnet',
                'property.status.louee' => 'Vermietet',
                'property.status.archivee' => 'Archiviert',
                'property.status.supprimee' => 'Gelöscht',
            ],
            'pt' => [
                'property.status.brouillon' => 'Rascunho',
                'property.status.pending' => 'A aguardar validação',
                'property.status.a_corriger' => 'A corrigir',
                'property.status.validate' => 'Validado',
                'property.status.refusee' => 'Recusado',
                'property.status.publiee' => 'Publicado',
                'property.status.depubliee' => 'Despublicado',
                'property.status.suspendue' => 'Suspenso',
                'property.status.suspended_by_plan' => 'Suspenso por limite do plano',
                'property.status.expiree' => 'Expirado',
                'property.status.disponible' => 'Disponível',
                'property.status.sous_offre' => 'Com proposta',
                'property.status.offre_acceptee' => 'Proposta aceite',
                'property.status.compromis_signe' => 'Contrato-promessa assinado',
                'property.status.vendue' => 'Vendido',
                'property.status.reservee' => 'Reservado',
                'property.status.dossier_en_cours' => 'Processo em curso',
                'property.status.bail_signe' => 'Contrato de arrendamento assinado',
                'property.status.louee' => 'Arrendado',
                'property.status.archivee' => 'Arquivado',
                'property.status.supprimee' => 'Eliminado',
            ],
            'it' => [
                'property.status.brouillon' => 'Bozza',
                'property.status.pending' => 'In attesa di convalida',
                'property.status.a_corriger' => 'Da correggere',
                'property.status.validate' => 'Convalidato',
                'property.status.refusee' => 'Rifiutato',
                'property.status.publiee' => 'Pubblicato',
                'property.status.depubliee' => 'Non più pubblicato',
                'property.status.suspendue' => 'Sospeso',
                'property.status.suspended_by_plan' => 'Sospeso per limite del piano',
                'property.status.expiree' => 'Scaduto',
                'property.status.disponible' => 'Disponibile',
                'property.status.sous_offre' => 'Con offerta',
                'property.status.offre_acceptee' => 'Offerta accettata',
                'property.status.compromis_signe' => 'Compromesso firmato',
                'property.status.vendue' => 'Venduto',
                'property.status.reservee' => 'Riservato',
                'property.status.dossier_en_cours' => 'Pratica in corso',
                'property.status.bail_signe' => 'Contratto di locazione firmato',
                'property.status.louee' => 'Affittato',
                'property.status.archivee' => 'Archiviato',
                'property.status.supprimee' => 'Eliminato',
            ],
            'nl' => [
                'property.status.brouillon' => 'Concept',
                'property.status.pending' => 'In afwachting van validatie',
                'property.status.a_corriger' => 'Te corrigeren',
                'property.status.validate' => 'Gevalideerd',
                'property.status.refusee' => 'Afgewezen',
                'property.status.publiee' => 'Gepubliceerd',
                'property.status.depubliee' => 'Niet meer gepubliceerd',
                'property.status.suspendue' => 'Opgeschort',
                'property.status.suspended_by_plan' => 'Opgeschort wegens abonnementslimiet',
                'property.status.expiree' => 'Verlopen',
                'property.status.disponible' => 'Beschikbaar',
                'property.status.sous_offre' => 'Onder bod',
                'property.status.offre_acceptee' => 'Bod geaccepteerd',
                'property.status.compromis_signe' => 'Voorlopig koopcontract getekend',
                'property.status.vendue' => 'Verkocht',
                'property.status.reservee' => 'Gereserveerd',
                'property.status.dossier_en_cours' => 'Dossier in behandeling',
                'property.status.bail_signe' => 'Huurcontract getekend',
                'property.status.louee' => 'Verhuurd',
                'property.status.archivee' => 'Gearchiveerd',
                'property.status.supprimee' => 'Verwijderd',
            ],
            'hu' => [
                'property.status.brouillon' => 'Piszkozat',
                'property.status.pending' => 'Jóváhagyásra vár',
                'property.status.a_corriger' => 'Javítandó',
                'property.status.validate' => 'Jóváhagyva',
                'property.status.refusee' => 'Elutasítva',
                'property.status.publiee' => 'Közzétéve',
                'property.status.depubliee' => 'Közzététel visszavonva',
                'property.status.suspendue' => 'Felfüggesztve',
                'property.status.suspended_by_plan' => 'Csomagkorlát miatt felfüggesztve',
                'property.status.expiree' => 'Lejárt',
                'property.status.disponible' => 'Elérhető',
                'property.status.sous_offre' => 'Ajánlat alatt',
                'property.status.offre_acceptee' => 'Ajánlat elfogadva',
                'property.status.compromis_signe' => 'Előszerződés aláírva',
                'property.status.vendue' => 'Eladva',
                'property.status.reservee' => 'Lefoglalva',
                'property.status.dossier_en_cours' => 'Kérelem folyamatban',
                'property.status.bail_signe' => 'Bérleti szerződés aláírva',
                'property.status.louee' => 'Kiadva',
                'property.status.archivee' => 'Archiválva',
                'property.status.supprimee' => 'Törölve',
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
