<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum SubscriptionPeriodStatus: string
{
    case PENDING = 'pending';
    case FREE = 'free';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
