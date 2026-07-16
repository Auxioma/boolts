<?php

declare(strict_types=1);

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
