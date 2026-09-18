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

namespace App\Exception;

/**
 * Raised when a subscription plan change cannot be scheduled or cancelled because
 * a business rule is violated (currency mismatch, not a downgrade, no active paid
 * subscription, ...). Carries a user-facing message.
 */
final class PlanChangeException extends \RuntimeException
{
}
