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

namespace App\Twig;

use App\Service\Intl\VisitorLocaleResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * URL de la page courante dans l'autre langue (sélecteur de langue, hreflang, canonical).
 * Si la page n'existe pas dans la langue demandée, on renvoie l'accueil de cette langue.
 */
final class LocaleUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('locale_page_url', $this->pageUrl(...)),
            new TwigFunction('locale_switch_url', $this->switchUrl(...)),
            new TwigFunction('locale_alternate_urls', $this->alternateUrls(...)),
            new TwigFunction('locale_canonical_url', $this->canonicalUrl(...)),
        ];
    }

    /**
     * Page courante traduite dans $locale (chemin relatif, query string conservée).
     */
    public function pageUrl(string $locale, bool $withQuery = true): string
    {
        $request = $this->requestStack->getMainRequest();
        $url = $request ? $this->translatedPath($request, $locale) : null;

        if (null === $url) {
            return $this->urlGenerator->generate('app_home', ['_locale' => $locale]);
        }

        $query = $request->getQueryString();

        return $withQuery && null !== $query ? $url.'?'.$query : $url;
    }

    /**
     * Lien du sélecteur : enregistre le choix manuel puis renvoie vers la page traduite.
     */
    public function switchUrl(string $locale): string
    {
        return $this->urlGenerator->generate('app_locale_switch', [
            'target' => $locale,
            'redirect' => $this->pageUrl($locale),
        ]);
    }

    /**
     * URL absolues de la page courante dans chaque langue (balises hreflang).
     * Vide si la page n'est pas traduite dans toutes les langues.
     *
     * @return array<string, string>
     */
    public function alternateUrls(): array
    {
        $request = $this->requestStack->getMainRequest();

        if (null === $request) {
            return [];
        }

        $urls = [];

        foreach (VisitorLocaleResolver::SUPPORTED as $locale) {
            $path = $this->translatedPath($request, $locale);

            if (null === $path) {
                return [];
            }

            $urls[$locale] = $request->getSchemeAndHttpHost().$path;
        }

        return $urls;
    }

    public function canonicalUrl(): ?string
    {
        $request = $this->requestStack->getMainRequest();

        return $request ? $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo() : null;
    }

    private function translatedPath(Request $request, string $locale): ?string
    {
        // _canonical_route n'existe que pour les routes déclarées par langue.
        $route = $request->attributes->get('_canonical_route');

        if (!\is_string($route) || '' === $route) {
            return null;
        }

        $params = $request->attributes->get('_route_params', []);
        $params['_locale'] = $locale;

        try {
            return $this->urlGenerator->generate($route, $params);
        } catch (\Throwable) {
            return null;
        }
    }
}
