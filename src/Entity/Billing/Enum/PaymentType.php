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

enum PaymentType: string
{
    case SUBSCRIPTION_INITIAL = 'subscription_initial';
    case SUBSCRIPTION_RENEWAL = 'subscription_renewal';
    case SUBSCRIPTION_UPGRADE = 'subscription_upgrade';
    case SUBSCRIPTION_DOWNGRADE_ADJUSTMENT = 'subscription_downgrade_adjustment';
    case BOOSTER_PACK = 'booster_pack';
    case MANUAL_ADJUSTMENT = 'manual_adjustment';
}
