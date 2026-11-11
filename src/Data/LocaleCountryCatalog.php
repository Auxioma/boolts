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

namespace App\Data;

/**
 * Pays francophones et anglophones du sélecteur de la homepage
 * (code ISO 3166-1 alpha-2 => locale du site : "fr" => /fr, "en" => /).
 */
final class LocaleCountryCatalog
{
    /**
     * @var array<string, string>
     */
    public const COUNTRIES = [
        // Francophones
        'FR' => 'fr', 'BE' => 'fr', 'CH' => 'fr', 'LU' => 'fr', 'MC' => 'fr',
        'GP' => 'fr', 'MQ' => 'fr', 'GF' => 'fr', 'RE' => 'fr', 'YT' => 'fr',
        'PM' => 'fr', 'BL' => 'fr', 'MF' => 'fr', 'NC' => 'fr', 'PF' => 'fr', 'WF' => 'fr',
        'BJ' => 'fr', 'BF' => 'fr', 'CF' => 'fr', 'KM' => 'fr', 'CG' => 'fr', 'CD' => 'fr',
        'CI' => 'fr', 'DJ' => 'fr', 'GQ' => 'fr', 'GA' => 'fr', 'GN' => 'fr', 'HT' => 'fr',
        'MG' => 'fr', 'ML' => 'fr', 'NE' => 'fr', 'SN' => 'fr', 'TD' => 'fr', 'TG' => 'fr',
        'CM' => 'fr', 'BI' => 'fr', 'MA' => 'fr', 'DZ' => 'fr', 'TN' => 'fr', 'LB' => 'fr',
        'MU' => 'fr', 'VU' => 'fr',

        // Anglophones
        'US' => 'en', 'GB' => 'en', 'IE' => 'en', 'CA' => 'en', 'AU' => 'en', 'NZ' => 'en',
        'MT' => 'en', 'ZA' => 'en', 'NG' => 'en', 'GH' => 'en', 'KE' => 'en', 'UG' => 'en',
        'TZ' => 'en', 'ZM' => 'en', 'ZW' => 'en', 'BW' => 'en', 'NA' => 'en', 'MW' => 'en',
        'SL' => 'en', 'LR' => 'en', 'GM' => 'en', 'SS' => 'en', 'LS' => 'en', 'SZ' => 'en',
        'RW' => 'en', 'SC' => 'en',
        'IN' => 'en', 'PK' => 'en', 'PH' => 'en', 'SG' => 'en',
        'JM' => 'en', 'BS' => 'en', 'BB' => 'en', 'TT' => 'en', 'BZ' => 'en', 'GY' => 'en',
        'AG' => 'en', 'DM' => 'en', 'GD' => 'en', 'KN' => 'en', 'LC' => 'en', 'VC' => 'en',
        'FJ' => 'en', 'PG' => 'en', 'SB' => 'en', 'KI' => 'en', 'TV' => 'en', 'NR' => 'en',
        'PW' => 'en', 'MH' => 'en', 'FM' => 'en', 'WS' => 'en', 'TO' => 'en',
    ];

    /**
     * Pays bilingues : la région (code ISO 3166-2 sans le préfixe pays,
     * ex. "QC") prime sur la langue du pays.
     *
     * @var array<string, array<string, string>>
     */
    public const REGIONS = [
        // Canada : Québec francophone, Nouveau-Brunswick bilingue (fr), reste anglophone.
        'CA' => ['QC' => 'fr', 'NB' => 'fr'],
        // Cameroun : Nord-Ouest et Sud-Ouest anglophones, reste francophone.
        'CM' => ['NW' => 'en', 'SW' => 'en'],
    ];

    /**
     * Locale du site ("fr" ou "en") pour un pays et, si connue, sa région ;
     * null si le pays n'est ni francophone ni anglophone.
     */
    public static function localeFor(?string $countryCode, ?string $regionCode = null): ?string
    {
        $country = strtoupper(trim((string) $countryCode));

        if (!isset(self::COUNTRIES[$country])) {
            return null;
        }

        $region = strtoupper(trim((string) $regionCode));

        if (str_contains($region, '-')) {
            $region = substr($region, strrpos($region, '-') + 1);
        }

        return self::REGIONS[$country][$region] ?? self::COUNTRIES[$country];
    }
}
