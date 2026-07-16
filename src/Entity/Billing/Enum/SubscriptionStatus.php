<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum SubscriptionStatus: string
{
    case FREE = 'free';
    case INCOMPLETE = 'incomplete';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case CANCELED = 'canceled';
    case UNPAID = 'unpaid';
    case EXPIRED = 'expired';
}
