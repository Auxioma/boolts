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

namespace App\Service\Import;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\UnicodeString;

/**
 * Mappage entre les colonnes d'un CSV quelconque et les champs attendus par
 * {@see PropertyCsvImporter}.
 *
 * Chaque champ attendu porte un libellé français et anglais ainsi qu'une liste
 * de synonymes (FR/EN) servant à proposer automatiquement un rapprochement à
 * partir des en-têtes du fichier (« Price », « Prix de vente », « title_en »,
 * « Titre (anglais) »…). L'utilisateur valide ou corrige ensuite ce mappage.
 */
final class PropertyCsvColumnMapper
{
    /**
     * Champs sans lesquels une ligne ne peut pas être importée.
     */
    public const array REQUIRED_FIELDS = ['agence_email', 'type_bien', 'type_transaction'];

    /**
     * Groupes d'affichage : clé => [libellé FR, libellé EN].
     */
    private const array GROUPS = [
        'general' => ['Général', 'General'],
        'price' => ['Prix & surface', 'Price & area'],
        'details' => ['Caractéristiques du bien', 'Property details'],
        'energy' => ['Énergie', 'Energy'],
        'location' => ['Localisation', 'Location'],
        'content' => ['Contenu & adresse', 'Content & address'],
        'media' => ['Équipements & médias', 'Features & media'],
        'technical' => ['Technique', 'Technical'],
    ];

