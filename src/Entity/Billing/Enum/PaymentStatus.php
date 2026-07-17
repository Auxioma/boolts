<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity\Billing\Enum;

enum PaymentStatus: string
{
    case CREATED = 'created';
    case PENDING = 'pending';
    case REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    case REQUIRES_ACTION = 'requires_action';
    case PROCESSING = 'processing';
    case AUTHORIZED = 'authorized';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';
}
