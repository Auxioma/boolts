<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Raised when a subscription plan change cannot be scheduled or cancelled because
 * a business rule is violated (currency mismatch, not a downgrade, no active paid
 * subscription, ...). Carries a user-facing message.
 */
final class PlanChangeException extends \RuntimeException
{
}
