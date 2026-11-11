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

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpLocationService
{
    /**
     * Durée de mise en cache d'une géolocalisation (évite un appel API par page).
     */
    private const CACHE_TTL = 86400;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function locate(?string $ip): ?array
    {
        if (!$ip || \in_array($ip, ['127.0.0.1', '::1'], true)) {
            return null;
        }

        // IP hachée dans la clé : l'adresse en clair n'est pas stockée en cache.
        return $this->cache->get('ip_location_'.hash('sha256', $ip), function (ItemInterface $item) use ($ip): ?array {
            $item->expiresAfter(self::CACHE_TTL);

            return $this->fetch($ip);
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetch(string $ip): ?array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                'http://ip-api.com/json/'.$ip,
                [
                    'query' => [
                        'fields' => implode(',', [
                            'status',
                            'message',
                            'continent',
                            'continentCode',
                            'country',
                            'countryCode',
                            'region',
                            'regionName',
                            'city',
                            'district',
                            'zip',
                            'lat',
                            'lon',
                            'timezone',
                            'offset',
                            'currency',
                            'isp',
                            'org',
                            'as',
                            'asname',
                            'mobile',
                            'proxy',
                            'hosting',
                            'query',
                        ]),
                        'lang' => 'en',
                    ],
                ]
            );

            $data = $response->toArray(false);

            if (($data['status'] ?? null) !== 'success') {
                return null;
            }

            return [
                'ip' => $data['query'] ?? null,

                'continent' => $data['continent'] ?? null,
                'continentCode' => $data['continentCode'] ?? null,

                'country' => $data['country'] ?? null,
                'countryCode' => $data['countryCode'] ?? null,

                'region' => $data['region'] ?? null,
                'regionName' => $data['regionName'] ?? null,

                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'zip' => $data['zip'] ?? null,

                'latitude' => $data['lat'] ?? null,
                'longitude' => $data['lon'] ?? null,

                'timezone' => $data['timezone'] ?? null,
                'offset' => $data['offset'] ?? null,

                'currency' => $data['currency'] ?? null,

                'isp' => $data['isp'] ?? null,
                'org' => $data['org'] ?? null,

                'as' => $data['as'] ?? null,
                'asname' => $data['asname'] ?? null,

                'mobile' => (bool) ($data['mobile'] ?? false),
                'proxy' => (bool) ($data['proxy'] ?? false),
                'hosting' => (bool) ($data['hosting'] ?? false),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
