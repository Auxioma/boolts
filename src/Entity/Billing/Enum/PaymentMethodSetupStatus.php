<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum PaymentMethodSetupStatus: string
{
    case PENDING = 'pending';
    case REQUIRES_ACTION = 'requires_action';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case DETACHED = 'detached';
    case EXPIRED = 'expired';
}
