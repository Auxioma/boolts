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

enum DowngradeReason: string
{
    case PAYMENT_DEFINITIVELY_FAILED = 'payment_definitively_failed';
    case CANCEL_AT_PERIOD_END = 'cancel_at_period_end';
    case STRIPE_SUBSCRIPTION_DELETED = 'stripe_subscription_deleted';
    case ADMIN_OR_SYSTEM = 'admin_or_system';
}
