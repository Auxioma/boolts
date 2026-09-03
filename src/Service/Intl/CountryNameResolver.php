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

namespace App\Service\Intl;

use Symfony\Component\Intl\Countries;

/**
 * Convertit un nom de pays (saisi ou importé, en français ou en anglais) en
 * code ISO 3166-1 alpha-2, et inversement.
 *
 * Utile lorsqu'un champ stocke le pays sous forme de libellé ("France") alors
 * qu'un widget de formulaire (CountryType) attend le code ISO ("FR").
 */
final class CountryNameResolver
{
    /**
     * Cache du dictionnaire "nom normalisé => code ISO".
     *
     * @var array<string, string>|null
     */
    private ?array $nameToCode = null;

    /**
     * Retourne le code ISO alpha-2 correspondant à la valeur fournie.
     *
     * Accepte aussi bien un code déjà valide ("FR", "fr") qu'un libellé
     * ("France", "Belgique"). Retourne null si rien ne correspond.
     */
    public function toAlphaTwoCode(?string $value): ?string
    {
        $value = null !== $value ? mb_trim($value) : '';

        if ('' === $value) {
            return null;
        }

        $upper = mb_strtoupper($value);

        if (2 === mb_strlen($upper) && Countries::exists($upper)) {
            return $upper;
        }

        return $this->nameToCodeMap()[$this->normalizeKey($value)] ?? null;
    }

    /**
     * Retourne le libellé du pays dans la locale demandée, ou null si le code
     * est inconnu.
     */
    public function toName(?string $code, string $locale): ?string
    {
        $code = null !== $code ? mb_strtoupper(mb_trim($code)) : '';

        if ('' === $code || !Countries::exists($code)) {
            return null;
        }

        try {
            return Countries::getName($code, $locale);
        } catch (\Throwable) {
            return Countries::getName($code);
        }
    }

    /**
     * @return array<string, string>
     */
    private function nameToCodeMap(): array
    {
        if (null !== $this->nameToCode) {
            return $this->nameToCode;
        }

        $map = [];

        foreach (['fr', 'en'] as $locale) {
            try {
                foreach (Countries::getNames($locale) as $code => $name) {
                    $map[$this->normalizeKey($name)] = $code;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $this->nameToCode = $map;
    }

    /**
     * Normalise un libellé pour la comparaison : minuscules, sans accents,
     * espaces multiples réduits.
     */
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
