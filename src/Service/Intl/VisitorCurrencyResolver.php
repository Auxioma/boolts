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

use App\Entity\Devise;
use App\Repository\DeviseRepository;
use App\Service\IpLocationService;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Devise d'affichage du visiteur, résolue une seule fois par requête :
 *  1. devise choisie manuellement (cookie posé par CurrencySwitchController) ;
 *  2. sinon devise du pays de l'URL (/{pays}-{langue}/…, Pays::$devise) ;
 *  3. sinon devise déduite de l'adresse IP (ip-api.com via IpLocationService,
 *     mis en cache 24 h par IP).
 *
 * Retourne null si aucune devise n'est trouvée (IP locale/privée, API muette,
 * devise absente de la table `devise`) : les prix restent alors dans la devise du bien.
 */
final class VisitorCurrencyResolver
{
    /** Cookie du choix manuel de devise (code ISO 4217, ex. "EUR"). */
    public const MANUAL_COOKIE = 'boolts_currency';

    private bool $resolved = false;

    private ?Devise $devise = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly IpLocationService $ipLocationService,
        private readonly DeviseRepository $deviseRepository,
        private readonly UrlCountryResolver $urlCountryResolver,
    ) {
    }

    public function resolve(): ?Devise
    {
        if ($this->resolved) {
            return $this->devise;
        }

        $this->resolved = true;

        $request = $this->requestStack->getMainRequest();

        if (null === $request) {
            return null;
        }

        $manualCode = $request->cookies->get(self::MANUAL_COOKIE);

        if (\is_string($manualCode) && 1 === preg_match('/^[A-Za-z]{3}$/', $manualCode)) {
            $manualDevise = $this->deviseRepository->findOneBy(['code' => mb_strtoupper($manualCode)]);

            if (null !== $manualDevise) {
                return $this->devise = $manualDevise;
            }
        }

        return $this->devise = $this->automatic();
    }

    /**
     * Devise déduite du pays de l'URL, sinon de l'IP, sans tenir compte du choix
     * manuel (devise "suggérée" du bandeau de la homepage).
     */
    public function automatic(): ?Devise
    {
        $request = $this->requestStack->getMainRequest();

        if (null === $request) {
            return null;
        }

        $urlDevise = $this->urlCountryResolver->findPays($this->urlCountryResolver->fromRoute($request))?->getDevise();

        if (null !== $urlDevise) {
            return $urlDevise;
        }

        $ip = $request->headers->get('cf-connecting-ip') ?: $request->getClientIp();
        $code = $this->ipLocationService->locate($ip)['currency'] ?? null;

        if (!\is_string($code) || '' === $code) {
            return null;
        }

        return $this->deviseRepository->findOneBy(['code' => mb_strtoupper($code)]);
    }
}
