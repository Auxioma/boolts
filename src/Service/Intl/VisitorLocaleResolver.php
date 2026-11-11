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

namespace App\Service\Intl;

use App\Data\LocaleCountryCatalog;
use App\Entity\User;
use App\Service\GeoIpLocationService;
use App\Service\IpLocationService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Détermine la langue initiale ("fr" ou "en") d'un visiteur.
 *
 * Ordre de priorité :
 *  1. choix manuel (cookie boolts_locale) ;
 *  2. langue des préférences de l'utilisateur connecté ;
 *  3. pays détecté par IP (Cloudflare si proxy de confiance, MaxMind, puis ip-api en cache) ;
 *  4. en-tête Accept-Language ;
 *  5. langue par défaut du site.
 */
final readonly class VisitorLocaleResolver
{
    public const SUPPORTED = ['fr', 'en'];

    /** Choix manuel du visiteur (via le sélecteur de langue). */
    public const MANUAL_COOKIE = 'boolts_locale';

    /** Langue déjà déterminée automatiquement : on ne redirige plus ensuite. */
    public const AUTO_COOKIE = 'boolts_locale_auto';

    private const REQUEST_ATTRIBUTE = '_visitor_locale';

    public function __construct(
        private GeoIpLocationService $geoIpLocationService,
        private IpLocationService $ipLocationService,
        private Security $security,
        #[Autowire('%kernel.default_locale%')]
        private string $defaultLocale,
    ) {
    }

    public function manualChoice(Request $request): ?string
    {
        return $this->supported($request->cookies->get(self::MANUAL_COOKIE));
    }

    /**
     * @return array{locale: string, countryCode: ?string, countryLocale: ?string, source: string}
     */
    public function resolve(Request $request): array
    {
        $cached = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        if (\is_array($cached)) {
            return $cached;
        }

        [$countryCode, $regionCode] = $this->detectCountry($request);
        $countryLocale = LocaleCountryCatalog::localeFor($countryCode, $regionCode);

        if (null !== $locale = $this->manualChoice($request)) {
            $source = 'manual';
        } elseif (null !== $locale = $this->userLocale()) {
            $source = 'user';
        } elseif (null !== $locale = $countryLocale) {
            $source = 'ip';
        } elseif (null !== $locale = $this->acceptLanguage($request)) {
            $source = 'accept-language';
        } else {
            $locale = $this->supported($this->defaultLocale) ?? 'en';
            $source = 'default';
        }

        $result = [
            'locale' => $locale,
            'countryCode' => $countryCode,
            // Langue du pays détecté (null si ni francophone ni anglophone).
            'countryLocale' => $countryLocale,
            'source' => $source,
        ];

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $result);

        return $result;
    }

    /**
     * @return array{0: ?string, 1: ?string} [code pays ISO 3166-1, code région]
     */
    private function detectCountry(Request $request): array
    {
        // En-têtes Cloudflare pris en compte uniquement derrière un proxy de confiance.
        if ($request->isFromTrustedProxy()) {
            $country = $this->countryCode($request->headers->get('cf-ipcountry'));

            if (null !== $country) {
                return [$country, $request->headers->get('cf-region-code')];
            }
        }

        $ip = $request->getClientIp();

        if (null === $ip || false === filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE)) {
            return [null, null];
        }

        $geoIp = $this->geoIpLocationService->locateIp($ip);
        $country = ($geoIp['success'] ?? false) ? $this->countryCode($geoIp['countryCode'] ?? null) : null;

        // MaxMind ne fournit pas le code région : on complète via ip-api pour les pays bilingues.
        if (null !== $country && !isset(LocaleCountryCatalog::REGIONS[$country])) {
            return [$country, null];
        }

        $location = $this->ipLocationService->locate($ip);

        return [
            $this->countryCode($location['countryCode'] ?? null) ?? $country,
            $location['region'] ?? null,
        ];
    }

    private function userLocale(): ?string
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $this->supported($user->getLangues()?->getIso()) : null;
    }

    private function acceptLanguage(Request $request): ?string
    {
        if (!$request->headers->has('Accept-Language')) {
            return null;
        }

        return $this->supported($request->getPreferredLanguage(self::SUPPORTED));
    }

    private function countryCode(mixed $value): ?string
    {
        $code = strtoupper(trim((string) $value));

        // "XX" / "T1" : codes Cloudflare pour pays inconnu / Tor.
        return 1 === preg_match('/^[A-Z]{2}$/', $code) && 'XX' !== $code ? $code : null;
    }

    private function supported(mixed $locale): ?string
    {
        $locale = strtolower(substr(trim((string) $locale), 0, 2));

        return \in_array($locale, self::SUPPORTED, true) ? $locale : null;
    }
}
