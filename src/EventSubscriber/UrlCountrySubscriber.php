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

use App\Service\Intl\UrlCountryResolver;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;

/**
 * Pays des URLs publiques (/{pays}-{langue}/…).
 *
 *  - Un pays absent de la table `pays` donne une 404.
 *  - Le pays courant (celui de l'URL, sinon celui du visiteur) est placé dans le
 *    contexte du routeur : path()/generateUrl() le reprennent automatiquement,
 *    sans devoir passer `_country` à chaque appel (comme `_locale`).
 *  - En console (worker Messenger, commandes), le pays par défaut est utilisé.
 */
final readonly class UrlCountrySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlCountryResolver $urlCountryResolver,
        private RequestContext $requestContext,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Juste après le routeur (32) : les attributs de route sont connus.
            KernelEvents::REQUEST => ['onKernelRequest', 31],
            ConsoleEvents::COMMAND => ['onConsoleCommand', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $country = $this->urlCountryResolver->fromRoute($request);

        if (null !== $country && !$this->urlCountryResolver->exists($country)) {
            throw new NotFoundHttpException(\sprintf('Pays "%s" inconnu.', $country));
        }

        $this->requestContext->setParameter(
            UrlCountryResolver::ROUTE_PARAM,
            $country ?? $this->urlCountryResolver->visitorCountry($request),
        );
    }

    public function onConsoleCommand(): void
    {
        if (!$this->requestContext->hasParameter(UrlCountryResolver::ROUTE_PARAM)) {
            $this->requestContext->setParameter(UrlCountryResolver::ROUTE_PARAM, $this->urlCountryResolver->defaultCountry());
        }
    }
}