    /**
     * Définition des champs non traduisibles : clé => [groupe, libellé FR,
     * libellé EN, synonymes FR/EN].
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: list<string>}>
     */
    private const array FIELDS = [
        'agence_email' => ['general', 'E-mail de l’agence', 'Agency email', ['email agence', 'mail agence', 'courriel', 'email', 'e mail', 'mail', 'agency', 'agence', 'agency mail', 'user email', 'utilisateur', 'user']],
        'type_bien' => ['general', 'Type de bien', 'Property type', ['type', 'type de logement', 'type logement', 'categorie', 'category', 'type bien', 'nature du bien', 'nature', 'kind']],
        'type_transaction' => ['general', 'Type de transaction', 'Transaction type', ['transaction', 'type de transaction', 'type d offre', 'offre', 'offer', 'offer type', 'mandat', 'vente location', 'sale rent', 'listing type']],
        'statut' => ['general', 'Statut', 'Status', ['status', 'etat', 'state', 'statut annonce', 'listing status']],
        'slug' => ['general', 'Slug (URL)', 'Slug (URL)', ['url', 'permalien', 'permalink']],
        'reference_interne' => ['general', 'Référence interne', 'Internal reference', ['reference', 'ref', 'ref interne', 'reference agence', 'agency reference', 'reference number', 'id annonce', 'listing id', 'mandat numero']],

        'prix' => ['price', 'Prix de vente', 'Sale price', ['prix', 'price', 'selling price', 'prix fai', 'prix net vendeur', 'montant', 'amount', 'asking price']],
        'montant_loyer_hors_charge' => ['price', 'Loyer hors charges', 'Rent (excl. charges)', ['loyer', 'loyer hc', 'loyer mensuel', 'rent', 'monthly rent', 'rent excluding charges', 'rent excl charges']],
        'montant_des_charges' => ['price', 'Charges', 'Service charges', ['charges', 'charges mensuelles', 'monthly charges', 'fees', 'service charge', 'provision sur charges']],
        'montant_depot_de_garantie' => ['price', 'Dépôt de garantie', 'Security deposit', ['depot', 'depot garantie', 'caution', 'deposit', 'security deposit', 'guarantee deposit']],
        'surface_total' => ['price', 'Surface totale (m²)', 'Total area (m²)', ['surface', 'superficie', 'surface habitable', 'surface m2', 'm2', 'area', 'living area', 'size', 'floor area', 'total area', 'sqm']],

        'chambres' => ['details', 'Chambres', 'Bedrooms', ['chambre', 'nb chambres', 'nombre de chambres', 'nombre chambres', 'bedroom', 'beds', 'bed', 'number of bedrooms']],
        'salle_de_bains' => ['details', 'Salles de bains', 'Bathrooms', ['salle de bain', 'salles de bain', 'salles de bains', 'sdb', 'nb salles de bain', 'bathroom', 'baths', 'bath', 'number of bathrooms']],
        'annee_construction' => ['details', 'Année de construction', 'Year built', ['annee', 'annee construction', 'date de construction', 'year', 'year built', 'construction year', 'built']],
        'show_adresse' => ['details', 'Afficher l’adresse', 'Show address', ['afficher adresse', 'adresse visible', 'show address', 'display address', 'address visible', 'public address']],

        'dpe' => ['energy', 'DPE (kWh/m²/an)', 'Energy consumption (kWh/m²/yr)', ['dpe valeur', 'consommation energie', 'consommation energetique', 'energy consumption', 'energy value', 'kwh', 'diagnostic energie']],
        'dpe_min' => ['energy', 'DPE minimum', 'Energy consumption min', ['dpe minimum', 'energy min', 'energy consumption min']],
        'dpe_max' => ['energy', 'DPE maximum', 'Energy consumption max', ['dpe maximum', 'energy max', 'energy consumption max']],
        'dpe_lettre' => ['energy', 'Classe énergie (DPE)', 'Energy class', ['classe energie', 'classe dpe', 'lettre dpe', 'dpe classe', 'dpe letter', 'energy class', 'energy rating', 'energy label', 'epc rating']],
        'ges' => ['energy', 'GES (kg CO₂/m²/an)', 'Greenhouse gas emissions', ['ges valeur', 'emissions ges', 'emission ges', 'greenhouse gas', 'ghg', 'co2', 'emissions', 'ges emissions']],
        'ges_lettre' => ['energy', 'Classe GES', 'GHG class', ['classe ges', 'lettre ges', 'ges classe', 'ges letter', 'ghg class', 'climate class', 'emission class']],
        'date_indexation_energie' => ['energy', 'Date du diagnostic énergie', 'Energy assessment date', ['date dpe', 'date diagnostic', 'date indexation', 'energy date', 'dpe date', 'energy assessment date']],

        'code_postal' => ['location', 'Code postal', 'Postal code', ['cp', 'code postal', 'postal code', 'postcode', 'post code', 'zip', 'zip code', 'zipcode']],
        'code_iso_pays' => ['location', 'Code pays (ISO)', 'Country code (ISO)', ['code pays', 'iso pays', 'country code', 'country iso', 'iso', 'iso code']],
        'latitude' => ['location', 'Latitude', 'Latitude', ['lat', 'gps lat', 'gps latitude']],
        'longitude' => ['location', 'Longitude', 'Longitude', ['lng', 'lon', 'long', 'gps lng', 'gps longitude']],

        'caracteristiques' => ['media', 'Caractéristiques (séparées par |)', 'Features (separated by |)', ['caracteristique', 'equipements', 'equipement', 'prestations', 'atouts', 'features', 'feature', 'amenities', 'characteristics', 'options']],
        'images' => ['media', 'Images (URL séparées par |)', 'Images (URLs separated by |)', ['image', 'photos', 'photo', 'pictures', 'picture', 'image url', 'images url', 'image urls', 'photo url', 'photos url', 'medias', 'media', 'gallery', 'galerie']],

        'mapbox_id' => ['technical', 'Identifiant Mapbox', 'Mapbox ID', ['mapbox']],
        'session_id_mapbox' => ['technical', 'Session Mapbox', 'Mapbox session ID', ['session mapbox', 'mapbox session']],
        'feature_type' => ['technical', 'Type de géocodage (Mapbox)', 'Feature type (Mapbox)', ['type geocodage', 'geocoding type']],
        'created_at' => ['technical', 'Date de création', 'Created at', ['date creation', 'date de creation', 'cree le', 'creation date', 'created', 'date publication', 'published at', 'date']],
        'updated_at' => ['technical', 'Date de mise à jour', 'Updated at', ['date mise a jour', 'date de modification', 'modifie le', 'mis a jour le', 'updated', 'last updated', 'modified', 'modified at', 'update date']],
    ];

