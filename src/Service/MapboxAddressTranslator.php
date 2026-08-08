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

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MapboxAddressTranslator
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $mapboxToken,
    ) {
    }

    public function translateByMapboxId(
        string $mapboxId,
        string $sessionToken,
        string $locale,
    ): ?array {
        if ('' === mb_trim($mapboxId)) {
            return null;
        }

        $response = $this->httpClient->request(
            'GET',
            'https://api.mapbox.com/search/searchbox/v1/retrieve/'.urlencode($mapboxId),
            [
                'query' => [
                    'access_token' => $this->mapboxToken,
                    'session_token' => $sessionToken,
                    'language' => $locale,
                ],
            ]
        );

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode < 200 || $statusCode >= 300 || '' === mb_trim($content)) {
            dd([
                'status' => $statusCode,
                'body' => $content,
                'mapboxId' => $mapboxId,
                'locale' => $locale,
            ]);
        }

        $data = json_decode($content, true);

        if (!\is_array($data)) {
            return null;
        }

        $feature = $data['features'][0] ?? null;

        if (!$feature) {
            return null;
        }

        $properties = $feature['properties'] ?? [];
        $context = $properties['context'] ?? [];

        return [
            'adresse' => $properties['name'] ?? null,
            'fullAddress' => $properties['full_address'] ?? $properties['place_formatted'] ?? null,
            'ville' => $context['place']['name'] ?? $context['locality']['name'] ?? null,
            'pays' => $context['country']['name'] ?? null,
            'region' => $context['region']['name'] ?? null,
            'district' => $context['district']['name'] ?? null,
            'locality' => $context['locality']['name'] ?? null,
            'neighborhood' => $context['neighborhood']['name'] ?? null,
            'poi' => $context['poi']['name'] ?? null,
        ];
    }
}
