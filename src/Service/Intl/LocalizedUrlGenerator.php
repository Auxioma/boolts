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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Chemin de la page courante dans une autre langue, ou null si la page n'est
 * pas déclarée dans cette langue (route non localisée ou traduction absente).
 */
final readonly class LocalizedUrlGenerator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function isLocalized(Request $request): bool
    {
        $params = $request->attributes->get('_route_params', []);

        return \is_string($request->attributes->get('_route')) && \is_array($params) && isset($params['_locale']);
    }

    public function translatedPath(Request $request, string $locale): ?string
    {
        if (!$this->isLocalized($request)) {
            return null;
        }

        $params = $request->attributes->get('_route_params', []);
        $params['_locale'] = $locale;

        try {
            $path = $this->urlGenerator->generate((string) $request->attributes->get('_route'), $params);
        } catch (\Throwable) {
            return null;
        }

        // Route sans variante pour cette langue : Symfony ajoute alors ?_locale=…
        return str_contains($path, '_locale=') ? null : $path;
    }
}
