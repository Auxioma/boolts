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

namespace App\Routing;

use App\Service\Intl\UrlCountryResolver;

/**
 * Préfixe dynamique des URLs publiques : /{pays}-{langue}/… (ex. /fr-fr/, /ca-en/, /ma-fr/).
 *
 * Usage dans un contrôleur :
 *   #[Route(path: PublicRoute::PREFIX.'/agency/{slug}', name: '…', requirements: PublicRoute::REQUIREMENTS)]
 *
 * `_locale` et `_country` n'ont pas à être passés à path()/generateUrl() : ils sont
 * repris du contexte du routeur (langue et pays de la page courante).
 */
final class PublicRoute
{
    public const PREFIX = '/{'.UrlCountryResolver::ROUTE_PARAM.'}-{_locale}';

    /** Langues du site (cf. VisitorLocaleResolver::SUPPORTED). */
    public const LOCALE_REQUIREMENT = 'fr|en';

    public const REQUIREMENTS = [
        '_locale' => self::LOCALE_REQUIREMENT,
        UrlCountryResolver::ROUTE_PARAM => UrlCountryResolver::REQUIREMENT,
    ];
}
