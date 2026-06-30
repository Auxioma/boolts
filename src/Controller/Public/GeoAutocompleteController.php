<?php

declare(strict_types=1);

namespace App\Controller\Public;

use Normalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[Route('/geo/autocomplete')]
final class GeoAutocompleteController extends AbstractController
{
    private const GEONAMES_BASE_URL = 'https://secure.geonames.org';
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    private const CACHE_TTL_SECONDS = 604800; // 7 jours
    private const MAX_RESULTS = 8;
    private const MAX_DISTANCE_KM_FOR_NEIGHBOURHOOD = 70.0;

    private const HTTP_TIMEOUT_SECONDS = 8;
    private const HTTP_CONNECT_TIMEOUT_SECONDS = 3;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    #[Route('/pays', name: 'app_geo_country_autocomplete', methods: ['GET'])]
    public function countries(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));

        if (mb_strlen($q) < 1) {
            return $this->json([
                'results' => [],
            ]);
        }

        return $this->json([
            'results' => $this->searchCountries($q, $request->getLocale()),
        ]);
    }

    #[Route('/villes', name: 'app_geo_city_autocomplete', methods: ['GET'])]
    public function cities(Request $request): JsonResponse
    {
        $countryCode = strtoupper(trim((string) $request->query->get('country_code', '')));
        $q = trim((string) $request->query->get('q', ''));

        if ($countryCode === '' || mb_strlen($q) < 2) {
            return $this->json([
                'results' => [],
            ]);
        }

        return $this->json([
            'results' => $this->searchCityCombined($countryCode, $q),
        ]);
    }

    #[Route('/quartiers', name: 'app_geo_district_autocomplete', methods: ['GET'])]
    public function districts(Request $request): JsonResponse
    {
        $countryCode = strtoupper(trim((string) $request->query->get('country_code', '')));
        $cityName = trim((string) $request->query->get('city_name', ''));
        $q = trim((string) $request->query->get('q', ''));

        $cityLat = $this->nullableFloat($request->query->get('city_lat'));
        $cityLng = $this->nullableFloat(
            $request->query->get('city_lng', $request->query->get('city_lon'))
        );

        $adminCode1 = trim((string) $request->query->get('admin_code_1', ''));
        $adminCode2 = trim((string) $request->query->get('admin_code_2', ''));
        $adminCode3 = trim((string) $request->query->get('admin_code_3', ''));

        if ($countryCode === '' || $cityName === '' || mb_strlen($q) < 2) {
            return $this->json([
                'results' => [],
            ]);
        }

        return $this->json([
            'results' => $this->searchNeighbourhoodCombined(
                $countryCode,
                $cityName,
                $q,
                $cityLat,
                $cityLng,
                $adminCode1,
                $adminCode2,
                $adminCode3
            ),
        ]);
    }

    /**
     * ==========================================================================
     * PAYS
     * ==========================================================================
     */
    private function searchCountries(string $q, string $locale = 'fr'): array
    {
        $normalizedQuery = $this->normalizeSearchText($q);

        $locales = array_values(array_unique([
            $locale ?: 'fr',
            'fr',
            'en',
            'de',
            'es',
            'it',
            'pt',
            'ru',
            'ar',
        ]));

        $countries = [];

        foreach ($locales as $currentLocale) {
            try {
                foreach (Countries::getNames($currentLocale) as $code => $name) {
                    $code = strtoupper((string) $code);

                    if (!isset($countries[$code])) {
                        $countries[$code] = [
                            'code' => $code,
                            'names' => [],
                        ];
                    }

                    $countries[$code]['names'][] = $name;
                }
            } catch (Throwable) {
                continue;
            }
        }

        $results = [];

        foreach ($countries as $code => $country) {
            $names = array_values(array_unique($country['names']));

            $matched = str_contains(strtolower($code), strtolower($q));

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
                'display_name' => $label . ' — ' . $code,
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

        return array_slice($results, 0, self::MAX_RESULTS);
    }

    /**
     * ==========================================================================
     * VILLE
     * ==========================================================================
     */
    private function searchCityCombined(string $countryCode, string $q): array
    {
        $results = [];

        $geonamesResults = $this->safeApiCall(
            fn () => $this->geonamesCity($countryCode, $q)
        );

        foreach ($geonamesResults as $item) {
            $results[] = $item;
        }

        if (count($geonamesResults) < 1) {
            foreach ($this->safeApiCall(fn () => $this->nominatimCity($countryCode, $q)) as $item) {
                $results[] = $item;
            }
        }

        $results = $this->mergeResultsByComposite($results, [
            'city_name',
            'admin_name_1',
            'country_code',
        ]);

        $results = $this->normalizeCitySearchDuplicates($results, $q, $countryCode);

        usort($results, static function (array $a, array $b): int {
            $populationA = (int) ($a['population'] ?? 0);
            $populationB = (int) ($b['population'] ?? 0);

            if ($populationA !== $populationB) {
                return $populationB <=> $populationA;
            }

            $sourceCountA = count($a['sources'] ?? []);
            $sourceCountB = count($b['sources'] ?? []);

            if ($sourceCountA !== $sourceCountB) {
                return $sourceCountB <=> $sourceCountA;
            }

            return strcasecmp((string) ($a['city_name'] ?? ''), (string) ($b['city_name'] ?? ''));
        });

        return array_slice($results, 0, self::MAX_RESULTS);
    }

    private function geonamesCity(string $countryCode, string $q): array
    {
        if ($this->geonamesUsername() === '') {
            return [];
        }

        $params = [
            'name_startsWith' => $q,
            'country' => $countryCode,
            'featureClass' => 'P',
            'maxRows' => self::MAX_RESULTS,
            'orderby' => 'relevance',
            'style' => 'FULL',
            'lang' => 'fr',
            'isNameRequired' => 'true',
            'username' => $this->geonamesUsername(),
        ];

        $data = $this->geonamesGetJson('/searchJSON', $params, 'geonames_city_starts');
        $items = $data['geonames'] ?? [];

        if (!$items) {
            $params = [
                'q' => $q,
                'country' => $countryCode,
                'featureClass' => 'P',
                'maxRows' => self::MAX_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'username' => $this->geonamesUsername(),
            ];

            $data = $this->geonamesGetJson('/searchJSON', $params, 'geonames_city_fallback');
            $items = $data['geonames'] ?? [];
        }

        $results = [];

        foreach ($items as $item) {
            $cityName = trim((string) ($item['name'] ?? $item['toponymName'] ?? ''));

            if ($cityName === '' || ($item['fcl'] ?? null) !== 'P') {
                continue;
            }

            if (!$this->cityMatchesSearch($cityName, $q)) {
                continue;
            }

            $results[] = [
                'source' => 'geonames',
                'sources' => ['geonames'],
                'postal_code' => null,
                'city_name' => $cityName,
                'area_name' => null,
                'country_code' => strtoupper((string) ($item['countryCode'] ?? $countryCode)),
                'country_name' => $item['countryName'] ?? null,
                'admin_name_1' => $item['adminName1'] ?? null,
                'admin_code_1' => $item['adminCode1'] ?? null,
                'admin_name_2' => $item['adminName2'] ?? null,
                'admin_code_2' => $item['adminCode2'] ?? null,
                'admin_name_3' => $item['adminName3'] ?? null,
                'admin_code_3' => $item['adminCode3'] ?? null,
                'geoname_id' => $item['geonameId'] ?? null,
                'osm_type' => null,
                'osm_id' => null,
                'place_id' => null,
                'feature_class' => $item['fcl'] ?? null,
                'feature_code' => $item['fcode'] ?? null,
                'lat' => $item['lat'] ?? null,
                'lng' => $item['lng'] ?? null,
                'lon' => $item['lng'] ?? null,
                'population' => $item['population'] ?? null,
                'display_name' => $this->buildDisplayName([
                    $cityName,
                    $item['adminName2'] ?? null,
                    $item['adminName1'] ?? null,
                    $item['countryName'] ?? null,
                ]),
            ];
        }

        return $results;
    }

    private function nominatimCity(string $countryCode, string $q): array
    {
        $params = [
            'q' => $q,
            'countrycodes' => strtolower($countryCode),
            'format' => 'jsonv2',
            'addressdetails' => '1',
            'limit' => (string) self::MAX_RESULTS,
            'dedupe' => '1',
            'accept-language' => 'fr',
            'email' => $this->nominatimEmail(),
        ];

        $items = $this->nominatimGetJson($params, 'nominatim_city');
        $results = [];

        foreach ($items as $item) {
            $address = $item['address'] ?? [];
            $resultCountryCode = strtoupper((string) ($address['country_code'] ?? $countryCode));

            if ($resultCountryCode !== '' && $resultCountryCode !== strtoupper($countryCode)) {
                continue;
            }

            $cityName = $this->getCityNameFromAddress($address) ?: ($item['name'] ?? null);

            if (!$cityName) {
                continue;
            }

            if (!$this->cityMatchesSearch((string) $cityName, $q)) {
                continue;
            }

            $type = $item['addresstype'] ?? $item['type'] ?? null;

            if (!$this->isAllowedCityType($type)) {
                continue;
            }

            $results[] = [
                'source' => 'nominatim',
                'sources' => ['nominatim'],
                'postal_code' => $address['postcode'] ?? null,
                'city_name' => $cityName,
                'area_name' => $this->getAreaNameFromAddress($address),
                'country_code' => $resultCountryCode,
                'country_name' => $address['country'] ?? null,
                'admin_name_1' => $address['state'] ?? null,
                'admin_code_1' => null,
                'admin_name_2' => $address['county'] ?? null,
                'admin_code_2' => null,
                'admin_name_3' => $address['municipality'] ?? null,
                'admin_code_3' => null,
                'geoname_id' => null,
                'osm_type' => $item['osm_type'] ?? null,
                'osm_id' => $item['osm_id'] ?? null,
                'place_id' => $item['place_id'] ?? null,
                'feature_class' => $item['class'] ?? null,
                'feature_code' => $type,
                'lat' => $item['lat'] ?? null,
                'lng' => $item['lon'] ?? null,
                'lon' => $item['lon'] ?? null,
                'population' => null,
                'display_name' => $item['display_name'] ?? $this->buildDisplayName([
                    $cityName,
                    $countryCode,
                ]),
            ];
        }

        return $results;
    }

    /**
     * ==========================================================================
     * QUARTIER
     * ==========================================================================
     */
    private function searchNeighbourhoodCombined(
        string $countryCode,
        string $cityName,
        string $q,
        ?float $cityLat,
        ?float $cityLng,
        string $adminCode1,
        string $adminCode2,
        string $adminCode3
    ): array {
        $geonamesResults = $this->safeApiCall(
            fn () => $this->geonamesNeighbourhood(
                $countryCode,
                $cityName,
                $q,
                $cityLat,
                $cityLng,
                $adminCode1,
                $adminCode2,
                $adminCode3
            )
        );

        $results = $geonamesResults;

        if (!$this->hasReliableNeighbourhoodResults($geonamesResults, $q)) {
            foreach ($this->safeApiCall(fn () => $this->nominatimNeighbourhood(
                $countryCode,
                $cityName,
                $q,
                $cityLat,
                $cityLng
            )) as $item) {
                $results[] = $item;
            }
        }

        $results = $this->filterNeighbourhoodsByDistance($results, $cityLat, $cityLng);
        $results = $this->mergeNeighbourhoods($results);

        usort($results, static function (array $a, array $b): int {
            $exactA = (int) ($a['exact_match'] ?? 0);
            $exactB = (int) ($b['exact_match'] ?? 0);

            if ($exactA !== $exactB) {
                return $exactB <=> $exactA;
            }

            $sourceCountA = count($a['sources'] ?? []);
            $sourceCountB = count($b['sources'] ?? []);

            if ($sourceCountA !== $sourceCountB) {
                return $sourceCountB <=> $sourceCountA;
            }

            $distanceA = $a['distance_km'] ?? 999999;
            $distanceB = $b['distance_km'] ?? 999999;

            if ($distanceA !== $distanceB) {
                return $distanceA <=> $distanceB;
            }

            return strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
        });

        return array_slice($results, 0, self::MAX_RESULTS);
    }

    private function geonamesNeighbourhood(
        string $countryCode,
        string $cityName,
        string $q,
        ?float $cityLat,
        ?float $cityLng,
        string $adminCode1,
        string $adminCode2,
        string $adminCode3
    ): array {
        if ($this->geonamesUsername() === '') {
            return [];
        }

        $allItems = [];

        $featureCodes = [
            'PPLX',
            'PPLL',
            'PPL',
            'PPLA3',
            'PPLA4',
        ];

        foreach ($featureCodes as $featureCode) {
            $params = [
                'name_startsWith' => $q,
                'country' => $countryCode,
                'featureClass' => 'P',
                'featureCode' => $featureCode,
                'maxRows' => self::MAX_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'isNameRequired' => 'true',
                'username' => $this->geonamesUsername(),
            ];

            if ($adminCode1 !== '') {
                $params['adminCode1'] = $adminCode1;
            }

            if ($adminCode2 !== '') {
                $params['adminCode2'] = $adminCode2;
            }

            if ($adminCode3 !== '') {
                $params['adminCode3'] = $adminCode3;
            }

            $data = $this->geonamesGetJson(
                '/searchJSON',
                $params,
                'geonames_neighbourhood_' . strtolower($featureCode)
            );

            foreach (($data['geonames'] ?? []) as $item) {
                $allItems[] = $item;
            }
        }

        if (!$allItems) {
            $params = [
                'q' => $q . ' ' . $cityName,
                'country' => $countryCode,
                'featureClass' => 'P',
                'maxRows' => self::MAX_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'username' => $this->geonamesUsername(),
            ];

            $data = $this->geonamesGetJson(
                '/searchJSON',
                $params,
                'geonames_neighbourhood_city_fallback'
            );

            foreach (($data['geonames'] ?? []) as $item) {
                $allItems[] = $item;
            }
        }

        $results = [];

        foreach ($allItems as $item) {
            $name = trim((string) ($item['name'] ?? $item['toponymName'] ?? ''));

            if ($name === '' || $this->textLooksSamePlace($name, $cityName)) {
                continue;
            }

            $featureClass = $item['fcl'] ?? null;
            $featureCode = $item['fcode'] ?? null;

            if ($featureClass !== 'P' || !$this->isAllowedGeonamesNeighbourhood($name, $featureCode)) {
                continue;
            }

            $lat = $item['lat'] ?? null;
            $lng = $item['lng'] ?? null;
            $distanceKm = null;

            if ($cityLat !== null && $cityLng !== null && is_numeric($lat) && is_numeric($lng)) {
                $distanceKm = $this->haversineDistanceKm(
                    $cityLat,
                    $cityLng,
                    (float) $lat,
                    (float) $lng
                );
            }

            $results[] = [
                'source' => 'geonames',
                'sources' => ['geonames'],
                'name' => $this->cleanNeighbourhoodName($name),
                'city_name' => $cityName,
                'country_code' => strtoupper((string) ($item['countryCode'] ?? $countryCode)),
                'country_name' => $item['countryName'] ?? null,
                'admin_name_1' => $item['adminName1'] ?? null,
                'admin_code_1' => $item['adminCode1'] ?? null,
                'admin_name_2' => $item['adminName2'] ?? null,
                'admin_code_2' => $item['adminCode2'] ?? null,
                'admin_name_3' => $item['adminName3'] ?? null,
                'admin_code_3' => $item['adminCode3'] ?? null,
                'geoname_id' => $item['geonameId'] ?? null,
                'osm_type' => null,
                'osm_id' => null,
                'place_id' => null,
                'feature_class' => $featureClass,
                'feature_code' => $featureCode,
                'lat' => $lat,
                'lng' => $lng,
                'lon' => $lng,
                'distance_km' => $distanceKm,
                'display_name' => $this->buildDisplayName([
                    $name,
                    $cityName,
                    $item['adminName2'] ?? null,
                    $item['adminName1'] ?? null,
                    $item['countryName'] ?? null,
                ]),
                'exact_match' => $this->normalizeSearchText($name) === $this->normalizeSearchText($q),
            ];
        }

        return $results;
    }

    private function nominatimNeighbourhood(
        string $countryCode,
        string $cityName,
        string $q,
        ?float $cityLat,
        ?float $cityLng
    ): array {
        $queries = [
            $q . ', ' . $cityName,
            $q . ' ' . $cityName,
        ];

        $allItems = [];

        foreach (array_unique($queries) as $query) {
            $params = [
                'q' => $query,
                'countrycodes' => strtolower($countryCode),
                'format' => 'jsonv2',
                'addressdetails' => '1',
                'namedetails' => '1',
                'extratags' => '1',
                'limit' => (string) self::MAX_RESULTS,
                'dedupe' => '1',
                'accept-language' => 'fr',
                'email' => $this->nominatimEmail(),
            ];

            foreach ($this->nominatimGetJson($params, 'nominatim_neighbourhood') as $item) {
                $allItems[] = $item;
            }
        }

        $results = [];

        foreach ($allItems as $item) {
            $address = $item['address'] ?? [];
            $resultCountryCode = strtoupper((string) ($address['country_code'] ?? $countryCode));

            if ($resultCountryCode !== '' && $resultCountryCode !== strtoupper($countryCode)) {
                continue;
            }

            $name = $this->getNeighbourhoodNameFromAddress($address, $item);

            if (!$name || $this->textLooksSamePlace($name, $cityName)) {
                continue;
            }

            $type = $item['addresstype'] ?? $item['type'] ?? null;

            if (!$this->isAllowedNominatimNeighbourhood($name, $type, $q)) {
                continue;
            }

            $cityFromResult = $this->getCityNameFromAddress($address);
            $areaName = $this->getAreaNameFromAddress($address);

            if (
                $cityFromResult !== null
                && !$this->textLooksSamePlace($cityFromResult, $cityName)
                && $areaName !== null
                && !$this->textLooksSamePlace($areaName, $cityName)
            ) {
                continue;
            }

            $lat = $item['lat'] ?? null;
            $lng = $item['lon'] ?? null;
            $distanceKm = null;

            if ($cityLat !== null && $cityLng !== null && is_numeric($lat) && is_numeric($lng)) {
                $distanceKm = $this->haversineDistanceKm(
                    $cityLat,
                    $cityLng,
                    (float) $lat,
                    (float) $lng
                );
            }

            $results[] = [
                'source' => 'nominatim',
                'sources' => ['nominatim'],
                'name' => $this->cleanNeighbourhoodName((string) $name),
                'city_name' => $cityName,
                'country_code' => $resultCountryCode,
                'country_name' => $address['country'] ?? null,
                'admin_name_1' => $address['state'] ?? null,
                'admin_code_1' => null,
                'admin_name_2' => $address['county'] ?? null,
                'admin_code_2' => null,
                'admin_name_3' => $address['municipality'] ?? null,
                'admin_code_3' => null,
                'geoname_id' => null,
                'osm_type' => $item['osm_type'] ?? null,
                'osm_id' => $item['osm_id'] ?? null,
                'place_id' => $item['place_id'] ?? null,
                'feature_class' => $item['class'] ?? null,
                'feature_code' => $type,
                'lat' => $lat,
                'lng' => $lng,
                'lon' => $lng,
                'distance_km' => $distanceKm,
                'display_name' => $item['display_name'] ?? $this->buildDisplayName([
                    $name,
                    $cityName,
                    $countryCode,
                ]),
                'exact_match' => $this->normalizeSearchText((string) $name) === $this->normalizeSearchText($q),
            ];
        }

        return $results;
    }

    /**
     * ==========================================================================
     * HTTP + CACHE
     * ==========================================================================
     */
    private function geonamesGetJson(string $endpoint, array $params, string $cachePrefix): array
    {
        $url = self::GEONAMES_BASE_URL . $endpoint . '?' . http_build_query($params);

        $data = $this->httpGetJsonWithCache($url, $cachePrefix, [
            'Accept' => 'application/json',
            'User-Agent' => 'TrouveMoiGeoNamesAutocomplete/1.0',
            'Referer' => $this->appReferer(),
        ]);

        if (isset($data['status']) && is_array($data['status'])) {
            return [];
        }

        return $data;
    }

    private function nominatimGetJson(array $params, string $cachePrefix): array
    {
        $url = self::NOMINATIM_URL . '?' . http_build_query($params);

        return $this->httpGetJsonWithCache($url, $cachePrefix, [
            'Accept' => 'application/json',
            'User-Agent' => $this->appUserAgent(),
            'Referer' => $this->appReferer(),
        ]);
    }

    private function httpGetJsonWithCache(string $url, string $prefix, array $headers): array
    {
        $cacheKey = 'geo_autocomplete_' . $prefix . '_' . hash('sha256', $url);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($url, $headers): array {
            $item->expiresAfter(self::CACHE_TTL_SECONDS);

            try {
                $response = $this->httpClient->request('GET', $url, [
                    'headers' => $headers,
                    'timeout' => self::HTTP_TIMEOUT_SECONDS,
                    'max_duration' => self::HTTP_TIMEOUT_SECONDS,
                ]);

                $statusCode = $response->getStatusCode();

                if (in_array($statusCode, [429, 500, 502, 503, 504], true)) {
                    return [];
                }

                if ($statusCode < 200 || $statusCode >= 300) {
                    return [];
                }

                $data = $response->toArray(false);

                return is_array($data) ? $data : [];
            } catch (Throwable) {
                return [];
            }
        });
    }

    private function safeApiCall(callable $callback): array
    {
        try {
            $result = $callback();

            return is_array($result) ? $result : [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * ==========================================================================
     * HELPERS FUSION
     * ==========================================================================
     */
    private function mergeResultsByComposite(array $items, array $keys): array
    {
        $merged = [];

        foreach ($items as $item) {
            $fingerprintParts = [];

            foreach ($keys as $key) {
                $fingerprintParts[] = $this->normalizeSearchText((string) ($item[$key] ?? ''));
            }

            $fingerprint = implode('|', $fingerprintParts);

            if (trim(str_replace('|', '', $fingerprint)) === '') {
                continue;
            }

            if (!isset($merged[$fingerprint])) {
                $item['sources'] = array_values(array_unique($item['sources'] ?? [$item['source'] ?? 'unknown']));
                $merged[$fingerprint] = $item;
                continue;
            }

            $existing = $merged[$fingerprint];

            $existing['sources'] = array_values(array_unique(array_merge(
                $existing['sources'] ?? [],
                $item['sources'] ?? [$item['source'] ?? 'unknown']
            )));

            foreach ($item as $key => $value) {
                if ($this->isEmptyValue($existing[$key] ?? null) && !$this->isEmptyValue($value)) {
                    $existing[$key] = $value;
                }
            }

            $merged[$fingerprint] = $existing;
        }

        return array_values($merged);
    }

    private function mergeNeighbourhoods(array $items): array
    {
        $merged = [];

        foreach ($items as $item) {
            $name = $this->cleanNeighbourhoodName((string) ($item['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $fingerprint = $this->normalizeSearchText(
                implode('|', [
                    $item['country_code'] ?? '',
                    $item['city_name'] ?? '',
                    $name,
                ])
            );

            if (!isset($merged[$fingerprint])) {
                $item['name'] = $name;
                $item['sources'] = array_values(array_unique($item['sources'] ?? [$item['source'] ?? 'unknown']));
                $merged[$fingerprint] = $item;
                continue;
            }

            $existing = $merged[$fingerprint];

            $existing['sources'] = array_values(array_unique(array_merge(
                $existing['sources'] ?? [],
                $item['sources'] ?? [$item['source'] ?? 'unknown']
            )));

            $existing['exact_match'] = (bool) ($existing['exact_match'] ?? false)
                || (bool) ($item['exact_match'] ?? false);

            if (
                ($existing['distance_km'] ?? null) === null
                || (
                    ($item['distance_km'] ?? null) !== null
                    && (float) $item['distance_km'] < (float) $existing['distance_km']
                )
            ) {
                $existing['distance_km'] = $item['distance_km'] ?? $existing['distance_km'] ?? null;
            }

            foreach ($item as $key => $value) {
                if ($this->isEmptyValue($existing[$key] ?? null) && !$this->isEmptyValue($value)) {
                    $existing[$key] = $value;
                }
            }

            $merged[$fingerprint] = $existing;
        }

        return array_values($merged);
    }

    private function filterNeighbourhoodsByDistance(array $items, ?float $cityLat, ?float $cityLng): array
    {
        if ($cityLat === null || $cityLng === null) {
            return $items;
        }

        $filtered = [];

        foreach ($items as $item) {
            $lat = $item['lat'] ?? null;
            $lng = $item['lng'] ?? $item['lon'] ?? null;

            $distanceKm = $item['distance_km'] ?? null;

            if ($distanceKm === null && is_numeric($lat) && is_numeric($lng)) {
                $distanceKm = $this->haversineDistanceKm(
                    $cityLat,
                    $cityLng,
                    (float) $lat,
                    (float) $lng
                );

                $item['distance_km'] = $distanceKm;
            }

            if ($distanceKm !== null && (float) $distanceKm > self::MAX_DISTANCE_KM_FOR_NEIGHBOURHOOD) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * ==========================================================================
     * HELPERS VILLE
     * ==========================================================================
     */
    private function normalizeCitySearchDuplicates(array $items, string $query, string $countryCode): array
    {
        $merged = [];

        foreach ($items as $item) {
            $cityName = trim((string) ($item['city_name'] ?? ''));

            if ($cityName === '') {
                continue;
            }

            $canonicalCityKey = $this->citySearchCanonicalKey($cityName, $query, $countryCode);

            if ($canonicalCityKey === '') {
                continue;
            }

            $dedupeKey = strtoupper($countryCode)
                . '|'
                . $this->normalizeSearchText((string) ($item['admin_name_1'] ?? ''))
                . '|'
                . $canonicalCityKey;

            $displayCityName = $this->citySearchDisplayName(
                $cityName,
                $query,
                $countryCode,
                $canonicalCityKey
            );

            $item['city_name'] = $displayCityName;
            $item['display_name'] = $this->buildDisplayName([
                $displayCityName,
                $item['admin_name_2'] ?? null,
                $item['admin_name_1'] ?? null,
                $item['country_name'] ?? null,
                $item['country_code'] ?? $countryCode,
            ]);

            if (!isset($merged[$dedupeKey])) {
                $item['sources'] = array_values(array_unique($item['sources'] ?? [$item['source'] ?? 'unknown']));
                $merged[$dedupeKey] = $item;
                continue;
            }

            $existing = $merged[$dedupeKey];

            $existing['sources'] = array_values(array_unique(array_merge(
                $existing['sources'] ?? [],
                $item['sources'] ?? [$item['source'] ?? 'unknown']
            )));

            foreach ($item as $key => $value) {
                if ($this->isEmptyValue($existing[$key] ?? null) && !$this->isEmptyValue($value)) {
                    $existing[$key] = $value;
                }
            }

            $existing['city_name'] = $displayCityName;
            $existing['display_name'] = $this->buildDisplayName([
                $displayCityName,
                $existing['admin_name_2'] ?? null,
                $existing['admin_name_1'] ?? null,
                $existing['country_name'] ?? null,
                $existing['country_code'] ?? $countryCode,
            ]);

            $merged[$dedupeKey] = $existing;
        }

        return array_values($merged);
    }

    private function citySearchCanonicalKey(string $cityName, string $query, string $countryCode): string
    {
        $countryCode = strtoupper($countryCode);
        $cityKey = $this->normalizeSearchText($cityName);
        $queryPostalCode = $this->normalizePostalCode($query);

        if ($countryCode === 'FR') {
            if (
                (
                    preg_match('/^750(0[1-9]|1[0-9]|20)$/', $queryPostalCode)
                    || str_starts_with($cityKey, 'paris')
                )
                && str_contains($cityKey, 'paris')
            ) {
                return 'paris';
            }

            if (
                (
                    preg_match('/^130(0[1-9]|1[0-6])$/', $queryPostalCode)
                    || str_starts_with($cityKey, 'marseille')
                )
                && str_contains($cityKey, 'marseille')
            ) {
                return 'marseille';
            }

            if (
                (
                    preg_match('/^6900[1-9]$/', $queryPostalCode)
                    || str_starts_with($cityKey, 'lyon')
                )
                && str_contains($cityKey, 'lyon')
            ) {
                return 'lyon';
            }
        }

        $cityKey = preg_replace('/\s+\d{1,2}(er|e|eme|ème)?(\s+.*)?$/u', '', $cityKey) ?? $cityKey;

        return trim($cityKey);
    }

    private function citySearchDisplayName(
        string $cityName,
        string $query,
        string $countryCode,
        string $canonicalCityKey
    ): string {
        $countryCode = strtoupper($countryCode);
        $queryPostalCode = $this->normalizePostalCode($query);

        if ($countryCode === 'FR') {
            if (
                $canonicalCityKey === 'paris'
                && (
                    preg_match('/^750(0[1-9]|1[0-9]|20)$/', $queryPostalCode)
                    || str_starts_with($this->normalizeSearchText($cityName), 'paris')
                )
            ) {
                return 'Paris';
            }

            if ($canonicalCityKey === 'marseille') {
                return 'Marseille';
            }

            if ($canonicalCityKey === 'lyon') {
                return 'Lyon';
            }
        }

        $cleanName = trim((string) preg_replace('/\s+\d{1,2}(er|e|eme|ème)?(\s+.*)?$/iu', '', $cityName));

        return $cleanName !== '' ? $cleanName : $cityName;
    }

    private function cityMatchesSearch(string $cityName, string $search): bool
    {
        $city = $this->normalizeSearchText($cityName);
        $q = $this->normalizeSearchText($search);

        if ($q === '' || $city === '') {
            return true;
        }

        if (preg_match('/^[a-z0-9 ]*\d[a-z0-9 ]*$/i', $q)) {
            return true;
        }

        if ($city === $q) {
            return true;
        }

        if (str_contains($q, ' ')) {
            if (str_starts_with($city, $q)) {
                $remaining = substr($city, strlen($q));

                if ($remaining !== '' && !preg_match('/^\s/u', $remaining)) {
                    return true;
                }

                return trim($remaining) === '';
            }

            return false;
        }

        return str_starts_with($city, $q) || str_contains($city, $q);
    }

    private function isAllowedCityType(?string $type): bool
    {
        if ($type === null) {
            return true;
        }

        return in_array($type, [
            'city',
            'town',
            'village',
            'municipality',
            'administrative',
            'locality',
            'hamlet',
        ], true);
    }

    /**
     * ==========================================================================
     * HELPERS QUARTIER
     * ==========================================================================
     */
    private function hasReliableNeighbourhoodResults(array $items, string $q): bool
    {
        if (!$items) {
            return false;
        }

        $target = $this->normalizeSearchText($q);

        foreach ($items as $item) {
            $name = $this->normalizeSearchText((string) ($item['name'] ?? ''));
            $featureCode = (string) ($item['feature_code'] ?? '');

            if ($name === $target && in_array($featureCode, ['PPLX', 'PPLL', 'PPL'], true)) {
                return true;
            }
        }

        return count($items) >= 3;
    }

    private function isAllowedGeonamesNeighbourhood(string $name, ?string $featureCode): bool
    {
        $normalized = $this->normalizeSearchText($name);

        $forbiddenWords = [
            'airport',
            'aeroport',
            'gare',
            'station',
            'arrondissement',
            'municipality',
            'commune',
            'county',
            'province',
            'state',
            'oblast',
            'prefecture',
        ];

        foreach ($forbiddenWords as $word) {
            if (str_contains($normalized, $word)) {
                return false;
            }
        }

        $allowedFeatureCodes = [
            'PPLX',
            'PPLL',
            'PPL',
            'PPLA',
            'PPLA2',
            'PPLA3',
            'PPLA4',
            'PPLC',
        ];

        if ($featureCode === null) {
            return true;
        }

        return in_array($featureCode, $allowedFeatureCodes, true);
    }

    private function isAllowedNominatimNeighbourhood(string $name, ?string $type, string $q): bool
    {
        $normalizedName = $this->normalizeSearchText($name);

        $forbiddenWords = [
            'airport',
            'aeroport',
            'gare',
            'station',
            'arrondissement',
            'arrondissements',
            'municipality',
            'commune',
            'county',
            'province',
            'state',
            'prefecture',
            'oblast',
            'hotel',
            'restaurant',
            'school',
            'hospital',
        ];

        foreach ($forbiddenWords as $word) {
            if (str_contains($normalizedName, $word)) {
                return false;
            }
        }

        $allowedTypes = [
            'neighbourhood',
            'neighborhood',
            'quarter',
            'suburb',
            'city_district',
            'borough',
            'residential',
            'locality',
            'administrative',
        ];

        if ($type === null) {
            return str_contains($normalizedName, $this->normalizeSearchText($q));
        }

        return in_array($type, $allowedTypes, true)
            || str_contains($normalizedName, $this->normalizeSearchText($q));
    }

    private function cleanNeighbourhoodName(string $name): string
    {
        $name = trim($name);

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

        return trim($name);
    }

    /**
     * ==========================================================================
     * HELPERS ADRESSE
     * ==========================================================================
     */
    private function getCityNameFromAddress(array $address): ?string
    {
        return $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? $address['hamlet']
            ?? $address['locality']
            ?? null;
    }

    private function getAreaNameFromAddress(array $address): ?string
    {
        return $address['suburb']
            ?? $address['city_district']
            ?? $address['borough']
            ?? $address['district']
            ?? $address['quarter']
            ?? $address['neighbourhood']
            ?? null;
    }

    private function getNeighbourhoodNameFromAddress(array $address, array $item): ?string
    {
        return $address['neighbourhood']
            ?? $address['quarter']
            ?? $address['suburb']
            ?? $address['city_district']
            ?? $address['borough']
            ?? ($item['namedetails']['name'] ?? null)
            ?? ($item['name'] ?? null);
    }

    private function textLooksSamePlace(string $a, string $b): bool
    {
        $a = $this->normalizeSearchText($a);
        $b = $this->normalizeSearchText($b);

        if ($a === '' || $b === '') {
            return true;
        }

        return $a === $b
            || str_contains($a, $b)
            || str_contains($b, $a);
    }

    /**
     * ==========================================================================
     * HELPERS GÉNÉRAUX
     * ==========================================================================
     */
    private function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(trim($value));

        if (class_exists(Normalizer::class)) {
            $value = Normalizer::normalize($value, Normalizer::FORM_D) ?: $value;
        }

        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', $value) ?? $value;
        $value = str_replace(['’', "'", '-', '_', '.', ',', ';', ':', '(', ')', '[', ']'], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function normalizePostalCode(string $value): string
    {
        $value = strtoupper(trim($value));

        return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
    }

    private function buildDisplayName(array $parts): string
    {
        $parts = array_filter($parts, static function ($value): bool {
            return $value !== null && trim((string) $value) !== '';
        });

        return implode(' — ', array_unique(array_map('strval', $parts)));
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
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function geonamesUsername(): string
    {
        return trim((string) ($_ENV['GEONAMES_USERNAME'] ?? $_SERVER['GEONAMES_USERNAME'] ?? ''));
    }

    private function nominatimEmail(): string
    {
        return trim((string) ($_ENV['NOMINATIM_EMAIL'] ?? $_SERVER['NOMINATIM_EMAIL'] ?? 'guillaume2vo@hotmail.com'));
    }

    private function appUserAgent(): string
    {
        $email = $this->nominatimEmail();

        return 'TrouveMoiGeoSearch/1.0 ' . $email;
    }

    private function appReferer(): string
    {
        return trim((string) ($_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? 'https://preprod.boolts.fr/'));
    }
}