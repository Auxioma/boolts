<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpLocationService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    public function locate(?string $ip): ?array
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'], true)) {
            return null;
        }

$response = $this->httpClient->request('GET', 'http://ip-api.com/json/'.$ip, [
    'query' => [
        'fields' => 'status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,offset,currency,isp,org,as,asname,mobile,proxy,hosting,query',
        'lang' => 'en',
    ],
]);


        $data = $response->toArray(false);

        if (($data['status'] ?? null) !== 'success') {
            return null;
        }

        return [
            'ip' => $data['query'] ?? $ip,
            'pays' => $data['country'] ?? null,
            'codePays' => $data['countryCode'] ?? null,
            'region' => $data['regionName'] ?? null,
            'ville' => $data['city'] ?? null,
            'codePostal' => $data['zip'] ?? null,
            'latitude' => $data['lat'] ?? null,
            'longitude' => $data['lon'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'fournisseur' => $data['isp'] ?? null,
        ];
    }
}
