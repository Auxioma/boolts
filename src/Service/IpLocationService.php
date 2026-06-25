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

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpLocationService
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function locate(?string $ip): ?array
    {
        if (!$ip || \in_array($ip, ['127.0.0.1', '::1'], true)) {
            return null;
        }

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
