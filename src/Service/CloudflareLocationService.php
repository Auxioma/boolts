<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CloudflareLocationService
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getLocation(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return [
                'ip' => null,
                'city' => null,
                'country' => null,
                'countryCode' => null,
                'region' => null,
                'regionCode' => null,
                'postalCode' => null,
                'latitude' => null,
                'longitude' => null,
                'timezone' => null,
                'isCloudflare' => false,
            ];
        }

        return [
            'ip' => $this->clean($request->headers->get('cf-connecting-ip')) ?? $request->getClientIp(),

            'city' => $this->clean($request->headers->get('cf-ipcity')),
            'countryCode' => $this->clean($request->headers->get('cf-ipcountry')),
            'continent' => $this->clean($request->headers->get('cf-ipcontinent')),

            'region' => $this->clean($request->headers->get('cf-region')),
            'regionCode' => $this->clean($request->headers->get('cf-region-code')),
            'postalCode' => $this->clean($request->headers->get('cf-postal-code')),

            'latitude' => $this->toFloat($request->headers->get('cf-iplatitude')),
            'longitude' => $this->toFloat($request->headers->get('cf-iplongitude')),

            'timezone' => $this->clean($request->headers->get('cf-timezone')),

            'isCloudflare' => $request->headers->has('cf-ray'),
        ];
    }

    public function getCity(): ?string
    {
        return $this->getLocation()['city'];
    }

    public function getCountryCode(): ?string
    {
        return $this->getLocation()['countryCode'];
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function toFloat(?string $value): ?float
    {
        $value = $this->clean($value);

        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
