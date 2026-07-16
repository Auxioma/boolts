<?php

declare(strict_types=1);

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
