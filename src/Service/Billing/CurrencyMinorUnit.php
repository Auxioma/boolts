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

namespace App\Service\Billing;

use App\Entity\Devise;

/**
 * Conversion des montants stockés en « unités mineures » (amountMinor) selon
 * la convention de Stripe, qui ne divise pas toutes les devises par 100 :
 *
 * - devises sans décimales (JPY, KRW…) : amountMinor = montant entier ;
 * - ISK : sans décimales, mais Stripe le représente sur deux décimales
 *   (montant × 100, multiple de 100) ;
 * - toutes les autres : amountMinor = montant × 100.
 *
 * @see https://docs.stripe.com/currencies#zero-decimal
 */
final class CurrencyMinorUnit
{
    /** Devises sans décimales chez Stripe. */
    private const ZERO_DECIMAL = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /** Devises représentées sur deux décimales chez Stripe mais affichées sans. */
    private const DISPLAYED_WITHOUT_DECIMALS = ['ISK'];

    /**
     * Code ISO 4217 d'une devise : l'entité Devise ne le stocke pas, il est
     * lu dans son nom (« euro (EUR) »). Accepte aussi directement un nom ou un code.
     */
    public function code(Devise|string|null $currency): ?string
    {
        $value = $currency instanceof Devise ? (string) $currency->getNom() : (string) $currency;

        if (preg_match('/\(([A-Z]{3})\)/', $value, $matches)) {
            return $matches[1];
        }

        return preg_match('/^[A-Z]{3}$/', $value) ? $value : null;
    }

    public function divisor(Devise|string|null $currency): int
    {
        return \in_array($this->code($currency), self::ZERO_DECIMAL, true) ? 1 : 100;
    }

    public function decimals(Devise|string|null $currency): int
    {
        $code = $this->code($currency);

        return \in_array($code, self::ZERO_DECIMAL, true) || \in_array($code, self::DISPLAYED_WITHOUT_DECIMALS, true) ? 0 : 2;
    }

    public function toMajor(int $amountMinor, Devise|string|null $currency): float
    {
        return $amountMinor / $this->divisor($currency);
    }

    /** Montant formaté sans symbole, au format français (« 1 999,99 », « 3 999 »). */
    public function format(int $amountMinor, Devise|string|null $currency): string
    {
        return number_format($this->toMajor($amountMinor, $currency), $this->decimals($currency), ',', ' ');
    }
}
