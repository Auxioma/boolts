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

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ForcedIpCityResolver
{
    public function __construct(
        private readonly GeoIpLocationService $geoIpLocationService,
        private readonly HttpClientInterface $httpClient,

        #[Autowire('%env(string:MAPBOX_PUBLIC_TOKEN)%')]
        private readonly string $mapboxToken,
    ) {
    }

    public function resolveFromRequest(Request $request): array
    {
        $ip = $request->headers->get('cf-connecting-ip')
            ?: $request->getClientIp();

        /**
         * 1. Cloudflare direct.
         */
        $cloudflareCity = $this->cleanCity($request->headers->get('cf-ipcity'));

        if ($cloudflareCity) {
            return [
                'success' => true,
                'ip' => $ip,
                'city' => $cloudflareCity,
                'finalCity' => $cloudflareCity,
                'source' => 'cloudflare',
                'isApproximate' => false,
                'message' => 'Ville récupérée depuis Cloudflare.',
            ];
        }

        return $this->resolveFromIp($ip);
    }

    public function resolveFromIp(?string $ip): array
    {
        if (!$ip || !$this->isValidPublicIp($ip)) {
            return $this->fallbackUnknown($ip, 'IP invalide, locale ou privée.');
        }

        /**
         * 2. MaxMind local.
         */
        $maxmind = $this->geoIpLocationService->locateIp($ip);

        if (!empty($maxmind['city'])) {
            return [
                'success' => true,
                'ip' => $ip,
                'city' => $maxmind['city'],
                'finalCity' => $maxmind['city'],
                'region' => $maxmind['region'] ?? null,
                'country' => $maxmind['country'] ?? null,
                'countryCode' => $maxmind['countryCode'] ?? null,
                'latitude' => $maxmind['latitude'] ?? null,
                'longitude' => $maxmind['longitude'] ?? null,
                'accuracyRadius' => $maxmind['accuracyRadius'] ?? null,
                'source' => 'maxmind',
                'isApproximate' => false,
                'message' => 'Ville récupérée depuis MaxMind.',
                'debug' => [
                    'maxmind' => $maxmind,
                ],
            ];
        }

        /**
         * 3. API ipapi.co.
         */
        $ipapi = $this->locateWithIpApi($ip);

        if (!empty($ipapi['city'])) {
            return [
                'success' => true,
                'ip' => $ip,
                'city' => $ipapi['city'],
                'finalCity' => $ipapi['city'],
                'region' => $ipapi['region'] ?? null,
                'country' => $ipapi['country'] ?? null,
                'countryCode' => $ipapi['countryCode'] ?? null,
                'latitude' => $ipapi['latitude'] ?? null,
                'longitude' => $ipapi['longitude'] ?? null,
                'source' => 'ipapi',
                'isApproximate' => true,
                'message' => 'Ville récupérée depuis ipapi.',
                'debug' => [
                    'maxmind' => $maxmind,
                    'ipapi' => $ipapi,
                ],
            ];
        }

        /**
         * 4. Mapbox reverse geocoding avec les coordonnées disponibles.
         */
        $latitude = $ipapi['latitude'] ?? $maxmind['latitude'] ?? null;
        $longitude = $ipapi['longitude'] ?? $maxmind['longitude'] ?? null;

        if ($latitude && $longitude) {
            $mapboxCity = $this->reverseGeocodeCityWithMapbox((float) $latitude, (float) $longitude);

            if ($mapboxCity) {
                return [
                    'success' => true,
                    'ip' => $ip,
                    'city' => $mapboxCity,
                    'finalCity' => $mapboxCity,
                    'region' => $ipapi['region'] ?? $maxmind['region'] ?? null,
                    'country' => $ipapi['country'] ?? $maxmind['country'] ?? null,
                    'countryCode' => $ipapi['countryCode'] ?? $maxmind['countryCode'] ?? null,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'source' => 'mapbox_reverse_geocoding',
                    'isApproximate' => true,
                    'message' => 'Ville approximative récupérée avec Mapbox depuis les coordonnées IP.',
                    'debug' => [
                        'maxmind' => $maxmind,
                        'ipapi' => $ipapi,
                    ],
                ];
            }
        }

        /**
         * 5. Dernier fallback : on force une valeur affichable.
         */
        $forcedCity = $ipapi['region']
            ?? $maxmind['region']
            ?? $ipapi['country']
            ?? $maxmind['country']
            ?? 'Ville inconnue';

        return [
            'success' => true,
            'ip' => $ip,
            'city' => $forcedCity,
            'finalCity' => $forcedCity,
            'region' => $ipapi['region'] ?? $maxmind['region'] ?? null,
            'country' => $ipapi['country'] ?? $maxmind['country'] ?? null,
            'countryCode' => $ipapi['countryCode'] ?? $maxmind['countryCode'] ?? null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'source' => 'forced_fallback',
            'isApproximate' => true,
            'message' => 'Aucune ville exacte trouvée. Une localisation approximative a été forcée.',
            'debug' => [
                'maxmind' => $maxmind,
                'ipapi' => $ipapi,
            ],
        ];
    }

    private function locateWithIpApi(string $ip): array
    {
        try {
            $response = $this->httpClient->request('GET', \sprintf('https://ipapi.co/%s/json/', $ip), [
                'timeout' => 5,
            ]);

            $data = $response->toArray(false);

            if (($data['error'] ?? false) === true) {
                return [
                    'success' => false,
                    'city' => null,
                    'message' => $data['reason'] ?? 'Erreur ipapi.',
                ];
            }

            return [
                'success' => true,
                'city' => $this->cleanCity($data['city'] ?? null),
                'region' => $this->cleanCity($data['region'] ?? null),
                'country' => $this->cleanCity($data['country_name'] ?? null),
                'countryCode' => $this->cleanCity($data['country_code'] ?? null),
                'postalCode' => $this->cleanCity($data['postal'] ?? null),
                'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
                'timezone' => $this->cleanCity($data['timezone'] ?? null),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'city' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function reverseGeocodeCityWithMapbox(float $latitude, float $longitude): ?string
    {
        if ('' === $this->mapboxToken) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.mapbox.com/search/geocode/v6/reverse', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'types' => 'place,locality',
                    'language' => 'fr',
                    'access_token' => $this->mapboxToken,
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray(false);

            foreach (($data['features'] ?? []) as $feature) {
                $featureType = $feature['properties']['feature_type'] ?? null;
                $name = $feature['properties']['name'] ?? null;

                if (
                    $name
                    && \in_array($featureType, ['place', 'locality'], true)
                ) {
                    return $this->cleanCity($name);
                }
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function cleanCity(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = rawurldecode($value);
        $value = mb_trim($value);

        return '' !== $value ? $value : null;
    }

    private function isValidPublicIp(string $ip): bool
    {
        return false !== filter_var(
            $ip,
            \FILTER_VALIDATE_IP,
            \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function fallbackUnknown(?string $ip, string $message): array
    {
        return [
            'success' => false,
            'ip' => $ip,
            'city' => 'Ville inconnue',
            'finalCity' => 'Ville inconnue',
            'source' => 'none',
            'isApproximate' => true,
            'message' => $message,
        ];
    }
}
