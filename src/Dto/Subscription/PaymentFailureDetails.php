<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

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
