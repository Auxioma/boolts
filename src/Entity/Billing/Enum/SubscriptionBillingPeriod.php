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

enum SubscriptionBillingPeriod: string
{
    case MONTHLY = 'monthly';
    case ANNUAL = 'annual';

    public static function fromBillingInterval(string $interval): self
    {
        return match (mb_strtolower(mb_trim($interval))) {
            'monthly', 'month' => self::MONTHLY,
            'yearly', 'annual', 'year' => self::ANNUAL,
            default => throw new \InvalidArgumentException(\sprintf(
                'Périodicité d’abonnement invalide : %s.',
                $interval
            )),
        };
    }

    public function stripeInterval(): string
    {
        return match ($this) {
            self::MONTHLY => 'month',
            self::ANNUAL => 'year',
        };
    }
}
