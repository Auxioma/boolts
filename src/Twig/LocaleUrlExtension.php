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

use App\Service\Intl\LocalizedUrlGenerator;
use App\Service\Intl\UrlCountryResolver;
use App\Service\Intl\VisitorLocaleResolver;
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
        private readonly LocalizedUrlGenerator $localizedUrlGenerator,
        private readonly UrlCountryResolver $urlCountryResolver,
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
     * Page courante traduite dans $locale (chemin relatif, query string conservée),
     * éventuellement pour un autre pays ($country, code ISO ; null = pays courant).
     */
    public function pageUrl(string $locale, bool $withQuery = true, ?string $country = null): string
    {
        $request = $this->requestStack->getMainRequest();
        $url = $request ? $this->localizedUrlGenerator->translatedPath($request, $locale, $country) : null;

        if (null === $url) {
            $params = ['_locale' => $locale];

            if (null !== $country) {
                $params[UrlCountryResolver::ROUTE_PARAM] = strtolower($country);
            }

            return $this->urlGenerator->generate('app_home', $params);
        }

        $query = $request->getQueryString();

        return $withQuery && null !== $query ? $url.'?'.$query : $url;
    }

    /**
     * Lien du sélecteur : enregistre le choix manuel puis renvoie vers la page traduite.
     */
    public function switchUrl(string $locale, ?string $country = null): string
    {
        return $this->urlGenerator->generate('app_locale_switch', [
            'target' => $locale,
            'redirect' => $this->pageUrl($locale, true, $country),
        ]);
    }

    /**
     * URL absolues de la page courante dans chaque langue, pour le pays courant
     * (balises hreflang, clés au format "fr-FR"). Vide si la page n'est pas
     * traduite dans toutes les langues.
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
            $path = $this->localizedUrlGenerator->translatedPath($request, $locale);

            if (null === $path) {
                return [];
            }

            $country = $this->urlCountryResolver->fromRoute($request);
            $hreflang = null !== $country ? $locale.'-'.strtoupper($country) : $locale;

            $urls[$hreflang] = $request->getSchemeAndHttpHost().$path;
        }

        return $urls;
    }

    public function canonicalUrl(): ?string
    {
        $request = $this->requestStack->getMainRequest();

        return $request ? $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo() : null;
    }
}
