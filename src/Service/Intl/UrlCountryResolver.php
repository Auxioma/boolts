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

use App\Entity\Pays;
use App\Repository\PaysRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Pays des URLs publiques (/{pays}-{langue}/…).
 *
 * Dans l'URL le pays est le code ISO 3166-1 alpha-2 en minuscules ("fr", "ma") ;
 * en base (Pays::$iso, Property::$codeIsoPays) il est en majuscules.
 * Seuls les pays présents dans la table `pays` sont acceptés.
 */
final class UrlCountryResolver
{
    /** Paramètre de route portant le pays. */
    public const ROUTE_PARAM = '_country';

    /** Contrainte de route du paramètre pays. */
    public const REQUIREMENT = '[a-z]{2}';

    /** @var array<string, ?Pays> */
    private array $memo = [];

    public function __construct(
        private readonly PaysRepository $paysRepository,
        private readonly VisitorLocaleResolver $visitorLocaleResolver,
        private readonly RequestStack $requestStack,
        #[Autowire('%app.default_country%')]
        private readonly string $defaultCountry,
    ) {
    }

    public function findPays(?string $country): ?Pays
    {
        $iso = strtoupper(trim((string) $country));

        if (1 !== preg_match('/^[A-Z]{2}$/', $iso)) {
            return null;
        }

        if (!\array_key_exists($iso, $this->memo)) {
            $this->memo[$iso] = $this->paysRepository->findOneBy(['iso' => $iso]);
        }

        return $this->memo[$iso];
    }

    public function exists(?string $country): bool
    {
        return null !== $this->findPays($country);
    }

    /**
     * Pays porté par la route de la requête (minuscules), ou null si la route n'en a pas.
     */
    public function fromRoute(Request $request): ?string
    {
        $country = $request->attributes->get(self::ROUTE_PARAM);

        return \is_string($country) && '' !== $country ? strtolower($country) : null;
    }

    /**
     * Pays du visiteur pour construire ses URLs : pays détecté par IP s'il est
     * connu en base, sinon pays par défaut du site (minuscules).
     */
    public function visitorCountry(Request $request): string
    {
        $detected = $this->visitorLocaleResolver->detectedCountryCode($request);

        return $this->exists($detected) ? strtolower((string) $detected) : $this->defaultCountry();
    }

    /**
     * Pays courant : celui de l'URL, sinon celui du visiteur, sinon le pays par défaut (minuscules).
     */
    public function current(): string
    {
        $request = $this->requestStack->getMainRequest();

        if (null === $request) {
            return $this->defaultCountry();
        }

        return $this->fromRoute($request) ?? $this->visitorCountry($request);
    }

    /**
     * Pays courant au format base de données (majuscules, ex. "FR").
     */
    public function currentIso(): string
    {
        return strtoupper($this->current());
    }

    public function defaultCountry(): string
    {
        return strtolower($this->defaultCountry);
    }
}