    /**
     * Champs traduisibles : base => [libellé FR, libellé EN, synonymes FR/EN].
     * Les colonnes attendues sont suffixées par la langue (« titre_fr »).
     *
     * @var array<string, array{0: string, 1: string, 2: list<string>}>
     */
    private const array TRANSLATABLE_FIELDS = [
        'titre' => ['Titre', 'Title', ['title', 'titre annonce', 'titre du logement', 'intitule', 'libelle', 'nom', 'name', 'headline', 'listing title']],
        'description' => ['Description', 'Description', ['desc', 'descriptif', 'texte', 'texte annonce', 'text', 'details', 'body', 'listing description']],
        'adresse' => ['Adresse', 'Address', ['address', 'rue', 'street', 'adresse postale', 'street address', 'address line', 'address 1']],
        'ville' => ['Ville', 'City', ['city', 'commune', 'town', 'municipality']],
        'pays' => ['Pays', 'Country', ['country', 'nation']],
        'adresse_complete' => ['Adresse complète', 'Full address', ['adresse complete', 'full address', 'complete address', 'formatted address']],
        'region' => ['Région', 'Region', ['region', 'province', 'state']],
        'district' => ['District / département', 'District / county', ['departement', 'county', 'district']],
        'localite' => ['Localité', 'Locality', ['locality', 'localite', 'lieu dit']],
        'quartier' => ['Quartier', 'Neighbourhood', ['neighborhood', 'neighbourhood', 'secteur', 'district quartier']],
        'point_interet' => ['Point d’intérêt', 'Point of interest', ['poi', 'point of interest', 'point interet', 'landmark']],
    ];

    /**
     * Libellés des langues : code => [libellé FR, libellé EN, synonymes].
     */
    private const array LOCALE_LABELS = [
        'fr' => ['Français', 'French', ['fr', 'francais', 'french', 'fra']],
        'en' => ['Anglais', 'English', ['en', 'anglais', 'english', 'eng', 'gb', 'uk', 'us']],
        'es' => ['Espagnol', 'Spanish', ['es', 'espagnol', 'spanish', 'espanol']],
        'de' => ['Allemand', 'German', ['de', 'allemand', 'german', 'deutsch']],
        'it' => ['Italien', 'Italian', ['it', 'italien', 'italian', 'italiano']],
    ];

    /**
     * @param list<string> $locales
     */
    public function __construct(
        private readonly PropertyCsvImporter $importer,
        #[Autowire('%app.languages%')]
        private readonly array $locales = ['fr', 'en'],
    ) {
    }

    /**
     * Champs attendus, dans l'ordre du modèle CSV.
     *
     * @return array<string, array{key: string, group: string, label_fr: string, label_en: string, required: bool, locale: ?string}>
     */
    public function fields(): array
    {
        $fields = [];

        foreach ($this->importer->templateColumns() as $key) {
            $fields[$key] = $this->describe($key);
        }

        return $fields;
    }

    /**
     * Champs attendus regroupés pour l'affichage (listes déroulantes, aide).
     *
     * @return list<array{key: string, label_fr: string, label_en: string, fields: list<array{key: string, group: string, label_fr: string, label_en: string, required: bool, locale: ?string}>}>
     */
    public function groupedFields(): array
    {
        $groups = [];

        foreach (self::GROUPS as $key => [$labelFr, $labelEn]) {
            $groups[$key] = ['key' => $key, 'label_fr' => $labelFr, 'label_en' => $labelEn, 'fields' => []];
        }

        foreach ($this->fields() as $field) {
            $groups[$field['group']]['fields'][] = $field;
        }

        return array_values(array_filter($groups, static fn (array $group): bool => [] !== $group['fields']));
    }

