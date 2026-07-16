<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum InvoiceType: string
{
    case SUBSCRIPTION = 'subscription';
    case BOOSTER_PACK = 'booster_pack';
    case ADJUSTMENT = 'adjustment';
}
