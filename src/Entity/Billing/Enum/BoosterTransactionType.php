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

enum BoosterTransactionType: string
{
    case SUBSCRIPTION_CREDIT = 'subscription_credit';
    case PACK_PURCHASE = 'pack_purchase';
    case PROPERTY_BOOST = 'property_boost';
    case REFUND = 'refund';
    case EXPIRATION = 'expiration';
    case ADMIN_CREDIT = 'admin_credit';
    case ADMIN_DEBIT = 'admin_debit';
}
