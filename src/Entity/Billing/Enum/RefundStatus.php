<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum RefundStatus: string
{
    case PENDING = 'pending';
    case REQUIRES_ACTION = 'requires_action';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
}
