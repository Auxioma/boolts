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

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Les 18 plus grandes villes du pays du visiteur (pays déduit de l'adresse IP),
 * dans la langue de la page.
 *
 * Aucun appel extérieur à l'affichage : les villes sont lues dans config/footer_cities.json,
 * généré par la commande app:footer-cities:generate (OpenStreetMap).
 *
 * Format du fichier :
 *   { "DE": [ { "default": "München", "fr": "Munich", "en": "Munich" }, … ], … }
 */
final class FooterCitiesProvider
{
    public const int LIMIT = 18;

    /** Langues générées par défaut dans le fichier. */
    public const array LOCALES = ['fr', 'en'];

    private const string FILE = '/config/footer_cities.json';
    private const string DEFAULT_COUNTRY = 'FR';
    private const string FALLBACK_LOCALE = 'en';

    /** Liste d'origine du footer, utilisée si le fichier n'existe pas encore. */
    private const array FALLBACK_CITIES = [
        'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes',
        'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille', 'Rennes', 'Reims',
        'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Nîmes', 'Le Havre',
    ];

    /** @var array<string, list<array<string, string>>>|null */
    private ?array $data = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly GeoIpLocationService $geoIpLocationService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<string> noms des villes, dans la langue de la page
     */
    public function getCities(): array
    {
        $data = $this->data ??= $this->readFile();
        // Pays sans ville connue (ex. Vatican) ou absent du fichier → France.
        $cities = ($data[$this->resolveCountryCode()] ?? []) ?: ($data[self::DEFAULT_COUNTRY] ?? []);

        if ([] === $cities) {
            return self::FALLBACK_CITIES;
        }

        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? self::FALLBACK_LOCALE;

        // Langue de la page → anglais → nom local.
        return array_map(
            static fn (array $names): string => $names[$locale] ?? $names[self::FALLBACK_LOCALE] ?? $names['default'],
            $cities,
        );
    }

    public function getFilePath(): string
    {
        return $this->projectDir.self::FILE;
    }

    /**
     * @return array<string, list<array<string, string>>>
     */
    public function readFile(): array
    {
        $filePath = $this->getFilePath();

        if (!is_file($filePath)) {
            return [];
        }

        try {
            $data = json_decode((string) file_get_contents($filePath), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return \is_array($data) ? $data : [];
    }

    /**
     * Pays du visiteur : en-tête Cloudflare, sinon base MaxMind, sinon France.
     */
    private function resolveCountryCode(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return self::DEFAULT_COUNTRY;
        }

        $cloudflareCountry = mb_strtoupper(mb_trim((string) $request->headers->get('cf-ipcountry')));

        // Cloudflare renvoie « XX » (inconnu) ou « T1 » (Tor) quand il ne sait pas.
        if (1 === preg_match('/^[A-Z]{2}$/', $cloudflareCountry) && !\in_array($cloudflareCountry, ['XX', 'T1'], true)) {
            return $cloudflareCountry;
        }

        $ip = $request->headers->get('cf-connecting-ip') ?: $request->getClientIp();
        $countryCode = $this->geoIpLocationService->locateIp($ip)['countryCode'] ?? null;

        return \is_string($countryCode) && 1 === preg_match('/^[A-Z]{2}$/', $countryCode)
            ? $countryCode
            : self::DEFAULT_COUNTRY;
    }
}
