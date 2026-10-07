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

namespace App\Service\Property;

use Symfony\Component\Intl\Countries;

/**
 * Résout le code ISO 3166-1 alpha-2 d'un pays à partir de son nom
 * (français ou anglais), tel que saisi par les agences sur leurs annonces.
 */
final class CountryCodeResolver
{
    /** @var array<string, string>|null */
    private ?array $map = null;

    public function resolve(string $name): ?string
    {
        if (null === $this->map) {
            $this->map = [];

            foreach (['fr', 'en'] as $locale) {
                try {
                    foreach (Countries::getNames($locale) as $code => $countryName) {
                        $this->map[$this->normalizeKey($countryName)] = $code;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return $this->map[$this->normalizeKey($name)] ?? null;
    }

    private function normalizeKey(string $value): string
    {
        $value = mb_strtolower(mb_trim($value));

        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D) ?: $value;
        }

        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', $value) ?? $value;

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }
}
