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
        'CM' => 'fr', 'BI' => 'fr',

        // Anglophones
        'US' => 'en', 'GB' => 'en', 'IE' => 'en', 'CA' => 'en', 'AU' => 'en', 'NZ' => 'en',
        'MT' => 'en', 'ZA' => 'en', 'NG' => 'en', 'GH' => 'en', 'KE' => 'en', 'UG' => 'en',
        'TZ' => 'en', 'ZM' => 'en', 'ZW' => 'en', 'BW' => 'en', 'NA' => 'en', 'MW' => 'en',
        'SL' => 'en', 'LR' => 'en', 'GM' => 'en', 'SS' => 'en', 'LS' => 'en', 'SZ' => 'en',
        'MU' => 'en', 'RW' => 'en', 'SC' => 'en', 'VU' => 'en',
        'IN' => 'en', 'PK' => 'en', 'PH' => 'en', 'SG' => 'en',
        'JM' => 'en', 'BS' => 'en', 'BB' => 'en', 'TT' => 'en', 'BZ' => 'en', 'GY' => 'en',
        'AG' => 'en', 'DM' => 'en', 'GD' => 'en', 'KN' => 'en', 'LC' => 'en', 'VC' => 'en',
        'FJ' => 'en', 'PG' => 'en', 'SB' => 'en', 'KI' => 'en', 'TV' => 'en', 'NR' => 'en',
        'PW' => 'en', 'MH' => 'en', 'FM' => 'en', 'WS' => 'en', 'TO' => 'en',
    ];
}
