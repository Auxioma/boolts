<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/geo/autocomplete')]
/**
 * HTTP controller for module Public / GeoAutocompleteController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class GeoAutocompleteController extends AbstractController
{
    private const GEONAMES_BASE_URL = 'https://secure.geonames.org';

    private const MIN_QUERY_LENGTH = 2;

    private const MAX_RESULTS = 10;
    private const MAX_EXTERNAL_RESULTS = 25;

    private const CACHE_TTL_SECONDS = 86400;
    private const EMPTY_CACHE_TTL_SECONDS = 20;

    private const HTTP_TIMEOUT_SECONDS = 6.0;

    private const MAX_DISTRICT_DISTANCE_KM = 80.0;

    /**
     * Handles the __construct controller action.
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    #[Route('/pays', name: 'app_geo_country_autocomplete', methods: ['GET'])]
    /**
     * Handles the countries controller action.
     */
    public function countries(Request $request): JsonResponse
    {
        $q = mb_trim((string) $request->query->get('q', ''));

        if (mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return $this->jsonResults([]);
        }

        $results = $this->cacheResult('countries', [
            'q' => $q,
            'locale' => $request->getLocale(),
        ], function () use ($q, $request): array {
            return $this->searchCountries($q, $request->getLocale());
        });

        return $this->jsonResults($results);
    }

    #[Route('/villes', name: 'app_geo_city_autocomplete', methods: ['GET'])]
    /**
     * Handles the cities controller action.
     */
    public function cities(Request $request): JsonResponse
    {
        $q = mb_trim((string) $request->query->get('q', ''));
        $countryCode = mb_strtoupper(mb_trim((string) $request->query->get('country_code', '')));
        $countryName = mb_trim((string) $request->query->get('country_name', ''));

        if ('' === $countryCode || mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return $this->jsonResults([]);
        }

        $results = $this->cacheResult('cities', [
            'q' => $q,
            'country_code' => $countryCode,
            'country_name' => $countryName,
        ], function () use ($q, $countryCode, $countryName): array {
            return $this->searchCitiesWorldwide($q, $countryCode, $countryName);
        });

        return $this->jsonResults($results);
    }

    #[Route('/quartiers', name: 'app_geo_district_autocomplete', methods: ['GET'])]
    /**
     * Handles the districts controller action.
     */
    public function districts(Request $request): JsonResponse
    {
        $q = mb_trim((string) $request->query->get('q', ''));
        $cityName = mb_trim((string) $request->query->get('city_name', ''));
        $countryCode = mb_strtoupper(mb_trim((string) $request->query->get('country_code', '')));
        $countryName = mb_trim((string) $request->query->get('country_name', ''));

        $cityLat = $this->nullableFloat($request->query->get('city_lat'));
        $cityLng = $this->nullableFloat(
            $request->query->get('city_lng', $request->query->get('city_lon'))
        );

        $adminCode1 = mb_trim((string) $request->query->get('admin_code_1', ''));
        $adminCode2 = mb_trim((string) $request->query->get('admin_code_2', ''));
        $adminCode3 = mb_trim((string) $request->query->get('admin_code_3', ''));

        if ('' === $countryCode || '' === $cityName || mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return $this->jsonResults([]);
        }

        $results = $this->cacheResult('districts', [
            'q' => $q,
            'city_name' => $cityName,
            'country_code' => $countryCode,
            'country_name' => $countryName,
            'city_lat' => $cityLat,
            'city_lng' => $cityLng,
            'admin_code_1' => $adminCode1,
            'admin_code_2' => $adminCode2,
            'admin_code_3' => $adminCode3,
        ], function () use (
            $q,
            $cityName,
            $countryCode,
            $countryName,
            $cityLat,
            $cityLng,
            $adminCode1,
            $adminCode2,
            $adminCode3
        ): array {
            return $this->searchDistrictsWorldwide(
                $q,
                $cityName,
                $countryCode,
                $countryName,
                $cityLat,
                $cityLng,
                $adminCode1,
                $adminCode2,
                $adminCode3
            );
        });

        return $this->jsonResults($results);
    }

    #[Route('/debug-live', name: 'app_geo_autocomplete_debug_live', methods: ['GET'])]
    /**
     * Handles the debugLive controller action.
     */
    public function debugLive(Request $request): JsonResponse
    {
        $q = mb_trim((string) $request->query->get('q', 'rou'));
        $cityName = mb_trim((string) $request->query->get('city_name', 'Rouen'));
        $countryCode = mb_strtoupper(mb_trim((string) $request->query->get('country_code', 'FR')));

        $cityParams = [
            'name_startsWith' => $q,
            'country' => $countryCode,
            'featureClass' => 'P',
            'maxRows' => self::MAX_EXTERNAL_RESULTS,
            'orderby' => 'relevance',
            'style' => 'FULL',
            'lang' => 'fr',
            'isNameRequired' => 'true',
            'username' => $this->geonamesUsername(),
        ];

        $districtParams = [
            'name_startsWith' => $q,
            'country' => $countryCode,
            'featureClass' => 'P',
            'maxRows' => self::MAX_EXTERNAL_RESULTS,
            'orderby' => 'relevance',
            'style' => 'FULL',
            'lang' => 'fr',
            'isNameRequired' => 'true',
            'username' => $this->geonamesUsername(),
        ];

        return $this->json([
            'env' => [
                'geonames_username_present' => '' !== $this->geonamesUsername(),
                'geonames_username' => '' !== $this->geonamesUsername() ? 'OK' : 'VIDE',
                'app_url' => $this->appReferer(),
            ],
            'city_test' => [
                'url' => self::GEONAMES_BASE_URL.'/searchJSON?'.http_build_query($cityParams),
                'raw' => $this->geonamesRaw('/searchJSON', $cityParams),
                'parsed_results' => $this->searchCitiesWorldwide($q, $countryCode, ''),
            ],
            'district_test' => [
                'city_name' => $cityName,
                'url' => self::GEONAMES_BASE_URL.'/searchJSON?'.http_build_query($districtParams),
                'raw' => $this->geonamesRaw('/searchJSON', $districtParams),
                'parsed_results' => $this->searchDistrictsWorldwide(
                    $q,
                    $cityName,
                    $countryCode,
                    '',
                    null,
                    null,
                    '',
                    '',
                    ''
                ),
            ],
        ]);
    }

    private function jsonResults(array $results): JsonResponse
    {
        $response = new JsonResponse([
            'results' => \array_slice($results, 0, self::MAX_RESULTS),
        ]);

        $response->setPublic();
        $response->setMaxAge(120);
        $response->setSharedMaxAge(120);

        return $response;
    }

    private function cacheResult(string $prefix, array $params, callable $callback): array
    {
        ksort($params);

        $encodedParams = json_encode($params);

        if (false === $encodedParams) {
            $encodedParams = serialize($params);
        }

        $cacheKey = 'geo_autocomplete_'.$prefix.'_'.hash('sha256', $encodedParams);

        return $this->cache->get($cacheKey, static function (ItemInterface $item) use ($callback): array {
            $item->expiresAfter(self::CACHE_TTL_SECONDS);

            $results = $callback();

            if (!\is_array($results) || [] === $results) {
                $item->expiresAfter(self::EMPTY_CACHE_TTL_SECONDS);

                return [];
            }

            return \array_slice($results, 0, self::MAX_RESULTS);
        });
    }

    /**
     * ==========================================================================
     * PAYS
     * ==========================================================================.
     */
    private function searchCountries(string $q, string $locale = 'fr'): array
    {
        $normalizedQuery = $this->normalizeSearchText($q);

        $locales = array_values(array_unique([
            $locale ?: 'fr',
            'fr',
            'en',
            'es',
            'de',
            'it',
            'pt',
            'ru',
            'ar',
        ]));

        $countries = [];

        foreach ($locales as $currentLocale) {
            try {
                foreach (Countries::getNames($currentLocale) as $code => $name) {
                    $code = mb_strtoupper((string) $code);

                    if (!isset($countries[$code])) {
                        $countries[$code] = [
                            'code' => $code,
                            'names' => [],
                        ];
                    }

                    $countries[$code]['names'][] = $name;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        $results = [];

        foreach ($countries as $code => $country) {
            $names = array_values(array_unique($country['names']));

            $matched = str_starts_with(
                $this->normalizeSearchText($code),
                $normalizedQuery
            );

            foreach ($names as $name) {
                $normalizedName = $this->normalizeSearchText($name);

                if (
                    str_starts_with($normalizedName, $normalizedQuery)
                    || str_contains($normalizedName, $normalizedQuery)
                ) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            $label = $names[0] ?? $code;

            $results[] = [
                'source' => 'intl',
                'sources' => ['intl'],
                'code' => $code,
                'country_code' => $code,
                'country_name' => $label,
                'label' => $label,
                'display_name' => $label.' — '.$code,
            ];
        }

        usort($results, function (array $a, array $b) use ($normalizedQuery): int {
            $aLabel = $this->normalizeSearchText((string) $a['label']);
            $bLabel = $this->normalizeSearchText((string) $b['label']);

            $aStarts = str_starts_with($aLabel, $normalizedQuery);
            $bStarts = str_starts_with($bLabel, $normalizedQuery);

            if ($aStarts !== $bStarts) {
                return $aStarts ? -1 : 1;
            }

            return strcasecmp((string) $a['label'], (string) $b['label']);
        });

        return \array_slice($results, 0, self::MAX_RESULTS);
    }

    /**
     * ==========================================================================
     * VILLES MONDE
     * ==========================================================================.
     */
    private function searchCitiesWorldwide(string $q, string $countryCode, string $countryName = ''): array
    {
        $items = [];

        $startsParams = [
            'name_startsWith' => $q,
            'country' => $countryCode,
            'featureClass' => 'P',
            'maxRows' => self::MAX_EXTERNAL_RESULTS,
            'orderby' => 'relevance',
            'style' => 'FULL',
            'lang' => 'fr',
            'isNameRequired' => 'true',
            'username' => $this->geonamesUsername(),
        ];

        foreach ($this->geonamesSearch($startsParams) as $item) {
            $items[] = $item;
        }

        if ([] === $items && mb_strlen($q) >= 3) {
            $fallbackParams = [
                'q' => $q,
                'country' => $countryCode,
                'featureClass' => 'P',
                'maxRows' => self::MAX_EXTERNAL_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'username' => $this->geonamesUsername(),
            ];

            foreach ($this->geonamesSearch($fallbackParams) as $item) {
                $items[] = $item;
            }
        }

        $results = [];

        foreach ($items as $item) {
            $cityName = mb_trim((string) ($item['name'] ?? $item['toponymName'] ?? ''));

            if ('' === $cityName) {
                continue;
            }

            if (($item['fcl'] ?? '') !== 'P') {
                continue;
            }

            $resultCountryCode = mb_strtoupper((string) ($item['countryCode'] ?? ''));

            if ($resultCountryCode !== mb_strtoupper($countryCode)) {
                continue;
            }

            if (!$this->textMatches($cityName, $q)) {
                continue;
            }

            $cleanCityName = $this->cleanCityName($cityName, $countryCode);

            $results[] = [
                'source' => 'geonames',
                'sources' => ['geonames'],
                'postal_code' => null,
                'city_name' => $cleanCityName,
                'country_code' => $resultCountryCode,
                'country_name' => $item['countryName'] ?? ($countryName ?: $this->countryNameFromCode($countryCode)),
                'admin_name_1' => $item['adminName1'] ?? null,
                'admin_code_1' => $item['adminCode1'] ?? null,
                'admin_name_2' => $item['adminName2'] ?? null,
                'admin_code_2' => $item['adminCode2'] ?? null,
                'admin_name_3' => $item['adminName3'] ?? null,
                'admin_code_3' => $item['adminCode3'] ?? null,
                'geoname_id' => $item['geonameId'] ?? null,
                'feature_class' => $item['fcl'] ?? null,
                'feature_code' => $item['fcode'] ?? null,
                'lat' => $item['lat'] ?? null,
                'lng' => $item['lng'] ?? null,
                'lon' => $item['lng'] ?? null,
                'population' => (int) ($item['population'] ?? 0),
                'display_name' => $this->buildDisplayName([
                    $cleanCityName,
                    $item['adminName2'] ?? null,
                    $item['adminName1'] ?? null,
                    $item['countryName'] ?? ($countryName ?: $this->countryNameFromCode($countryCode)),
                ]),
            ];
        }

        $results = $this->dedupe($results, static function (array $item): string {
            return implode('|', [
                $item['city_name'] ?? '',
                $item['admin_code_1'] ?? '',
                $item['admin_code_2'] ?? '',
                $item['country_code'] ?? '',
            ]);
        });

        usort($results, static function (array $a, array $b): int {
            $populationA = (int) ($a['population'] ?? 0);
            $populationB = (int) ($b['population'] ?? 0);

            if ($populationA !== $populationB) {
                return $populationB <=> $populationA;
            }

            return strcasecmp((string) ($a['city_name'] ?? ''), (string) ($b['city_name'] ?? ''));
        });

        return \array_slice($results, 0, self::MAX_RESULTS);
    }

    /**
     * ==========================================================================
     * QUARTIERS MONDE
     * ==========================================================================.
     */
    private function searchDistrictsWorldwide(
        string $q,
        string $cityName,
        string $countryCode,
        string $countryName,
        ?float $cityLat,
        ?float $cityLng,
        string $adminCode1,
        string $adminCode2,
        string $adminCode3,
    ): array {
        $items = [];

        $startsParams = [
            'name_startsWith' => $q,
            'country' => $countryCode,
            'featureClass' => 'P',
            'maxRows' => self::MAX_EXTERNAL_RESULTS,
            'orderby' => 'relevance',
            'style' => 'FULL',
            'lang' => 'fr',
            'isNameRequired' => 'true',
            'username' => $this->geonamesUsername(),
        ];

        if ('' !== $adminCode1) {
            $startsParams['adminCode1'] = $adminCode1;
        }

        if ('' !== $adminCode2) {
            $startsParams['adminCode2'] = $adminCode2;
        }

        if ('' !== $adminCode3) {
            $startsParams['adminCode3'] = $adminCode3;
        }

        foreach ($this->geonamesSearch($startsParams) as $item) {
            $items[] = $item;
        }

        if ([] === $items && mb_strlen($q) >= 3) {
            $fallbackParams = [
                'q' => $q.' '.$cityName,
                'country' => $countryCode,
                'featureClass' => 'P',
                'maxRows' => self::MAX_EXTERNAL_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'username' => $this->geonamesUsername(),
            ];

            foreach ($this->geonamesSearch($fallbackParams) as $item) {
                $items[] = $item;
            }
        }

        $results = [];

        foreach ($items as $item) {
            $name = mb_trim((string) ($item['name'] ?? $item['toponymName'] ?? ''));

            if ('' === $name) {
                continue;
            }

            if (($item['fcl'] ?? '') !== 'P') {
                continue;
            }

            $resultCountryCode = mb_strtoupper((string) ($item['countryCode'] ?? ''));

            if ($resultCountryCode !== mb_strtoupper($countryCode)) {
                continue;
            }

            if ($this->samePlace($name, $cityName)) {
                continue;
            }

            if (!$this->textMatches($name, $q)) {
                continue;
            }

            $featureCode = (string) ($item['fcode'] ?? '');

            if (!$this->isAllowedDistrictFeatureCode($featureCode)) {
                continue;
            }

            $lat = $this->nullableFloat($item['lat'] ?? null);
            $lng = $this->nullableFloat($item['lng'] ?? null);

            $distanceKm = null;

            if (null !== $cityLat && null !== $cityLng && null !== $lat && null !== $lng) {
                $distanceKm = $this->haversineDistanceKm($cityLat, $cityLng, $lat, $lng);

                if ($distanceKm > self::MAX_DISTRICT_DISTANCE_KM) {
                    continue;
                }
            }

            $districtName = $this->cleanDistrictName($name);

            $results[] = [
                'source' => 'geonames',
                'sources' => ['geonames'],
                'name' => $districtName,
                'district_name' => $districtName,
                'city_name' => $cityName,
                'country_code' => $resultCountryCode,
                'country_name' => $item['countryName'] ?? ($countryName ?: $this->countryNameFromCode($countryCode)),
                'admin_name_1' => $item['adminName1'] ?? null,
                'admin_code_1' => $item['adminCode1'] ?? null,
                'admin_name_2' => $item['adminName2'] ?? null,
                'admin_code_2' => $item['adminCode2'] ?? null,
                'admin_name_3' => $item['adminName3'] ?? null,
                'admin_code_3' => $item['adminCode3'] ?? null,
                'geoname_id' => $item['geonameId'] ?? null,
                'feature_class' => $item['fcl'] ?? null,
                'feature_code' => $featureCode,
                'lat' => null !== $lat ? (string) $lat : null,
                'lng' => null !== $lng ? (string) $lng : null,
                'lon' => null !== $lng ? (string) $lng : null,
                'distance_km' => $distanceKm,
                'exact_match' => $this->normalizeSearchText($name) === $this->normalizeSearchText($q),
                'display_name' => $this->buildDisplayName([
                    $districtName,
                    $cityName,
                    $item['adminName2'] ?? null,
                    $item['adminName1'] ?? null,
                    $item['countryName'] ?? ($countryName ?: $this->countryNameFromCode($countryCode)),
                ]),
            ];
        }

        $results = $this->dedupe($results, static function (array $item): string {
            return implode('|', [
                $item['name'] ?? '',
                $item['city_name'] ?? '',
                $item['admin_code_1'] ?? '',
                $item['admin_code_2'] ?? '',
                $item['country_code'] ?? '',
            ]);
        });

        usort($results, static function (array $a, array $b): int {
            $exactA = (int) ($a['exact_match'] ?? false);
            $exactB = (int) ($b['exact_match'] ?? false);

            if ($exactA !== $exactB) {
                return $exactB <=> $exactA;
            }

            $distanceA = $a['distance_km'] ?? 999999;
            $distanceB = $b['distance_km'] ?? 999999;

            if ($distanceA !== $distanceB) {
                return $distanceA <=> $distanceB;
            }

            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return \array_slice($results, 0, self::MAX_RESULTS);
    }

    /**
     * ==========================================================================
     * GEONAMES
     * ==========================================================================.
     */
    private function geonamesSearch(array $params): array
    {
        if ('' === $this->geonamesUsername()) {
            return [];
        }

        $data = $this->geonamesJson('/searchJSON', $params);

        if (isset($data['status'])) {
            return [];
        }

        $items = $data['geonames'] ?? [];

        return \is_array($items) ? $items : [];
    }

    private function geonamesJson(string $endpoint, array $params): array
    {
        $url = self::GEONAMES_BASE_URL.$endpoint.'?'.http_build_query($params);
        $cacheKey = 'geonames_http_'.hash('sha256', $url);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($url): array {
            $item->expiresAfter(self::CACHE_TTL_SECONDS);

            try {
                $response = $this->httpClient->request('GET', $url, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'User-Agent' => 'TrouveMoiGeoAutocomplete/1.0',
                        'Referer' => $this->appReferer(),
                    ],
                    'timeout' => self::HTTP_TIMEOUT_SECONDS,
                    'max_duration' => self::HTTP_TIMEOUT_SECONDS,
                ]);

                $statusCode = $response->getStatusCode();

                if ($statusCode < 200 || $statusCode >= 300) {
                    $item->expiresAfter(self::EMPTY_CACHE_TTL_SECONDS);

                    return [];
                }

                $data = $response->toArray(false);

                if (!\is_array($data)) {
                    $item->expiresAfter(self::EMPTY_CACHE_TTL_SECONDS);

                    return [];
                }

                if ([] === $data || isset($data['status'])) {
                    $item->expiresAfter(self::EMPTY_CACHE_TTL_SECONDS);
                }

                return $data;
            } catch (\Throwable) {
                $item->expiresAfter(self::EMPTY_CACHE_TTL_SECONDS);

                return [];
            }
        });
    }

    private function geonamesRaw(string $endpoint, array $params): array
    {
        $url = self::GEONAMES_BASE_URL.$endpoint.'?'.http_build_query($params);

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'TrouveMoiGeoAutocomplete/1.0',
                    'Referer' => $this->appReferer(),
                ],
                'timeout' => self::HTTP_TIMEOUT_SECONDS,
                'max_duration' => self::HTTP_TIMEOUT_SECONDS,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            return [
                'http_status' => $statusCode,
                'json' => json_decode($content, true),
                'raw' => $content,
            ];
        } catch (\Throwable $exception) {
            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * ==========================================================================
     * FILTRES
     * ==========================================================================.
     */
    private function isAllowedDistrictFeatureCode(string $featureCode): bool
    {
        if ('' === $featureCode) {
            return true;
        }

        return \in_array($featureCode, [
            'PPLX',
            'PPLL',
            'PPL',
            'PPLA',
            'PPLA2',
            'PPLA3',
            'PPLA4',
            'PPLC',
            'PPLF',
            'PPLG',
            'PPLH',
            'PPLQ',
            'PPLR',
            'PPLS',
            'PPLW',
        ], true);
    }

    private function textMatches(string $name, string $q): bool
    {
        $name = $this->normalizeSearchText($name);
        $q = $this->normalizeSearchText($q);

        if ('' === $name || '' === $q) {
            return true;
        }

        return str_starts_with($name, $q) || str_contains($name, $q);
    }

    private function samePlace(string $a, string $b): bool
    {
        $a = $this->normalizeSearchText($a);
        $b = $this->normalizeSearchText($b);

        if ('' === $a || '' === $b) {
            return false;
        }

        return $a === $b;
    }

    private function cleanCityName(string $name, string $countryCode): string
    {
        $name = mb_trim($name);

        if ('FR' === mb_strtoupper($countryCode)) {
            $normalized = $this->normalizeSearchText($name);

            if (str_contains($normalized, 'paris')) {
                return 'Paris';
            }

            if (str_contains($normalized, 'marseille')) {
                return 'Marseille';
            }

            if (str_contains($normalized, 'lyon')) {
                return 'Lyon';
            }
        }

        return $name;
    }

    private function cleanDistrictName(string $name): string
    {
        $name = mb_trim($name);

        $patterns = [
            '/^Quartier\s+des\s+/iu',
            '/^Quartier\s+de la\s+/iu',
            '/^Quartier\s+du\s+/iu',
            '/^Quartier\s+de\s+/iu',
            '/^Quartier\s+/iu',
            '/^Neighborhood\s+/iu',
            '/^Neighbourhood\s+/iu',
            '/^Quarter\s+/iu',
            '/^District\s+/iu',
            '/^Section\s+/iu',
        ];

        foreach ($patterns as $pattern) {
            $name = preg_replace($pattern, '', $name) ?? $name;
        }

        return mb_trim($name);
    }

    private function dedupe(array $items, callable $keyCallback): array
    {
        $map = [];

        foreach ($items as $item) {
            $key = $this->normalizeSearchText((string) $keyCallback($item));

            if ('' === $key) {
                continue;
            }

            if (!isset($map[$key])) {
                $map[$key] = $item;
                continue;
            }

            $existing = $map[$key];

            foreach ($item as $field => $value) {
                if ($this->isEmptyValue($existing[$field] ?? null) && !$this->isEmptyValue($value)) {
                    $existing[$field] = $value;
                }
            }

            $existing['sources'] = array_values(array_unique(array_merge(
                $existing['sources'] ?? [],
                $item['sources'] ?? []
            )));

            $map[$key] = $existing;
        }

        return array_values($map);
    }

    /**
     * ==========================================================================
     * HELPERS GÉNÉRAUX
     * ==========================================================================.
     */
    private function buildDisplayName(array $parts): string
    {
        $parts = array_filter($parts, static function ($value): bool {
            return null !== $value && '' !== mb_trim((string) $value);
        });

        return implode(' — ', array_unique(array_map('strval', $parts)));
    }

    private function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(mb_trim($value));

        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D) ?: $value;
        }

        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', $value) ?? $value;
        $value = str_replace(['’', "'", '-', '_', '.', ',', ';', ':', '(', ')', '[', ']'], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return mb_trim($value);
    }

    private function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    private function nullableFloat(mixed $value): ?float
    {
        if (null === $value || '' === $value || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function isEmptyValue(mixed $value): bool
    {
        return null === $value || '' === $value || [] === $value;
    }

    private function countryNameFromCode(string $countryCode): string
    {
        try {
            return Countries::getName(mb_strtoupper($countryCode), 'fr');
        } catch (\Throwable) {
            return mb_strtoupper($countryCode);
        }
    }

    private function geonamesUsername(): string
    {
        return mb_trim((string) (
            $_ENV['GEONAMES_USERNAME']
            ?? $_SERVER['GEONAMES_USERNAME']
            ?? getenv('GEONAMES_USERNAME')
            ?: ''
        ));
    }

    private function appReferer(): string
    {
        return mb_trim((string) (
            $_ENV['APP_URL']
            ?? $_SERVER['APP_URL']
            ?? getenv('APP_URL')
            ?: 'https://127.0.0.1:8000'
        ));
    }
}
