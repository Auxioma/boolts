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
 * Devise locale du visiteur, déduite de son adresse IP (ip-api.com via IpLocationService,
 * mis en cache 24 h par IP). Résolue une seule fois par requête.
 *
 * Retourne null si l'IP est locale/privée, si l'API ne répond pas ou si la devise
 * n'existe pas dans la table `devise` : les prix restent alors dans la devise du bien.
 */
final class VisitorCurrencyResolver
{
    private bool $resolved = false;

    private ?Devise $devise = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly IpLocationService $ipLocationService,
        private readonly DeviseRepository $deviseRepository,
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

        $ip = $request->headers->get('cf-connecting-ip') ?: $request->getClientIp();
        $code = $this->ipLocationService->locate($ip)['currency'] ?? null;

        if (!\is_string($code) || '' === $code) {
            return null;
        }

        return $this->devise = $this->deviseRepository->findOneBy(['code' => mb_strtoupper($code)]);
    }
}
