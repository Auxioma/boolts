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

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Les 18 plus grandes villes du pays du visiteur (pays déduit de l'adresse IP),
 * récupérées depuis OpenStreetMap (API Overpass) et traduites selon la locale.
 *
 * Overpass met souvent 30 à 60 s à répondre : la page ne l'attend JAMAIS.
 *  - l'affichage lit uniquement le cache ;
 *  - si le pays n'est pas encore en cache (ou si les données ont plus de 30 jours),
 *    l'appel OSM est fait après l'envoi de la réponse (kernel.terminate) ;
 *  - en attendant, on affiche la France si elle est en cache, sinon la liste statique.
 */
final class FooterCitiesProvider
{
    public const int LIMIT = 18;

    private const string OVERPASS_URL = 'https://overpass-api.de/api/interpreter';
    private const string DEFAULT_COUNTRY = 'FR';
    private const array LOCALES = ['fr', 'en'];
    private const int REFRESH_AFTER = 30 * 24 * 3600;
    private const int RETRY_AFTER = 3600;

    /** Liste d'origine du footer, utilisée tant qu'OSM n'a pas répondu. */
    private const array FALLBACK_CITIES = [
        'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes',
        'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille', 'Rennes', 'Reims',
        'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Nîmes', 'Le Havre',
    ];

    /** @var array<string, true> pays à (re)charger depuis OSM en fin de requête */
    private array $pendingCountries = [];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly GeoIpLocationService $geoIpLocationService,
        private readonly HttpClientInterface $httpClient,
        private readonly CacheItemPoolInterface $cacheApp,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @return list<string> noms des villes, dans la langue de la locale courante
     */
    public function getCities(): array
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'fr';

        $cities = $this->readCountry($this->resolveCountryCode())
            ?? $this->readCountry(self::DEFAULT_COUNTRY);

        if (null === $cities) {
            return self::FALLBACK_CITIES;
        }

        return array_map(
            static fn (array $names): string => $names[$locale] ?? $names['default'],
            $cities,
        );
    }

    /**
     * Appel OSM après l'envoi de la réponse au navigateur : le visiteur n'attend pas.
     */
    #[AsEventListener(event: KernelEvents::TERMINATE)]
    public function refreshPendingCountries(): void
    {
        if ([] === $this->pendingCountries) {
            return;
        }

        set_time_limit(180);

        foreach (array_keys($this->pendingCountries) as $countryCode) {
            $this->refreshCountry($countryCode);
        }

        $this->pendingCountries = [];
    }

    /**
     * Récupère (et met en cache) les villes d'un pays. Utilisable aussi depuis une commande.
     */
    public function refreshCountry(string $countryCode): bool
    {
        // Évite que plusieurs requêtes simultanées appellent OSM pour le même pays.
        $attempt = $this->cacheApp->getItem($this->cacheKey($countryCode).'_attempt');

        if ($attempt->isHit()) {
            return false;
        }

        $this->cacheApp->save($attempt->set(true)->expiresAfter(self::RETRY_AFTER));

        try {
            $cities = $this->fetchFromOverpass($countryCode);
        } catch (\Throwable $exception) {
            $this->logger->warning('Footer : impossible de récupérer les villes OpenStreetMap.', [
                'country' => $countryCode,
                'exception' => $exception,
            ]);

            return false;
        }

        if ([] === $cities) {
            return false;
        }

        // Pas d'expiration : on garde les anciennes données si OSM ne répond plus.
        $item = $this->cacheApp->getItem($this->cacheKey($countryCode));
        $this->cacheApp->save($item->set(['fetchedAt' => time(), 'cities' => $cities]));

        return true;
    }

    /**
     * @return list<array<string, string>>|null null si le pays n'est pas encore en cache
     */
    private function readCountry(string $countryCode): ?array
    {
        $item = $this->cacheApp->getItem($this->cacheKey($countryCode));
        $data = $item->isHit() ? $item->get() : null;

        if (!\is_array($data) || time() - ($data['fetchedAt'] ?? 0) > self::REFRESH_AFTER) {
            $this->pendingCountries[$countryCode] = true;
        }

        return \is_array($data) && [] !== ($data['cities'] ?? []) ? $data['cities'] : null;
    }

    /**
     * Pays du visiteur : en-tête Cloudflare, sinon base MaxMind, sinon France.
     */
    private function resolveCountryCode(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return self::DEFAULT_COUNTRY;
        }

        $cloudflareCountry = mb_strtoupper(mb_trim((string) $request->headers->get('cf-ipcountry')));

        // Cloudflare renvoie « XX » (inconnu) ou « T1 » (Tor) quand il ne sait pas.
        if (1 === preg_match('/^[A-Z]{2}$/', $cloudflareCountry) && !\in_array($cloudflareCountry, ['XX', 'T1'], true)) {
            return $cloudflareCountry;
        }

        $ip = $request->headers->get('cf-connecting-ip') ?: $request->getClientIp();
        $countryCode = $this->geoIpLocationService->locateIp($ip)['countryCode'] ?? null;

        return \is_string($countryCode) && 1 === preg_match('/^[A-Z]{2}$/', $countryCode)
            ? $countryCode
            : self::DEFAULT_COUNTRY;
    }

    /**
     * @return list<array<string, string>> pour chaque ville : ['default' => nom, 'fr' => ..., 'en' => ...]
     */
    private function fetchFromOverpass(string $countryCode): array
    {
        $nameColumns = implode(',', array_map(static fn (string $locale): string => '"name:'.$locale.'"', self::LOCALES));

        // Sortie CSV limitée aux colonnes utiles (la sortie JSON complète est énorme).
        $query = <<<OVERPASS
            [out:csv(name,{$nameColumns},population;false;"\t")][timeout:120];
            area["ISO3166-1"="{$countryCode}"]["admin_level"="2"]->.country;
            node["place"="city"]["population"](area.country);
            out tags;
            OVERPASS;

        $content = $this->httpClient->request('POST', self::OVERPASS_URL, [
            'body' => ['data' => $query],
            'headers' => ['User-Agent' => 'Boolts/1.0 (+https://boolts.com)'],
            'timeout' => 150,
            'max_duration' => 150,
        ])->getContent();

        $cities = [];

        foreach (preg_split('/\R/', mb_trim($content)) ?: [] as $line) {
            $columns = explode("\t", $line);
            $name = mb_trim($columns[0] ?? '');
            $population = (int) preg_replace('/\D/', '', $columns[\count(self::LOCALES) + 1] ?? '');

            if ('' === $name || $population <= 0) {
                continue;
            }

            $names = ['default' => $name];

            foreach (self::LOCALES as $index => $locale) {
                $localized = mb_trim($columns[$index + 1] ?? '');

                if ('' !== $localized) {
                    $names[$locale] = $localized;
                }
            }

            // Une même ville peut apparaître plusieurs fois : on garde la plus peuplée.
            if (!isset($cities[$name]) || $cities[$name]['population'] < $population) {
                $cities[$name] = ['population' => $population, 'names' => $names];
            }
        }

        usort($cities, static fn (array $a, array $b): int => $b['population'] <=> $a['population']);

        return array_map(
            static fn (array $city): array => $city['names'],
            \array_slice($cities, 0, self::LIMIT),
        );
    }

    private function cacheKey(string $countryCode): string
    {
        return 'footer_cities_osm_v2_'.$countryCode;
    }
}
