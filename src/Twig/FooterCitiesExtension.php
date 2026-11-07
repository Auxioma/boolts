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

use App\Service\FooterCitiesProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * {{ footer_cities() }} → les 18 plus grandes villes du pays du visiteur, traduites selon la locale.
 */
final class FooterCitiesExtension extends AbstractExtension
{
    public function __construct(
        private readonly FooterCitiesProvider $footerCitiesProvider,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('footer_cities', $this->footerCitiesProvider->getCities(...)),
        ];
    }
}
