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

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Point d'entrée d'authentification du firewall principal.
 *
 * Un visiteur non authentifié qui demande une page du back-office (/admin…)
 * est redirigé vers la page de connexion EasyAdmin ; pour tout le reste du
 * site, il est redirigé vers la page de connexion visiteur habituelle.
 */
final class AppEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (str_starts_with($request->getPathInfo(), '/admin')) {
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_login')
            );
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login')
        );
    }
}
