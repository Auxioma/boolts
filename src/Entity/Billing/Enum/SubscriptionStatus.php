<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity\Billing\Enum;

enum SubscriptionStatus: string
{
    case FREE = 'free';
    case INCOMPLETE = 'incomplete';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case PAYMENT_FAILED = 'payment_failed';
    case CANCEL_SCHEDULED = 'cancel_scheduled';
    case CANCELED = 'canceled';
    case UNPAID = 'unpaid';
    case EXPIRED = 'expired';

    public function isPaidAccessAllowed(): bool
    {
        return match ($this) {
            self::ACTIVE,
            self::PAST_DUE,
            self::PAYMENT_FAILED,
            self::CANCEL_SCHEDULED => true,
            default => false,
        };
    }

    public function isRecoverableFailure(): bool
    {
        return match ($this) {
            self::PAST_DUE,
            self::PAYMENT_FAILED,
            self::UNPAID => true,
            default => false,
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::CANCELED,
            self::EXPIRED => true,
            default => false,
        };
    }
}
