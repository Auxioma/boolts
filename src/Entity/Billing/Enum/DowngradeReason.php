<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum DowngradeReason: string
{
    case PAYMENT_DEFINITIVELY_FAILED = 'payment_definitively_failed';
    case CANCEL_AT_PERIOD_END = 'cancel_at_period_end';
    case STRIPE_SUBSCRIPTION_DELETED = 'stripe_subscription_deleted';
    case ADMIN_OR_SYSTEM = 'admin_or_system';
}
