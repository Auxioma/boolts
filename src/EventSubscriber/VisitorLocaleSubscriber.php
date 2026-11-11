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

namespace App\EventSubscriber;

use App\Service\Intl\LocalizedUrlGenerator;
use App\Service\Intl\VisitorLocaleResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Langue initiale du visiteur : à sa première visite (aucun cookie de langue),
 * la langue détectée est mémorisée et, si elle diffère de celle de l'URL, le
 * visiteur est redirigé (302, jamais 301) vers la même page dans sa langue.
 *
 * La détection n'a lieu qu'une fois : ensuite le visiteur navigue librement
 * dans les deux langues et un choix manuel n'est jamais écrasé.
 */
final readonly class VisitorLocaleSubscriber implements EventSubscriberInterface
{
    private const COOKIE_LIFETIME = '+1 year';

    private const PENDING_ATTRIBUTE = '_visitor_locale_pending_cookie';

    private const BOT_PATTERN = '/bot|crawl|spider|slurp|facebookexternalhit|embedly|preview|lighthouse|headless/i';

    public function __construct(
        private VisitorLocaleResolver $visitorLocaleResolver,
        private LocalizedUrlGenerator $localizedUrlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Après le routeur (32) et le pare-feu (8) : route et utilisateur connus.
            KernelEvents::REQUEST => ['onKernelRequest', 5],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || !$this->isEligible($request)) {
            return;
        }

        $locale = $this->visitorLocaleResolver->resolve($request)['locale'];

        if ($locale === $request->getLocale()) {
            $request->attributes->set(self::PENDING_ATTRIBUTE, $locale);

            return;
        }

        $url = $this->localizedUrlGenerator->translatedPath($request, $locale);

        if (null === $url) {
            $request->attributes->set(self::PENDING_ATTRIBUTE, $locale);

            return;
        }

        if (null !== $query = $request->getQueryString()) {
            $url .= '?'.$query;
        }

        $response = new RedirectResponse($url, RedirectResponse::HTTP_FOUND);
        $response->setPrivate();
        $response->headers->setCookie($this->autoCookie($locale, $request));

        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $locale = $event->getRequest()->attributes->get(self::PENDING_ATTRIBUTE);

        if ($event->isMainRequest() && \is_string($locale)) {
            $event->getResponse()->headers->setCookie($this->autoCookie($locale, $event->getRequest()));
        }
    }

    private function isEligible(Request $request): bool
    {
        if (!$request->isMethod('GET') || $request->isXmlHttpRequest() || $request->headers->has('Turbo-Frame')) {
            return false;
        }

        // Uniquement les pages déclarées dans les deux langues.
        if (!$this->localizedUrlGenerator->isLocalized($request)) {
            return false;
        }

        if ($request->cookies->has(VisitorLocaleResolver::MANUAL_COOKIE) || $request->cookies->has(VisitorLocaleResolver::AUTO_COOKIE)) {
            return false;
        }

        if (!str_contains((string) $request->headers->get('Accept'), 'text/html')) {
            return false;
        }

        // Les robots d'indexation voient chaque URL dans sa langue (SEO, pas de contenu dupliqué).
        return 1 !== preg_match(self::BOT_PATTERN, (string) $request->headers->get('User-Agent'));
    }

    private function autoCookie(string $locale, Request $request): Cookie
    {
        return Cookie::create(VisitorLocaleResolver::AUTO_COOKIE, $locale)
            ->withExpires(new \DateTimeImmutable(self::COOKIE_LIFETIME))
            ->withSecure($request->isSecure())
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }
}
