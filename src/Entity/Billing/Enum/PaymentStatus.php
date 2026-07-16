<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum PaymentStatus: string
{
    case CREATED = 'created';
    case PENDING = 'pending';
    case REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
    case REQUIRES_ACTION = 'requires_action';
    case PROCESSING = 'processing';
    case AUTHORIZED = 'authorized';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';
}
