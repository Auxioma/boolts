<?php

declare(strict_types=1);

namespace App\Dto\Subscription;

final readonly class PaymentFailureDetails
{
    public function __construct(
        public ?string $failureCode,
        public ?string $failureMessage,
        public ?string $declineCode = null,
        public ?string $requiresActionType = null,
    ) {
    }

    public static function empty(): self
    {
        return new self(null, null);
    }
}