    /**
     * Propose un champ attendu pour chaque en-tête du fichier (index de
     * colonne => clé de champ, ou null si aucun rapprochement). Un champ
     * n'est proposé qu'une seule fois : la première colonne qui correspond
     * le mieux l'emporte.
     *
     * @param list<string> $headers
     *
     * @return array<int, ?string>
     */
    public function suggest(array $headers): array
    {
        $index = $this->buildAliasIndex();
        $suggestions = array_fill_keys(array_keys($headers), null);
        $taken = [];

        // Passe 1 : correspondances exactes (clé du champ, libellé, synonyme
        // avec langue explicite). Passe 2 : colonnes sans langue pour les
        // champs traduisibles (« Titre » -> première langue encore libre).
        foreach ($headers as $position => $header) {
            $normalized = $this->normalize($header);

            if ('' === $normalized) {
                continue;
            }

            $candidate = $index['exact'][$normalized] ?? null;

            if (null === $candidate) {
                [$base, $locale] = $this->splitLocale($normalized);

                if (null !== $locale) {
                    $candidate = $index['translatable'][$base][$locale] ?? null;
                }
            }

            if (null !== $candidate && !isset($taken[$candidate])) {
                $suggestions[$position] = $candidate;
                $taken[$candidate] = true;
            }
        }

        foreach ($headers as $position => $header) {
            if (null !== $suggestions[$position]) {
                continue;
            }

            $normalized = $this->normalize($header);

            foreach ($index['translatable'][$normalized] ?? [] as $field) {
                if (!isset($taken[$field])) {
                    $suggestions[$position] = $field;
                    $taken[$field] = true;

                    break;
                }
            }
        }

        return $suggestions;
    }

    /**
     * Valide un mappage soumis (index de colonne => clé de champ ou chaîne
     * vide pour « ignorer »). Retourne le mappage nettoyé et la liste des
     * erreurs bloquantes.
     *
     * @param array<array-key, mixed> $submitted
     *
     * @return array{mapping: array<int, string>, errors: list<string>}
     */
    public function validate(array $submitted, int $columnCount): array
    {
        $fields = $this->fields();
        $mapping = [];
        $usedBy = [];
        $errors = [];

        foreach ($submitted as $position => $field) {
            if (!is_numeric($position) || !\is_string($field) || '' === $field) {
                continue;
            }

            $position = (int) $position;

            if ($position < 0 || $position >= $columnCount || !isset($fields[$field])) {
                continue;
            }

            if (isset($usedBy[$field])) {
                $errors[] = \sprintf(
                    'Le champ « %s / %s » est associé à plusieurs colonnes.',
                    $fields[$field]['label_fr'],
                    $fields[$field]['label_en'],
                );

                continue;
            }

            $usedBy[$field] = $position;
            $mapping[$position] = $field;
        }

        foreach (self::REQUIRED_FIELDS as $required) {
            if (!isset($usedBy[$required])) {
                $errors[] = \sprintf(
                    'Champ obligatoire non associé : « %s / %s ».',
                    $fields[$required]['label_fr'],
                    $fields[$required]['label_en'],
                );
            }
        }

        return ['mapping' => $mapping, 'errors' => array_values(array_unique($errors))];
    }

