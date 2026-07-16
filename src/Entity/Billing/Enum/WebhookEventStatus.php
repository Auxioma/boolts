<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum WebhookEventStatus: string
{
    case RECEIVED = 'received';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case IGNORED = 'ignored';
    case FAILED = 'failed';
}
