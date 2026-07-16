<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum PropertyBoostStatus: string
{
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';
}
