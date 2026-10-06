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
 * Catalogue des langues ISO 639-1 avec leur nom français et le pays natif
 * (code ISO 3166-1 alpha-2, ou subdivision ISO 3166-2 type "GB-WLS") utilisé pour le drapeau.
 * Un pays à null signifie qu'aucun drapeau ne correspond (langues construites ou mortes).
 */
final class LanguageCatalog
{
    /**
     * @var array<string, array{name: string, country: ?string}>
     */
    public const LANGUAGES = [
        'aa' => ['name' => 'Afar', 'country' => 'ET'],
        'ab' => ['name' => 'Abkhaze', 'country' => 'GE'],
        'ae' => ['name' => 'Avestique', 'country' => null],
        'af' => ['name' => 'Afrikaans', 'country' => 'ZA'],
        'ak' => ['name' => 'Akan', 'country' => 'GH'],
        'am' => ['name' => 'Amharique', 'country' => 'ET'],
        'an' => ['name' => 'Aragonais', 'country' => 'ES'],
        'ar' => ['name' => 'Arabe', 'country' => 'SA'],
        'as' => ['name' => 'Assamais', 'country' => 'IN'],
        'av' => ['name' => 'Avar', 'country' => 'RU'],
        'ay' => ['name' => 'Aymara', 'country' => 'BO'],
        'az' => ['name' => 'Azerbaïdjanais', 'country' => 'AZ'],
        'ba' => ['name' => 'Bachkir', 'country' => 'RU'],
        'be' => ['name' => 'Biélorusse', 'country' => 'BY'],
        'bg' => ['name' => 'Bulgare', 'country' => 'BG'],
        'bi' => ['name' => 'Bichelamar', 'country' => 'VU'],
        'bm' => ['name' => 'Bambara', 'country' => 'ML'],
        'bn' => ['name' => 'Bengali', 'country' => 'BD'],
        'bo' => ['name' => 'Tibétain', 'country' => 'CN'],
        'br' => ['name' => 'Breton', 'country' => 'FR'],
        'bs' => ['name' => 'Bosnien', 'country' => 'BA'],
        'ca' => ['name' => 'Catalan', 'country' => 'ES'],
        'ce' => ['name' => 'Tchétchène', 'country' => 'RU'],
        'ch' => ['name' => 'Chamorro', 'country' => 'GU'],
        'co' => ['name' => 'Corse', 'country' => 'FR'],
        'cr' => ['name' => 'Cri', 'country' => 'CA'],
        'cs' => ['name' => 'Tchèque', 'country' => 'CZ'],
        'cu' => ['name' => 'Vieux-slave', 'country' => null],
        'cv' => ['name' => 'Tchouvache', 'country' => 'RU'],
        'cy' => ['name' => 'Gallois', 'country' => 'GB-WLS'],
        'da' => ['name' => 'Danois', 'country' => 'DK'],
        'de' => ['name' => 'Allemand', 'country' => 'DE'],
        'dv' => ['name' => 'Maldivien', 'country' => 'MV'],
        'dz' => ['name' => 'Dzongkha', 'country' => 'BT'],
        'ee' => ['name' => 'Éwé', 'country' => 'GH'],
        'el' => ['name' => 'Grec', 'country' => 'GR'],
        'en' => ['name' => 'Anglais', 'country' => 'GB'],
        'eo' => ['name' => 'Espéranto', 'country' => null],
        'es' => ['name' => 'Espagnol', 'country' => 'ES'],
        'et' => ['name' => 'Estonien', 'country' => 'EE'],
        'eu' => ['name' => 'Basque', 'country' => 'ES'],
        'fa' => ['name' => 'Persan', 'country' => 'IR'],
        'ff' => ['name' => 'Peul', 'country' => 'SN'],
        'fi' => ['name' => 'Finnois', 'country' => 'FI'],
        'fj' => ['name' => 'Fidjien', 'country' => 'FJ'],
        'fo' => ['name' => 'Féroïen', 'country' => 'FO'],
        'fr' => ['name' => 'Français', 'country' => 'FR'],
        'fy' => ['name' => 'Frison occidental', 'country' => 'NL'],
        'ga' => ['name' => 'Irlandais', 'country' => 'IE'],
        'gd' => ['name' => 'Gaélique écossais', 'country' => 'GB-SCT'],
        'gl' => ['name' => 'Galicien', 'country' => 'ES'],
        'gn' => ['name' => 'Guarani', 'country' => 'PY'],
        'gu' => ['name' => 'Gujarati', 'country' => 'IN'],
        'gv' => ['name' => 'Mannois', 'country' => 'IM'],
        'ha' => ['name' => 'Haoussa', 'country' => 'NG'],
        'he' => ['name' => 'Hébreu', 'country' => 'IL'],
        'hi' => ['name' => 'Hindi', 'country' => 'IN'],
        'ho' => ['name' => 'Hiri motu', 'country' => 'PG'],
        'hr' => ['name' => 'Croate', 'country' => 'HR'],
        'ht' => ['name' => 'Créole haïtien', 'country' => 'HT'],
        'hu' => ['name' => 'Hongrois', 'country' => 'HU'],
        'hy' => ['name' => 'Arménien', 'country' => 'AM'],
        'hz' => ['name' => 'Héréro', 'country' => 'NA'],
        'ia' => ['name' => 'Interlingua', 'country' => null],
        'id' => ['name' => 'Indonésien', 'country' => 'ID'],
        'ie' => ['name' => 'Interlingue', 'country' => null],
        'ig' => ['name' => 'Igbo', 'country' => 'NG'],
        'ii' => ['name' => 'Yi du Sichuan', 'country' => 'CN'],
        'ik' => ['name' => 'Inupiaq', 'country' => 'US'],
        'io' => ['name' => 'Ido', 'country' => null],
        'is' => ['name' => 'Islandais', 'country' => 'IS'],
        'it' => ['name' => 'Italien', 'country' => 'IT'],
        'iu' => ['name' => 'Inuktitut', 'country' => 'CA'],
        'ja' => ['name' => 'Japonais', 'country' => 'JP'],
        'jv' => ['name' => 'Javanais', 'country' => 'ID'],
        'ka' => ['name' => 'Géorgien', 'country' => 'GE'],
        'kg' => ['name' => 'Kikongo', 'country' => 'CD'],
        'ki' => ['name' => 'Kikuyu', 'country' => 'KE'],
        'kj' => ['name' => 'Kuanyama', 'country' => 'NA'],
        'kk' => ['name' => 'Kazakh', 'country' => 'KZ'],
        'kl' => ['name' => 'Groenlandais', 'country' => 'GL'],
        'km' => ['name' => 'Khmer', 'country' => 'KH'],
        'kn' => ['name' => 'Kannada', 'country' => 'IN'],
        'ko' => ['name' => 'Coréen', 'country' => 'KR'],
        'kr' => ['name' => 'Kanouri', 'country' => 'NG'],
        'ks' => ['name' => 'Cachemiri', 'country' => 'IN'],
        'ku' => ['name' => 'Kurde', 'country' => 'IQ'],
        'kv' => ['name' => 'Komi', 'country' => 'RU'],
        'kw' => ['name' => 'Cornique', 'country' => 'GB'],
        'ky' => ['name' => 'Kirghiz', 'country' => 'KG'],
        'la' => ['name' => 'Latin', 'country' => 'VA'],
        'lb' => ['name' => 'Luxembourgeois', 'country' => 'LU'],
        'lg' => ['name' => 'Luganda', 'country' => 'UG'],
        'li' => ['name' => 'Limbourgeois', 'country' => 'NL'],
        'ln' => ['name' => 'Lingala', 'country' => 'CD'],
        'lo' => ['name' => 'Lao', 'country' => 'LA'],
        'lt' => ['name' => 'Lituanien', 'country' => 'LT'],
        'lu' => ['name' => 'Luba-katanga', 'country' => 'CD'],
        'lv' => ['name' => 'Letton', 'country' => 'LV'],
        'mg' => ['name' => 'Malgache', 'country' => 'MG'],
        'mh' => ['name' => 'Marshallais', 'country' => 'MH'],
        'mi' => ['name' => 'Maori', 'country' => 'NZ'],
        'mk' => ['name' => 'Macédonien', 'country' => 'MK'],
        'ml' => ['name' => 'Malayalam', 'country' => 'IN'],
        'mn' => ['name' => 'Mongol', 'country' => 'MN'],
        'mr' => ['name' => 'Marathi', 'country' => 'IN'],
        'ms' => ['name' => 'Malais', 'country' => 'MY'],
        'mt' => ['name' => 'Maltais', 'country' => 'MT'],
        'my' => ['name' => 'Birman', 'country' => 'MM'],
        'na' => ['name' => 'Nauruan', 'country' => 'NR'],
        'nb' => ['name' => 'Norvégien bokmål', 'country' => 'NO'],
        'nd' => ['name' => 'Ndébélé du Nord', 'country' => 'ZW'],
        'ne' => ['name' => 'Népalais', 'country' => 'NP'],
        'ng' => ['name' => 'Ndonga', 'country' => 'NA'],
        'nl' => ['name' => 'Néerlandais', 'country' => 'NL'],
        'nn' => ['name' => 'Norvégien nynorsk', 'country' => 'NO'],
        'no' => ['name' => 'Norvégien', 'country' => 'NO'],
        'nr' => ['name' => 'Ndébélé du Sud', 'country' => 'ZA'],
        'nv' => ['name' => 'Navajo', 'country' => 'US'],
        'ny' => ['name' => 'Chichewa', 'country' => 'MW'],
        'oc' => ['name' => 'Occitan', 'country' => 'FR'],
        'oj' => ['name' => 'Ojibwé', 'country' => 'CA'],
        'om' => ['name' => 'Oromo', 'country' => 'ET'],
        'or' => ['name' => 'Odia', 'country' => 'IN'],
        'os' => ['name' => 'Ossète', 'country' => 'RU'],
        'pa' => ['name' => 'Pendjabi', 'country' => 'IN'],
        'pi' => ['name' => 'Pali', 'country' => 'IN'],
        'pl' => ['name' => 'Polonais', 'country' => 'PL'],
        'ps' => ['name' => 'Pachto', 'country' => 'AF'],
        'pt' => ['name' => 'Portugais', 'country' => 'PT'],
        'qu' => ['name' => 'Quechua', 'country' => 'PE'],
        'rm' => ['name' => 'Romanche', 'country' => 'CH'],
        'rn' => ['name' => 'Kirundi', 'country' => 'BI'],
        'ro' => ['name' => 'Roumain', 'country' => 'RO'],
        'ru' => ['name' => 'Russe', 'country' => 'RU'],
        'rw' => ['name' => 'Kinyarwanda', 'country' => 'RW'],
        'sa' => ['name' => 'Sanskrit', 'country' => 'IN'],
        'sc' => ['name' => 'Sarde', 'country' => 'IT'],
        'sd' => ['name' => 'Sindhi', 'country' => 'PK'],
        'se' => ['name' => 'Same du Nord', 'country' => 'NO'],
        'sg' => ['name' => 'Sango', 'country' => 'CF'],
        'si' => ['name' => 'Cingalais', 'country' => 'LK'],
        'sk' => ['name' => 'Slovaque', 'country' => 'SK'],
        'sl' => ['name' => 'Slovène', 'country' => 'SI'],
        'sm' => ['name' => 'Samoan', 'country' => 'WS'],
        'sn' => ['name' => 'Shona', 'country' => 'ZW'],
        'so' => ['name' => 'Somali', 'country' => 'SO'],
        'sq' => ['name' => 'Albanais', 'country' => 'AL'],
        'sr' => ['name' => 'Serbe', 'country' => 'RS'],
        'ss' => ['name' => 'Swati', 'country' => 'SZ'],
        'st' => ['name' => 'Sotho du Sud', 'country' => 'LS'],
        'su' => ['name' => 'Soundanais', 'country' => 'ID'],
        'sv' => ['name' => 'Suédois', 'country' => 'SE'],
        'sw' => ['name' => 'Swahili', 'country' => 'TZ'],
        'ta' => ['name' => 'Tamoul', 'country' => 'IN'],
        'te' => ['name' => 'Télougou', 'country' => 'IN'],
        'tg' => ['name' => 'Tadjik', 'country' => 'TJ'],
        'th' => ['name' => 'Thaï', 'country' => 'TH'],
        'ti' => ['name' => 'Tigrigna', 'country' => 'ER'],
        'tk' => ['name' => 'Turkmène', 'country' => 'TM'],
        'tl' => ['name' => 'Tagalog', 'country' => 'PH'],
        'tn' => ['name' => 'Tswana', 'country' => 'BW'],
        'to' => ['name' => 'Tongien', 'country' => 'TO'],
        'tr' => ['name' => 'Turc', 'country' => 'TR'],
        'ts' => ['name' => 'Tsonga', 'country' => 'ZA'],
        'tt' => ['name' => 'Tatar', 'country' => 'RU'],
        'tw' => ['name' => 'Twi', 'country' => 'GH'],
        'ty' => ['name' => 'Tahitien', 'country' => 'PF'],
        'ug' => ['name' => 'Ouïghour', 'country' => 'CN'],
        'uk' => ['name' => 'Ukrainien', 'country' => 'UA'],
        'ur' => ['name' => 'Ourdou', 'country' => 'PK'],
        'uz' => ['name' => 'Ouzbek', 'country' => 'UZ'],
        've' => ['name' => 'Venda', 'country' => 'ZA'],
        'vi' => ['name' => 'Vietnamien', 'country' => 'VN'],
        'vo' => ['name' => 'Volapük', 'country' => null],
        'wa' => ['name' => 'Wallon', 'country' => 'BE'],
        'wo' => ['name' => 'Wolof', 'country' => 'SN'],
        'xh' => ['name' => 'Xhosa', 'country' => 'ZA'],
        'yi' => ['name' => 'Yiddish', 'country' => 'IL'],
        'yo' => ['name' => 'Yoruba', 'country' => 'NG'],
        'za' => ['name' => 'Zhuang', 'country' => 'CN'],
        'zh' => ['name' => 'Chinois', 'country' => 'CN'],
        'zu' => ['name' => 'Zoulou', 'country' => 'ZA'],
    ];

    /**
     * Construit l'emoji drapeau à partir d'un code pays ISO 3166-1 ("FR")
     * ou d'une subdivision ISO 3166-2 supportée par Unicode ("GB-WLS", "GB-SCT", "GB-ENG").
     */
    public static function flagFor(string $languageCode): ?string
    {
        $country = self::LANGUAGES[strtolower($languageCode)]['country'] ?? null;

        if (null === $country) {
            return null;
        }

        $country = strtoupper($country);

        if (str_contains($country, '-')) {
            // Drapeau de subdivision : 🏴 + caractères "tag" + terminateur.
            $flag = mb_chr(0x1F3F4);
            foreach (str_split(strtolower(str_replace('-', '', $country))) as $char) {
                $flag .= mb_chr(0xE0000 + ord($char));
            }

            return $flag.mb_chr(0xE007F);
        }

        // Drapeau national : deux "regional indicator symbols".
        return mb_chr(0x1F1E6 + ord($country[0]) - ord('A'))
            .mb_chr(0x1F1E6 + ord($country[1]) - ord('A'));
    }
}
