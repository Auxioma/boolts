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

    private const MIN_QUERY_LENGTH = 2;

    private const CACHE_TTL_SECONDS = 604800; // 7 jours
    private const SHORT_CACHE_TTL_SECONDS = 30;

    private const MAX_RESULTS = 8;
    private const MAX_EXTERNAL_RESULTS = 20;

    private const MAX_DISTANCE_KM_FOR_NEIGHBOURHOOD = 70.0;

    private const HTTP_TIMEOUT_SECONDS = 4.0;
    private const HTTP_CONNECT_TIMEOUT_SECONDS = 1.5;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    #[Route('/pays', name: 'app_geo_country_autocomplete', methods: ['GET'])]
    public function countries(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));

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
    public function cities(Request $request): JsonResponse
    {
        $countryCode = strtoupper(trim((string) $request->query->get('country_code', '')));
        $countryName = trim((string) $request->query->get('country_name', ''));
        $q = trim((string) $request->query->get('q', ''));

        if ($countryCode === '' || mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return $this->jsonResults([]);
        }

        $results = $this->cacheResult('cities', [
            'country_code' => $countryCode,
            'country_name' => $countryName,
            'q' => $q,
        ], function () use ($countryCode, $countryName, $q): array {
            return $this->searchCityCombined($countryCode, $countryName, $q);
        });

        return $this->jsonResults($results);
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

        if ($countryCode === '' || $cityName === '' || mb_strlen($q) < self::MIN_QUERY_LENGTH) {
            return $this->jsonResults([]);
        }

        $results = $this->cacheResult('districts', [
            'country_code' => $countryCode,
            'city_name' => $cityName,
            'q' => $q,
            'city_lat' => $cityLat,
            'city_lng' => $cityLng,
            'admin_code_1' => $adminCode1,
            'admin_code_2' => $adminCode2,
            'admin_code_3' => $adminCode3,
        ], function () use (
            $countryCode,
            $cityName,
            $q,
            $cityLat,
            $cityLng,
            $adminCode1,
            $adminCode2,
            $adminCode3
        ): array {
            return $this->searchNeighbourhoodCombined(
                $countryCode,
                $cityName,
                $q,
                $cityLat,
                $cityLng,
                $adminCode1,
                $adminCode2,
                $adminCode3
            );
        });

        return $this->jsonResults($results);
    }

    #[Route('/debug-ville', name: 'app_geo_autocomplete_debug_city', methods: ['GET'])]
    public function debugVille(Request $request): JsonResponse
    {
        $countryCode = strtoupper(trim((string) $request->query->get('country_code', 'FR')));
        $countryName = trim((string) $request->query->get('country_name', 'France'));
        $q = trim((string) $request->query->get('q', 'par'));

        $geonamesUsername = $this->geonamesUsername();

        $geonamesParams = [
            'name_startsWith' => $q,
            'country' => $countryCode,
            'featureClass' => 'P',
            'maxRows' => self::MAX_EXTERNAL_RESULTS,
            'orderby' => 'relevance',
            'style' => 'FULL',
            'lang' => 'fr',
            'isNameRequired' => 'true',
            'username' => $geonamesUsername,
        ];

        $geonamesUrl = self::GEONAMES_BASE_URL . '/searchJSON?' . http_build_query($geonamesParams);

        $nominatimParams = [
            'q' => $q,
            'countrycodes' => strtolower($countryCode),
            'format' => 'jsonv2',
            'addressdetails' => '1',
            'limit' => (string) self::MAX_RESULTS,
            'dedupe' => '1',
            'accept-language' => 'fr',
            'email' => $this->nominatimEmail(),
        ];

        $nominatimUrl = self::NOMINATIM_URL . '?' . http_build_query($nominatimParams);

        return $this->json([
            'request' => [
                'q' => $q,
                'country_code' => $countryCode,
                'country_name' => $countryName,
            ],
            'env' => [
                'geonames_username_present' => $geonamesUsername !== '',
                'geonames_username' => $geonamesUsername !== '' ? 'OK' : 'VIDE',
                'nominatim_email' => $this->nominatimEmail(),
                'use_nominatim_autocomplete' => $this->useNominatimAutocomplete(),
            ],
            'geonames_url' => $geonamesUrl,
            'geonames_raw' => $this->httpGetJsonNoCache($geonamesUrl, [
                'Accept' => 'application/json',
                'User-Agent' => 'TrouveMoiGeoNamesAutocomplete/1.0',
                'Referer' => $this->appReferer(),
            ]),
            'nominatim_url' => $nominatimUrl,
            'nominatim_raw' => $this->httpGetJsonNoCache($nominatimUrl, [
                'Accept' => 'application/json',
                'User-Agent' => $this->appUserAgent(),
                'Referer' => $this->appReferer(),
            ]),
            'final_city_results' => $this->searchCityCombined($countryCode, $countryName, $q),
        ]);
    }

    #[Route('/debug', name: 'app_geo_autocomplete_debug', methods: ['GET'])]
    public function debug(Request $request): JsonResponse
    {
        $countryCode = strtoupper(trim((string) $request->query->get('country_code', 'FR')));
        $countryName = trim((string) $request->query->get('country_name', 'France'));
        $q = trim((string) $request->query->get('q', 'par'));
        $cityName = trim((string) $request->query->get('city_name', 'Paris'));

        return $this->json([
            'env' => [
                'geonames_username_present' => $this->geonamesUsername() !== '',
                'geonames_username' => $this->geonamesUsername() !== '' ? 'OK' : 'VIDE',
                'nominatim_email' => $this->nominatimEmail(),
                'use_nominatim_autocomplete' => $this->useNominatimAutocomplete(),
            ],
            'tests' => [
                'city_results' => $this->searchCityCombined($countryCode, $countryName, $q),
                'district_results' => $this->searchNeighbourhoodCombined(
                    $countryCode,
                    $cityName,
                    'mon',
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
            'results' => array_slice($results, 0, self::MAX_RESULTS),
        ]);

        $response->setPublic();
        $response->setMaxAge(300);
        $response->setSharedMaxAge(300);

        return $response;
    }

    private function cacheResult(string $prefix, array $params, callable $callback): array
    {
        ksort($params);

        $cacheKey = 'geo_autocomplete_result_' . $prefix . '_' . hash('sha256', json_encode($params));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($callback): array {
            $item->expiresAfter(self::CACHE_TTL_SECONDS);

            $results = $callback();

            if (!is_array($results)) {
                $item->expiresAfter(self::SHORT_CACHE_TTL_SECONDS);

                return [];
            }

            if ($results === []) {
                $item->expiresAfter(self::SHORT_CACHE_TTL_SECONDS);
            }

            return array_slice($results, 0, self::MAX_RESULTS);
        });
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
            $matched = str_contains($this->normalizeSearchText($code), $normalizedQuery);

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
     * VILLES
     * ==========================================================================
     */
    private function searchCityCombined(string $countryCode, string $countryName, string $q): array
    {
        $results = [];

        foreach ($this->safeApiCall(fn (): array => $this->geonamesCity($countryCode, $countryName, $q)) as $item) {
            $results[] = $item;
        }

        /**
         * Nominatim public n'est pas adapté à l'autocomplétion intensive.
         * Il est donc désactivé par défaut.
         */
        if (!$results && $this->useNominatimAutocomplete() && mb_strlen($q) >= 3) {
            foreach ($this->safeApiCall(fn (): array => $this->nominatimCity($countryCode, $countryName, $q)) as $item) {
                $results[] = $item;
            }
        }

        /**
         * Fallback de sécurité pour ne pas bloquer le formulaire
         * pendant que GeoNames est mal configuré ou indisponible.
         */
        if (!$results) {
            foreach ($this->fallbackCities($countryCode, $countryName, $q) as $item) {
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

            return strcasecmp((string) ($a['city_name'] ?? ''), (string) ($b['city_name'] ?? ''));
        });

        return array_slice($results, 0, self::MAX_RESULTS);
    }

    private function geonamesCity(string $countryCode, string $countryName, string $q): array
    {
        if ($this->geonamesUsername() === '') {
            return [];
        }

        $params = [
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

        $data = $this->geonamesGetJson('/searchJSON', $params, 'geonames_city_starts');
        $items = $data['geonames'] ?? [];

        if (!$items && mb_strlen($q) >= 3) {
            $params = [
                'q' => $q,
                'country' => $countryCode,
                'featureClass' => 'P',
                'maxRows' => self::MAX_EXTERNAL_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'username' => $this->geonamesUsername(),
            ];

            $data = $this->geonamesGetJson('/searchJSON', $params, 'geonames_city_q');
            $items = $data['geonames'] ?? [];
        }

        $results = [];

        foreach ($items as $item) {
            $cityName = trim((string) ($item['name'] ?? $item['toponymName'] ?? ''));

            if ($cityName === '') {
                continue;
            }

            if (($item['fcl'] ?? null) !== 'P') {
                continue;
            }

            if (!$this->cityMatchesSearch($cityName, $q)) {
                continue;
            }

            $resultCountryCode = strtoupper((string) ($item['countryCode'] ?? $countryCode));

            if ($resultCountryCode !== strtoupper($countryCode)) {
                continue;
            }

            $results[] = [
                'source' => 'geonames',
                'sources' => ['geonames'],
                'postal_code' => null,
                'city_name' => $cityName,
                'area_name' => null,
                'country_code' => $resultCountryCode,
                'country_name' => $item['countryName'] ?? $countryName ?: null,
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
                    $item['countryName'] ?? $countryName ?: null,
                ]),
            ];
        }

        return $results;
    }

    private function nominatimCity(string $countryCode, string $countryName, string $q): array
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

            if (!is_array($address)) {
                continue;
            }

            $resultCountryCode = strtoupper((string) ($address['country_code'] ?? $countryCode));

            if ($resultCountryCode !== strtoupper($countryCode)) {
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
                'city_name' => (string) $cityName,
                'area_name' => $this->getAreaNameFromAddress($address),
                'country_code' => $resultCountryCode,
                'country_name' => $address['country'] ?? $countryName ?: null,
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
                    $countryName ?: $countryCode,
                ]),
            ];
        }

        return $results;
    }

    private function fallbackCities(string $countryCode, string $countryName, string $q): array
    {
        $normalizedQ = $this->normalizeSearchText($q);

        $citiesByCountry = [
            'FR' => [
                ['Paris', 'Île-de-France', 'Paris', '48.8566', '2.3522', 2161000],
                ['Pantin', 'Île-de-France', 'Seine-Saint-Denis', '48.8966', '2.4017', 59000],
                ['Pau', 'Nouvelle-Aquitaine', 'Pyrénées-Atlantiques', '43.2951', '-0.3708', 76000],
                ['Parthenay', 'Nouvelle-Aquitaine', 'Deux-Sèvres', '46.6486', '-0.2466', 10000],
                ['Palaiseau', 'Île-de-France', 'Essonne', '48.7145', '2.2457', 35000],
                ['Perpignan', 'Occitanie', 'Pyrénées-Orientales', '42.6887', '2.8948', 119000],
                ['Poitiers', 'Nouvelle-Aquitaine', 'Vienne', '46.5802', '0.3404', 90000],
                ['Puteaux', 'Île-de-France', 'Hauts-de-Seine', '48.8847', '2.2396', 45000],
            ],
            'BE' => [
                ['Bruxelles', 'Bruxelles-Capitale', 'Bruxelles', '50.8503', '4.3517', 1200000],
                ['Bruges', 'Flandre', 'Flandre-Occidentale', '51.2093', '3.2247', 118000],
            ],
            'MA' => [
                ['Casablanca', 'Casablanca-Settat', 'Casablanca', '33.5731', '-7.5898', 3350000],
                ['Marrakech', 'Marrakech-Safi', 'Marrakech', '31.6295', '-7.9811', 928000],
            ],
        ];

        $cities = $citiesByCountry[$countryCode] ?? [];
        $results = [];

        foreach ($cities as $city) {
            [$cityName, $adminName1, $adminName2, $lat, $lng, $population] = $city;

            $normalizedCityName = $this->normalizeSearchText($cityName);

            if (
                !str_starts_with($normalizedCityName, $normalizedQ)
                && !str_contains($normalizedCityName, $normalizedQ)
            ) {
                continue;
            }

            $results[] = [
                'source' => 'fallback',
                'sources' => ['fallback'],
                'postal_code' => null,
                'city_name' => $cityName,
                'area_name' => null,
                'country_code' => $countryCode,
                'country_name' => $countryName ?: $this->countryNameFromCode($countryCode),
                'admin_name_1' => $adminName1,
                'admin_code_1' => null,
                'admin_name_2' => $adminName2,
                'admin_code_2' => null,
                'admin_name_3' => null,
                'admin_code_3' => null,
                'geoname_id' => null,
                'osm_type' => null,
                'osm_id' => null,
                'place_id' => null,
                'feature_class' => 'P',
                'feature_code' => 'PPL',
                'lat' => $lat,
                'lng' => $lng,
                'lon' => $lng,
                'population' => $population,
                'display_name' => $this->buildDisplayName([
                    $cityName,
                    $adminName2,
                    $adminName1,
                    $countryName ?: $this->countryNameFromCode($countryCode),
                ]),
            ];
        }

        usort($results, static function (array $a, array $b): int {
            return ((int) ($b['population'] ?? 0)) <=> ((int) ($a['population'] ?? 0));
        });

        return $results;
    }

    /**
     * ==========================================================================
     * QUARTIERS
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
        $results = [];

        foreach ($this->safeApiCall(fn (): array => $this->geonamesNeighbourhood(
            $countryCode,
            $cityName,
            $q,
            $cityLat,
            $cityLng,
            $adminCode1,
            $adminCode2,
            $adminCode3
        )) as $item) {
            $results[] = $item;
        }

        if (!$results && $this->useNominatimAutocomplete() && mb_strlen($q) >= 3) {
            foreach ($this->safeApiCall(fn (): array => $this->nominatimNeighbourhood(
                $countryCode,
                $cityName,
                $q,
                $cityLat,
                $cityLng
            )) as $item) {
                $results[] = $item;
            }
        }

        if (!$results) {
            foreach ($this->fallbackNeighbourhoods($countryCode, $cityName, $q) as $item) {
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

        $params = [
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

        if ($adminCode1 !== '') {
            $params['adminCode1'] = $adminCode1;
        }

        if ($adminCode2 !== '') {
            $params['adminCode2'] = $adminCode2;
        }

        if ($adminCode3 !== '') {
            $params['adminCode3'] = $adminCode3;
        }

        $data = $this->geonamesGetJson('/searchJSON', $params, 'geonames_neighbourhood_starts');
        $items = $data['geonames'] ?? [];

        if (!$items && mb_strlen($q) >= 3) {
            $params = [
                'q' => $q . ' ' . $cityName,
                'country' => $countryCode,
                'featureClass' => 'P',
                'maxRows' => self::MAX_EXTERNAL_RESULTS,
                'orderby' => 'relevance',
                'style' => 'FULL',
                'lang' => 'fr',
                'username' => $this->geonamesUsername(),
            ];

            $data = $this->geonamesGetJson('/searchJSON', $params, 'geonames_neighbourhood_q');
            $items = $data['geonames'] ?? [];
        }

        $results = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? $item['toponymName'] ?? ''));

            if ($name === '') {
                continue;
            }

            if ($this->textLooksSamePlace($name, $cityName)) {
                continue;
            }

            if (!$this->placeNameMatchesSearch($name, $q)) {
                continue;
            }

            $featureClass = $item['fcl'] ?? null;
            $featureCode = $item['fcode'] ?? null;

            if ($featureClass !== 'P') {
                continue;
            }

            if (!$this->isAllowedGeonamesNeighbourhood($name, $featureCode)) {
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
                'district_name' => $this->cleanNeighbourhoodName($name),
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
        $params = [
            'q' => $q . ', ' . $cityName,
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

        $items = $this->nominatimGetJson($params, 'nominatim_neighbourhood');
        $results = [];

        foreach ($items as $item) {
            $address = $item['address'] ?? [];

            if (!is_array($address)) {
                continue;
            }

            $resultCountryCode = strtoupper((string) ($address['country_code'] ?? $countryCode));

            if ($resultCountryCode !== strtoupper($countryCode)) {
                continue;
            }

            $name = $this->getNeighbourhoodNameFromAddress($address, $item);

            if (!$name) {
                continue;
            }

            if ($this->textLooksSamePlace((string) $name, $cityName)) {
                continue;
            }

            if (!$this->placeNameMatchesSearch((string) $name, $q)) {
                continue;
            }

            $type = $item['addresstype'] ?? $item['type'] ?? null;

            if (!$this->isAllowedNominatimNeighbourhood((string) $name, $type, $q)) {
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
                'district_name' => $this->cleanNeighbourhoodName((string) $name),
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

    private function fallbackNeighbourhoods(string $countryCode, string $cityName, string $q): array
    {
        $normalizedCountry = strtoupper($countryCode);
        $normalizedCity = $this->normalizeSearchText($cityName);
        $normalizedQ = $this->normalizeSearchText($q);

        $items = [];

        if ($normalizedCountry === 'FR' && $normalizedCity === 'paris') {
            $items = [
                ['Montmartre', '48.8867', '2.3431'],
                ['Montparnasse', '48.8421', '2.3219'],
                ['Marais', '48.8566', '2.3622'],
                ['Belleville', '48.8722', '2.3849'],
                ['Bastille', '48.8530', '2.3690'],
                ['Pigalle', '48.8822', '2.3372'],
                ['Passy', '48.8570', '2.2770'],
                ['Batignolles', '48.8864', '2.3215'],
            ];
        }

        $results = [];

        foreach ($items as $item) {
            [$name, $lat, $lng] = $item;

            $normalizedName = $this->normalizeSearchText($name);

            if (
                !str_starts_with($normalizedName, $normalizedQ)
                && !str_contains($normalizedName, $normalizedQ)
            ) {
                continue;
            }

            $results[] = [
                'source' => 'fallback',
                'sources' => ['fallback'],
                'name' => $name,
                'district_name' => $name,
                'city_name' => $cityName,
                'country_code' => $countryCode,
                'country_name' => $this->countryNameFromCode($countryCode),
                'admin_name_1' => 'Île-de-France',
                'admin_code_1' => null,
                'admin_name_2' => 'Paris',
                'admin_code_2' => null,
                'admin_name_3' => null,
                'admin_code_3' => null,
                'geoname_id' => null,
                'osm_type' => null,
                'osm_id' => null,
                'place_id' => null,
                'feature_class' => 'P',
                'feature_code' => 'PPLX',
                'lat' => $lat,
                'lng' => $lng,
                'lon' => $lng,
                'distance_km' => null,
                'display_name' => $this->buildDisplayName([
                    $name,
                    $cityName,
                    'Paris',
                    'Île-de-France',
                    $this->countryNameFromCode($countryCode),
                ]),
                'exact_match' => $normalizedName === $normalizedQ,
            ];
        }

        return $results;
    }

    /**
     * ==========================================================================
     * HTTP
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

        $data = $this->httpGetJsonWithCache($url, $cachePrefix, [
            'Accept' => 'application/json',
            'User-Agent' => $this->appUserAgent(),
            'Referer' => $this->appReferer(),
        ]);

        return array_is_list($data) ? $data : [];
    }

    private function httpGetJsonWithCache(string $url, string $prefix, array $headers): array
    {
        $cacheKey = 'geo_autocomplete_http_' . $prefix . '_' . hash('sha256', $url);

        $cached = $this->cache->get($cacheKey, function (ItemInterface $item) use ($url, $headers): array {
            $item->expiresAfter(self::CACHE_TTL_SECONDS);

            try {
                $response = $this->httpClient->request('GET', $url, [
                    'headers' => $headers,
                    'timeout' => self::HTTP_TIMEOUT_SECONDS,
                    'max_duration' => self::HTTP_TIMEOUT_SECONDS,
                    'connect_timeout' => self::HTTP_CONNECT_TIMEOUT_SECONDS,
                ]);

                $statusCode = $response->getStatusCode();

                if ($statusCode < 200 || $statusCode >= 300) {
                    $item->expiresAfter(self::SHORT_CACHE_TTL_SECONDS);

                    return [];
                }

                $data = $response->toArray(false);

                if (!is_array($data)) {
                    $item->expiresAfter(self::SHORT_CACHE_TTL_SECONDS);

                    return [];
                }

                if ($data === [] || isset($data['status'])) {
                    $item->expiresAfter(self::SHORT_CACHE_TTL_SECONDS);
                }

                return $data;
            } catch (Throwable) {
                $item->expiresAfter(self::SHORT_CACHE_TTL_SECONDS);

                return [];
            }
        });

        return is_array($cached) ? $cached : [];
    }

    private function httpGetJsonNoCache(string $url, array $headers): array
    {
        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => $headers,
                'timeout' => self::HTTP_TIMEOUT_SECONDS,
                'max_duration' => self::HTTP_TIMEOUT_SECONDS,
                'connect_timeout' => self::HTTP_CONNECT_TIMEOUT_SECONDS,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            return [
                'http_status' => $statusCode,
                'json' => json_decode($content, true),
                'raw' => $content,
            ];
        } catch (Throwable $exception) {
            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
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
     * MERGE / DEDUPE
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
                $item['district_name'] = $name;
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

            foreach ($item as $key => $value) {
                if ($this->isEmptyValue($existing[$key] ?? null) && !$this->isEmptyValue($value)) {
                    $existing[$key] = $value;
                }
            }

            $merged[$fingerprint] = $existing;
        }

        return array_values($merged);
    }

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

            $merged[$dedupeKey] = $existing;
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
     * MATCHING / FILTRES
     * ==========================================================================
     */
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

        return str_starts_with($city, $q) || str_contains($city, $q);
    }

    private function placeNameMatchesSearch(string $name, string $q): bool
    {
        $name = $this->normalizeSearchText($name);
        $q = $this->normalizeSearchText($q);

        if ($name === '' || $q === '') {
            return true;
        }

        return str_starts_with($name, $q) || str_contains($name, $q);
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

        if ($featureCode === null) {
            return true;
        }

        return in_array($featureCode, [
            'PPLX',
            'PPLL',
            'PPL',
            'PPLA',
            'PPLA2',
            'PPLA3',
            'PPLA4',
            'PPLC',
        ], true);
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
            return false;
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

    private function countryNameFromCode(string $countryCode): string
    {
        try {
            return Countries::getName(strtoupper($countryCode), 'fr');
        } catch (Throwable) {
            return strtoupper($countryCode);
        }
    }

    private function geonamesUsername(): string
    {
        return trim((string) (
            $_ENV['GEONAMES_USERNAME']
            ?? $_SERVER['GEONAMES_USERNAME']
            ?? getenv('GEONAMES_USERNAME')
            ?: ''
        ));
    }

    private function nominatimEmail(): string
    {
        return trim((string) (
            $_ENV['NOMINATIM_EMAIL']
            ?? $_SERVER['NOMINATIM_EMAIL']
            ?? getenv('NOMINATIM_EMAIL')
            ?: 'contact@example.com'
        ));
    }

    private function useNominatimAutocomplete(): bool
    {
        $value = strtolower(trim((string) (
            $_ENV['GEO_USE_NOMINATIM_AUTOCOMPLETE']
            ?? $_SERVER['GEO_USE_NOMINATIM_AUTOCOMPLETE']
            ?? getenv('GEO_USE_NOMINATIM_AUTOCOMPLETE')
            ?: '0'
        )));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function appUserAgent(): string
    {
        return 'TrouveMoiGeoSearch/1.0 ' . $this->nominatimEmail();
    }

    private function appReferer(): string
    {
        return trim((string) (
            $_ENV['APP_URL']
            ?? $_SERVER['APP_URL']
            ?? getenv('APP_URL')
            ?: 'https://127.0.0.1:8000'
        ));
    }
}