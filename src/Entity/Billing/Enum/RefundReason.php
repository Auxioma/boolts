<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum RefundReason: string
{
    case REQUESTED_BY_CUSTOMER = 'requested_by_customer';
    case DUPLICATE = 'duplicate';
    case FRAUDULENT = 'fraudulent';
    case SUBSCRIPTION_CANCELATION = 'subscription_cancelation';
    case SERVICE_NOT_PROVIDED = 'service_not_provided';
    case ADMINISTRATIVE_ERROR = 'administrative_error';
    case OTHER = 'other';
}
