<?php

declare(strict_types=1);

namespace App\Message\Billing;

final readonly class SendSubscriptionEmailMessage
{
    public function __construct(
        public int $emailLogId,
    ) {
    }
}
