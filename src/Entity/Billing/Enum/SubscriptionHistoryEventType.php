<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum SubscriptionHistoryEventType: string
{
    case SUBSCRIPTION_CREATED = 'subscription_created';
    case RENEWAL_SUCCEEDED = 'renewal_succeeded';
    case RENEWAL_FAILED = 'renewal_failed';
    case PAYMENT_RETRY = 'payment_retry';
    case PAYMENT_RECOVERED = 'payment_recovered';
    case PAYMENT_DEFINITIVELY_FAILED = 'payment_definitively_failed';
    case CANCEL_REQUESTED = 'cancel_requested';
    case CANCEL_REVOKED = 'cancel_revoked';
    case SUBSCRIPTION_ENDED = 'subscription_ended';
    case DOWNGRADED_TO_FREE = 'downgraded_to_free';
    case STRIPE_SYNCHRONIZED = 'stripe_synchronized';
}