    /**
     * @return array{key: string, group: string, label_fr: string, label_en: string, required: bool, locale: ?string}
     */
    private function describe(string $key): array
    {
        $required = \in_array($key, self::REQUIRED_FIELDS, true);

        if (isset(self::FIELDS[$key])) {
            [$group, $labelFr, $labelEn] = self::FIELDS[$key];

            return ['key' => $key, 'group' => $group, 'label_fr' => $labelFr, 'label_en' => $labelEn, 'required' => $required, 'locale' => null];
        }

        foreach ($this->locales as $locale) {
            $base = mb_substr($key, 0, -mb_strlen('_'.$locale));

            if (str_ends_with($key, '_'.$locale) && isset(self::TRANSLATABLE_FIELDS[$base])) {
                [$labelFr, $labelEn] = self::TRANSLATABLE_FIELDS[$base];
                [$localeFr, $localeEn] = self::LOCALE_LABELS[$locale] ?? [mb_strtoupper($locale), mb_strtoupper($locale)];

                return [
                    'key' => $key,
                    'group' => 'content',
                    'label_fr' => \sprintf('%s (%s)', $labelFr, $localeFr),
                    'label_en' => \sprintf('%s (%s)', $labelEn, $localeEn),
                    'required' => $required,
                    'locale' => $locale,
                ];
            }
        }

        return ['key' => $key, 'group' => 'technical', 'label_fr' => $key, 'label_en' => $key, 'required' => $required, 'locale' => null];
    }

    /**
     * Index des synonymes normalisés :
     *  - exact : libellé normalisé => clé de champ ;
     *  - translatable : base normalisée => [langue => clé de champ] (ordre des
     *    langues du site conservé, pour l'attribution des colonnes sans langue).
     *
     * @return array{exact: array<string, string>, translatable: array<string, array<string, string>>}
     */
    private function buildAliasIndex(): array
    {
        $fields = $this->fields();
        $exact = [];
        $translatable = [];

        foreach ($fields as $key => $field) {
            if (null !== $field['locale']) {
                continue;
            }

            $aliases = [$key, $field['label_fr'], $field['label_en'], ...(self::FIELDS[$key][3] ?? [])];

            foreach ($aliases as $alias) {
                $exact[$this->normalize($alias)] ??= $key;
            }
        }

        foreach ($fields as $key => $field) {
            $locale = $field['locale'];

            if (null === $locale) {
                continue;
            }

            $base = mb_substr($key, 0, -mb_strlen('_'.$locale));
            [$labelFr, $labelEn, $aliases] = self::TRANSLATABLE_FIELDS[$base];

            $exact[$this->normalize($key)] ??= $key;

            foreach ([$base, $labelFr, $labelEn, ...$aliases] as $alias) {
                $translatable[$this->normalize($alias)][$locale] ??= $key;
            }
        }

        // Un synonyme traduisible ne doit pas masquer un champ simple
        // homonyme (« district », « region »…) : le champ simple prime.
        foreach (array_keys($translatable) as $alias) {
            if (isset($exact[$alias]) && null === $fields[$exact[$alias]]['locale']) {
                unset($translatable[$alias]);
            }
        }

        return ['exact' => $exact, 'translatable' => $translatable];
    }

    /**
     * Sépare un en-tête normalisé en [base, langue] lorsqu'il se termine (ou
     * commence) par un code ou un nom de langue : « title en », « titre
     * anglais », « fr description ».
     *
     * @return array{0: string, 1: ?string}
     */
    private function splitLocale(string $normalized): array
    {
        $tokens = explode(' ', $normalized);

        if (\count($tokens) < 2) {
            return [$normalized, null];
        }

        foreach ($this->locales as $locale) {
            $synonyms = self::LOCALE_LABELS[$locale][2] ?? [$locale];

            if (\in_array(end($tokens), $synonyms, true)) {
                return [implode(' ', \array_slice($tokens, 0, -1)), $locale];
            }

            if (\in_array($tokens[0], $synonyms, true)) {
                return [implode(' ', \array_slice($tokens, 1)), $locale];
            }
        }

        return [$normalized, null];
    }

    /**
     * Minuscules, sans accents, séparateurs unifiés ; les mots de liaison
     * courants sont conservés pour coller aux synonymes déclarés.
     */
    private function normalize(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = mb_trim(mb_strtolower($value));

        if ('' === $value) {
            return '';
        }

        // iconv//TRANSLIT est peu fiable selon la plateforme (« R'ef'erence »
        // sous Windows) : translittération ICU via symfony/string.
        $value = str_replace('²', '2', $value);
        $value = (new UnicodeString($value))->ascii()->lower()->toString();

        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return mb_trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }
}
