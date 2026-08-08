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

enum RefundReason: string
{
    case REQUESTED_BY_CUSTOMER = 'requested_by_customer';
    case DUPLICATE = 'duplicate';
    case FRAUDULENT = 'fraudulent';
    case SUBSCRIPTION_CANCELATION = 'subscription_cancelation';
    case SERVICE_NOT_PROVIDED = 'service_not_provided';
    case ADMINISTRATIVE_ERROR = 'administrative_error';
    case OTHER = 'other';
}
