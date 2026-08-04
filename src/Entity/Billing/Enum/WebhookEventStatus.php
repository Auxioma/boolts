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

namespace App\Entity\Billing\Enum;

enum WebhookEventStatus: string
{
    case RECEIVED = 'received';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case IGNORED = 'ignored';
    case FAILED = 'failed';
}
