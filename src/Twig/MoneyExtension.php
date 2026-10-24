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

namespace App\Twig;

use App\Entity\Devise;
use App\Service\Billing\CurrencyMinorUnit;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Affichage des montants de facturation stockés en unités mineures.
 *
 *   {{ price.amountMinor|minor_amount(price.currency) }}      → « 19,99 » / « 3 999 »
 *   {{ price.amountMinor|minor_to_major(price.currency) }}    → 19.99 (nombre, pour le JS)
 *
 * La devise peut être une entité Devise, son nom (« yen japonais (JPY) ») ou son code.
 */
final class MoneyExtension extends AbstractExtension
{
    public function __construct(
        private readonly CurrencyMinorUnit $currencyMinorUnit,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('minor_amount', $this->formatMinorAmount(...)),
            new TwigFilter('minor_to_major', $this->minorToMajor(...)),
        ];
    }

    public function formatMinorAmount(?int $amountMinor, Devise|string|null $currency): string
    {
        return $this->currencyMinorUnit->format($amountMinor ?? 0, $currency);
    }

    public function minorToMajor(?int $amountMinor, Devise|string|null $currency): float
    {
        return $this->currencyMinorUnit->toMajor($amountMinor ?? 0, $currency);
    }
}
