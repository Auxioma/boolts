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

namespace App\Entity\Enum;

enum DocumentRequestStatus: string
{
    case WAITING_UPLOAD = 'waiting_upload';
    case UNDER_REVIEW = 'under_review';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case BLOCKED = 'blocked';
}
