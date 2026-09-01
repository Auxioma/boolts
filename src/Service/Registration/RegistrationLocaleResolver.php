<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Service\Registration;

use App\Entity\Devise;
use App\Entity\FuseauHoraire;
use App\Entity\LangueParler;
use App\Entity\Langues;
use App\Entity\Pays;
use App\Entity\User;
use App\Repository\FuseauHoraireRepository;
use App\Repository\LangueParlerRepository;
use App\Repository\LanguesRepository;
use App\Repository\PaysRepository;
use App\Service\CloudflareLocationService;
use App\Service\GeoIpLocationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Renseigne la langue parlée, la devise et le fuseau horaire d'un utilisateur
 * lors de la dernière étape de son inscription, à partir des données du
 * navigateur, des en-têtes Cloudflare, puis de la géolocalisation IP (MaxMind).
 *
 * Chaque champ n'est écrit que s'il est encore vide : un choix explicite de
 * l'utilisateur n'est jamais écrasé.
 */
final readonly class RegistrationLocaleResolver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LangueParlerRepository $langueParlerRepository,
        private LanguesRepository $languesRepository,
        private PaysRepository $paysRepository,
        private FuseauHoraireRepository $fuseauHoraireRepository,
        private CloudflareLocationService $cloudflareLocationService,
        private GeoIpLocationService $geoIpLocationService,
        private RequestStack $requestStack,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array{language?: mixed, locale?: mixed, timeZone?: mixed} $browserHints
     *        Indices explicites (endpoint beacon). À défaut, ceux du cookie
     *        boolts_locale_hints de la requête courante sont utilisés.
     */
    public function apply(User $user, array $browserHints = []): void
    {
        $browserHints += $this->cookieHints();

        $cloudflare = $this->cloudflareLocationService->getLocation();
        $geoIp = $this->geoIpLocationService->locateIp(
            \is_string($cloudflare['ip'] ?? null) ? $cloudflare['ip'] : null,
        );

        $languageCode = $this->firstNonEmpty(
            $this->languageFrom($browserHints['language'] ?? null),
            $this->languageFrom($browserHints['locale'] ?? null),
            $this->acceptLanguage(),
        );

        $countryCode = $this->firstNonEmpty(
            $this->countryFromLocale($browserHints['locale'] ?? null),
            $this->countryCode($cloudflare['countryCode'] ?? null),
            $this->countryCode(($geoIp['success'] ?? false) ? ($geoIp['countryCode'] ?? null) : null),
        );

        $timeZoneName = $this->firstNonEmpty(
            $this->timeZoneFrom($browserHints['timeZone'] ?? null),
            $this->timeZoneFrom($cloudflare['timezone'] ?? null),
            $this->timeZoneFrom(($geoIp['success'] ?? false) ? ($geoIp['timezone'] ?? null) : null),
        );

        $changed = $this->applySpokenLanguage($user, $languageCode);
        $changed = $this->applyCurrency($user, $countryCode) || $changed;
        $changed = $this->applyTimeZone($user, $timeZoneName) || $changed;

        if ($changed) {
            $this->entityManager->flush();
        }

        $this->logger->info('[REGISTRATION] Locale résolue pour l’utilisateur.', [
            'user' => $user->getId(),
            'language' => $languageCode,
            'country' => $countryCode,
            'timezone' => $timeZoneName,
            'cloudflare' => $cloudflare['isCloudflare'] ?? false,
            'geoip' => $geoIp['success'] ?? false,
            'persisted' => $changed,
        ]);
    }

    private function applySpokenLanguage(User $user, ?string $languageCode): bool
    {
        if (null === $languageCode) {
            return false;
        }

        $changed = false;

        if ($user->getLangueParlers()->isEmpty()) {
            $langueParler = $this->langueParlerRepository->findOneBy(['code' => $languageCode]);

            if ($langueParler instanceof LangueParler) {
                $user->addLangueParler($langueParler);
                $changed = true;
            }
        }

        if (null === $user->getLangues()) {
            $langue = $this->languesRepository->findOneBy(['iso' => $languageCode]);

            if ($langue instanceof Langues) {
                $user->setLangues($langue);
                $changed = true;
            }
        }

        return $changed;
    }

    private function applyCurrency(User $user, ?string $countryCode): bool
    {
        if (null !== $user->getDevise()) {
            return false;
        }

        $pays = $user->getPays();

        if (!$pays instanceof Pays && null !== $countryCode) {
            $pays = $this->paysRepository->findOneBy(['iso' => $countryCode]);

            if ($pays instanceof Pays && null === $user->getPays()) {
                $user->setPays($pays);
            }
        }

        $devise = $pays?->getDevise();

        if (!$devise instanceof Devise) {
            return false;
        }

        $user->setDevise($devise);

        return true;
    }

    private function applyTimeZone(User $user, ?string $timeZoneName): bool
    {
        if (null !== $user->getFuseauHoraire() || null === $timeZoneName) {
            return false;
        }

        $fuseauHoraire = $this->fuseauHoraireRepository->findOneBy(['nom' => $timeZoneName]);

        if (!$fuseauHoraire instanceof FuseauHoraire) {
            $fuseauHoraire = (new FuseauHoraire())->setNom($timeZoneName);
            $this->entityManager->persist($fuseauHoraire);
        }

        $user->setFuseauHoraire($fuseauHoraire);

        return true;
    }

    private function languageFrom(mixed $value): ?string
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        $language = mb_strtolower(trim(explode('-', str_replace('_', '-', $value))[0]));

        return preg_match('/^[a-z]{2,3}$/', $language) ? $language : null;
    }

    private function countryFromLocale(mixed $value): ?string
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        $parts = explode('-', str_replace('_', '-', trim($value)));

        // Le pays n'est fiable que si la locale porte une région (« fr-FR »).
        if (\count($parts) < 2) {
            return null;
        }

        return $this->countryCode(array_pop($parts));
    }

    private function countryCode(mixed $value): ?string
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        $country = mb_strtoupper(trim($value));

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : null;
    }

    private function timeZoneFrom(mixed $value): ?string
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        $value = trim($value);

        return \in_array($value, \DateTimeZone::listIdentifiers(), true) ? $value : null;
    }

    /**
     * @return array{language?: ?string, locale?: ?string, timeZone?: ?string}
     */
    private function cookieHints(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $raw = $request?->cookies->get('boolts_locale_hints');

        if (!\is_string($raw) || '' === $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (!\is_array($decoded)) {
            return [];
        }

        return array_filter(
            [
                'language' => \is_string($decoded['language'] ?? null) ? $decoded['language'] : null,
                'locale' => \is_string($decoded['locale'] ?? null) ? $decoded['locale'] : null,
                'timeZone' => \is_string($decoded['timeZone'] ?? null) ? $decoded['timeZone'] : null,
            ],
            static fn (?string $value): bool => null !== $value,
        );
    }

    private function acceptLanguage(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        foreach ($request->getLanguages() as $language) {
            $normalized = $this->languageFrom($language);

            if (null !== $normalized) {
                return $normalized;
            }
        }

        return null;
    }

    private function firstNonEmpty(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if (null !== $value && '' !== $value) {
                return $value;
            }
        }

        return null;
    }
}
