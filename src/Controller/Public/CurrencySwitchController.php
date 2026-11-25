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

namespace App\Controller\Public;

use App\Repository\DeviseRepository;
use App\Service\Intl\VisitorCurrencyResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Changement manuel de devise (modal du bandeau de la homepage).
 * Le choix est conservé dans un cookie lu en priorité par VisitorCurrencyResolver.
 */
final class CurrencySwitchController extends AbstractController
{
    private const COOKIE_LIFETIME = '+1 year';

    #[Route(
        path: '/devise/{code}',
        name: 'app_currency_switch',
        requirements: ['code' => '[A-Za-z]{3}'],
        methods: ['GET']
    )]
    public function switch(Request $request, string $code, DeviseRepository $deviseRepository): Response
    {
        $response = new RedirectResponse($this->safeRedirect($request));
        $devise = $deviseRepository->findOneBy(['code' => strtoupper($code)]);

        if (null !== $devise) {
            $response->headers->setCookie(
                Cookie::create(VisitorCurrencyResolver::MANUAL_COOKIE, (string) $devise->getCode())
                    ->withExpires(new \DateTimeImmutable(self::COOKIE_LIFETIME))
                    ->withSecure($request->isSecure())
                    ->withHttpOnly(true)
                    ->withSameSite(Cookie::SAMESITE_LAX)
            );
        }

        return $response;
    }

    /**
     * Uniquement un chemin interne (pas de redirection ouverte vers un autre domaine).
     */
    private function safeRedirect(Request $request): string
    {
        $redirect = (string) $request->query->get('redirect', '');

        if (1 === preg_match('#^/(?![/\\\\])#', $redirect)) {
            return $redirect;
        }

        return $this->generateUrl('app_root');
    }
}
