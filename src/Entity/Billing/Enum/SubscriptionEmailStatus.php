<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum SubscriptionEmailStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
