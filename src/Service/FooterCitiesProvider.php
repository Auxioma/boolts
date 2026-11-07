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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Les 18 plus grandes villes du pays du visiteur (pays déduit de l'adresse IP),
 * récupérées depuis OpenStreetMap (API Overpass) et traduites selon la locale.
 *
 * Résultat mis en cache par pays : OpenStreetMap n'est appelé qu'une fois par pays et par mois.
 */
final class FooterCitiesProvider
{
    public const int LIMIT = 18;

    private const string OVERPASS_URL = 'https://overpass-api.de/api/interpreter';
    private const string DEFAULT_COUNTRY = 'FR';
    private const int CACHE_TTL = 30 * 24 * 3600;
    private const int CACHE_TTL_ON_ERROR = 3600;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly GeoIpLocationService $geoIpLocationService,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return list<string> noms des villes, dans la langue de la locale courante
     */
    public function getCities(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request?->getLocale() ?? 'fr';

        $countryCode = $this->resolveCountryCode();
        $cities = $this->getCitiesForCountry($countryCode);

        if ([] === $cities && self::DEFAULT_COUNTRY !== $countryCode) {
            $cities = $this->getCitiesForCountry(self::DEFAULT_COUNTRY);
        }

        return array_map(
            static fn (array $names): string => $names[$locale] ?? $names['default'],
            $cities,
        );
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

    /**
     * @return list<array<string, string>> pour chaque ville : ['default' => nom, 'fr' => ..., 'en' => ...]
     */
    private function getCitiesForCountry(string $countryCode): array
    {
        return $this->cache->get(
            'footer_cities_osm_'.$countryCode,
            function (ItemInterface $item) use ($countryCode): array {
                $item->expiresAfter(self::CACHE_TTL);

                try {
                    return $this->fetchFromOverpass($countryCode);
                } catch (\Throwable $exception) {
                    $this->logger->warning('Footer : impossible de récupérer les villes OpenStreetMap.', [
                        'country' => $countryCode,
                        'exception' => $exception,
                    ]);

                    // On réessaiera plus tard plutôt que de rappeler OSM à chaque page.
                    $item->expiresAfter(self::CACHE_TTL_ON_ERROR);

                    return [];
                }
            },
        );
    }

    /**
     * @return list<array<string, string>>
     */
    private function fetchFromOverpass(string $countryCode): array
    {
        $query = <<<OVERPASS
            [out:json][timeout:25];
            area["ISO3166-1"="{$countryCode}"]["admin_level"="2"]->.country;
            node["place"="city"]["population"](area.country);
            out tags;
            OVERPASS;

        $data = $this->httpClient->request('POST', self::OVERPASS_URL, [
            'body' => ['data' => $query],
            'headers' => ['User-Agent' => 'Boolts/1.0 (+https://boolts.com)'],
            'timeout' => 30,
        ])->toArray();

        $cities = [];

        foreach ($data['elements'] ?? [] as $element) {
            $tags = $element['tags'] ?? [];
            $name = $tags['name'] ?? null;
            $population = (int) preg_replace('/\D/', '', (string) ($tags['population'] ?? ''));

            if (!\is_string($name) || '' === $name || $population <= 0) {
                continue;
            }

            $names = ['default' => $name];

            foreach ($tags as $key => $value) {
                if (str_starts_with($key, 'name:') && \is_string($value) && '' !== $value) {
                    $names[mb_substr($key, 5)] = $value;
                }
            }

            // Une même ville peut apparaître plusieurs fois : on garde la plus peuplée.
            if (!isset($cities[$name]) || $cities[$name]['population'] < $population) {
                $cities[$name] = ['population' => $population, 'names' => $names];
            }
        }

        usort($cities, static fn (array $a, array $b): int => $b['population'] <=> $a['population']);

        return array_map(
            static fn (array $city): array => $city['names'],
            \array_slice($cities, 0, self::LIMIT),
        );
    }
}
