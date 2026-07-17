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

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GeoIpLocationService
{
    private string $databasePath;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ) {
        $this->databasePath = $projectDir.'/var/geoip/GeoLite2-City.mmdb';
    }

    public function locateIp(?string $ip): array
    {
        if (!$ip) {
            return $this->error($ip, 'Aucune IP fournie.');
        }

        if (!$this->isValidPublicIp($ip)) {
            return $this->error($ip, 'IP invalide, locale ou privée.');
        }

        if (!file_exists($this->databasePath)) {
            return $this->error($ip, 'Base GeoLite2-City.mmdb introuvable dans var/geoip/.');
        }

        try {
            $reader = new Reader($this->databasePath, ['fr', 'en']);
            $record = $reader->city($ip);

            $city = $record->city->names['fr']
                ?? $record->city->names['en']
                ?? $record->city->name
                ?? null;

            $region = $record->mostSpecificSubdivision->names['fr']
                ?? $record->mostSpecificSubdivision->names['en']
                ?? $record->mostSpecificSubdivision->name
                ?? null;

            $country = $record->country->names['fr']
                ?? $record->country->names['en']
                ?? $record->country->name
                ?? null;

            return [
                'success' => true,

                'ip' => $ip,

                'city' => $city,
                'region' => $region,
                'country' => $country,
                'countryCode' => $record->country->isoCode ?? null,

                'postalCode' => $record->postal->code ?? null,
                'latitude' => $record->location->latitude ?? null,
                'longitude' => $record->location->longitude ?? null,
                'timezone' => $record->location->timeZone ?? null,
                'accuracyRadius' => $record->location->accuracyRadius ?? null,

                'hasCity' => null !== $city,
                'displayLocation' => $city ?: ($region ?: $country),
            ];
        } catch (AddressNotFoundException) {
            return $this->error($ip, 'Aucune localisation trouvée pour cette IP dans MaxMind.');
        } catch (\Throwable $e) {
            return $this->error($ip, $e->getMessage());
        }
    }

    private function isValidPublicIp(string $ip): bool
    {
        return false !== filter_var(
            $ip,
            \FILTER_VALIDATE_IP,
            \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function error(?string $ip, string $message): array
    {
        return [
            'success' => false,
            'ip' => $ip,

            'city' => null,
            'region' => null,
            'country' => null,
            'countryCode' => null,

            'postalCode' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => null,
            'accuracyRadius' => null,

            'hasCity' => false,
            'displayLocation' => null,
        ];
    }
}
