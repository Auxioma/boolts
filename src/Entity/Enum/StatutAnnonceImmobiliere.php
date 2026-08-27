<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity\Enum;

enum StatutAnnonceImmobiliere: string
{
    /*
     * Statuts de création / validation
     */
    case BROUILLON = 'brouillon';
    case PENDING = 'pending';
    case A_CORRIGER = 'a_corriger';
    case VALIDATE = 'validate';
    case REFUSEE = 'refusee';

    /*
     * Statuts de publication
     */
    case PUBLIEE = 'publiee';
    case DEPUBLIEE = 'depubliee';
    case SUSPENDUE = 'suspendue';
    case SUSPENDED_BY_PLAN = 'suspended_by_plan';
    case EXPIREE = 'expiree';

    /*
     * Statuts commerciaux - vente
     */
    case DISPONIBLE = 'disponible';
    case SOUS_OFFRE = 'sous_offre';
    case OFFRE_ACCEPTEE = 'offre_acceptee';
    case COMPROMIS_SIGNE = 'compromis_signe';
    case VENDUE = 'vendue';

    /*
     * Statuts commerciaux - location
     */
    case RESERVEE = 'reservee';
    case DOSSIER_EN_COURS = 'dossier_en_cours';
    case BAIL_SIGNE = 'bail_signe';
    case LOUEE = 'louee';

    /*
     * Statuts finaux
     */
    case ARCHIVEE = 'archivee';
    case SUPPRIMEE = 'supprimee';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::PENDING => 'En attente de validation',
            self::A_CORRIGER => 'À corriger',
            self::VALIDATE => 'Validée',
            self::REFUSEE => 'Refusée',

            self::PUBLIEE => 'Publiée',
            self::DEPUBLIEE => 'Dépubliée',
            self::SUSPENDUE => 'Suspendue',
            self::SUSPENDED_BY_PLAN => 'Suspendue par limitation de forfait',
            self::EXPIREE => 'Expirée',

            self::DISPONIBLE => 'Disponible',
            self::SOUS_OFFRE => 'Sous offre',
            self::OFFRE_ACCEPTEE => 'Offre acceptée',
            self::COMPROMIS_SIGNE => 'Compromis signé',
            self::VENDUE => 'Vendue',

            self::RESERVEE => 'Réservée',
            self::DOSSIER_EN_COURS => 'Dossier en cours',
            self::BAIL_SIGNE => 'Bail signé',
            self::LOUEE => 'Louée',

            self::ARCHIVEE => 'Archivée',
            self::SUPPRIMEE => 'Supprimée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::BROUILLON => 'badge bg-secondary',
            self::PENDING => 'badge bg-warning text-dark',
            self::A_CORRIGER => 'badge bg-warning text-dark',
            self::VALIDATE => 'badge bg-success',
            self::REFUSEE => 'badge bg-danger',

            self::PUBLIEE => 'badge bg-success',
            self::DEPUBLIEE => 'badge bg-secondary',
            self::SUSPENDUE => 'badge bg-danger',
            self::SUSPENDED_BY_PLAN => 'badge bg-danger',
            self::EXPIREE => 'badge bg-dark',

            self::DISPONIBLE => 'badge bg-primary',
            self::SOUS_OFFRE => 'badge bg-info text-dark',
            self::OFFRE_ACCEPTEE => 'badge bg-info text-dark',
            self::COMPROMIS_SIGNE => 'badge bg-warning text-dark',
            self::VENDUE => 'badge bg-success',

            self::RESERVEE => 'badge bg-info text-dark',
            self::DOSSIER_EN_COURS => 'badge bg-warning text-dark',
            self::BAIL_SIGNE => 'badge bg-success',
            self::LOUEE => 'badge bg-success',

            self::ARCHIVEE => 'badge bg-dark',
            self::SUPPRIMEE => 'badge bg-danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BROUILLON => 'pencil',
            self::PENDING => 'clock',
            self::A_CORRIGER => 'triangle-alert',
            self::VALIDATE => 'circle-check',
            self::REFUSEE => 'circle-x',

            self::PUBLIEE => 'eye',
            self::DEPUBLIEE => 'eye-off',
            self::SUSPENDUE => 'ban',
            self::SUSPENDED_BY_PLAN => 'ban',
            self::EXPIREE => 'calendar-x',

            self::DISPONIBLE => 'badge-check',
            self::SOUS_OFFRE => 'handshake',
            self::OFFRE_ACCEPTEE => 'thumbs-up',
            self::COMPROMIS_SIGNE => 'file-pen-line',
            self::VENDUE => 'badge-euro',

            self::RESERVEE => 'bookmark-check',
            self::DOSSIER_EN_COURS => 'folder-clock',
            self::BAIL_SIGNE => 'file-check',
            self::LOUEE => 'key-round',

            self::ARCHIVEE => 'archive',
            self::SUPPRIMEE => 'trash-2',
        };
    }

    public function isVisiblePublic(): bool
    {
        return match ($this) {
            self::PUBLIEE,
            self::DISPONIBLE,
            self::SOUS_OFFRE,
            self::OFFRE_ACCEPTEE,
            self::RESERVEE,
            self::DOSSIER_EN_COURS => true,

            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::VENDUE,
            self::LOUEE,
            self::ARCHIVEE,
            self::SUPPRIMEE,
            self::REFUSEE => true,

            default => false,
        };
    }

    public static function choices(): array
    {
        return [
            'Brouillon' => self::BROUILLON,
            'En attente de validation' => self::PENDING,
            'À corriger' => self::A_CORRIGER,
            'Validée' => self::VALIDATE,
            'Refusée' => self::REFUSEE,

            'Publiée' => self::PUBLIEE,
            'Dépubliée' => self::DEPUBLIEE,
            'Suspendue' => self::SUSPENDUE,
            'Suspendue par limitation de forfait' => self::SUSPENDED_BY_PLAN,
            'Expirée' => self::EXPIREE,

            'Disponible' => self::DISPONIBLE,
            'Sous offre' => self::SOUS_OFFRE,
            'Offre acceptée' => self::OFFRE_ACCEPTEE,
            'Compromis signé' => self::COMPROMIS_SIGNE,
            'Vendue' => self::VENDUE,

            'Réservée' => self::RESERVEE,
            'Dossier en cours' => self::DOSSIER_EN_COURS,
            'Bail signé' => self::BAIL_SIGNE,
            'Louée' => self::LOUEE,

            'Archivée' => self::ARCHIVEE,
            'Supprimée' => self::SUPPRIMEE,
        ];
    }
}
