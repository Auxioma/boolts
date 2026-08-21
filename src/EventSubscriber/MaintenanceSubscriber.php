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

use App\Service\MaintenanceManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MaintenanceManager $maintenanceManager,
        private readonly Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        /*
         * Ne traiter que la requête HTTP principale.
         *
         * Cela évite notamment de bloquer les sous-requêtes
         * générées par Symfony.
         */
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        /*
         * Certains chemins ne doivent jamais être bloqués.
         */
        if ($this->mustBypassMaintenance($request)) {
            return;
        }

        $settings = $this->maintenanceManager->getActiveSettings();

        /*
         * Maintenance désactivée.
         */
        if (null === $settings) {
            return;
        }

        /*
         * IP présente dans la liste blanche.
         */
        if ($this->maintenanceManager->isClientAllowed($request)) {
            return;
        }

        $content = $this->twig->render(
            'maintenance/index.html.twig',
            [
                'maintenance' => $settings,
            ]
        );

        $response = new Response(
            $content,
            Response::HTTP_SERVICE_UNAVAILABLE
        );

        /*
         * Important :
         * ne jamais mettre la page de maintenance en cache.
         */
        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, private'
        );

        /*
         * Évite l'indexation de la page maintenance.
         */
        $response->headers->set(
            'X-Robots-Tag',
            'noindex, nofollow, noarchive'
        );

        /*
         * Si une heure de fin est définie,
         * informer les clients HTTP du délai estimé.
         */
        if (null !== $settings->getEndsAt()) {
            $retryAfter = $settings->getEndsAt()->getTimestamp() - time();

            if ($retryAfter > 0) {
                $response->headers->set(
                    'Retry-After',
                    (string) $retryAfter
                );
            }
        }

        $event->setResponse($response);
    }

    private function mustBypassMaintenance(Request $request): bool
    {
        $path = $request->getPathInfo();

        /*
         * L'administration reste accessible.
         *
         * Elle est déjà protégée par ROLE_ADMIN dans security.yaml.
         *
         * Cela permet de désactiver la maintenance même si l'adresse
         * IP actuelle de l'administrateur n'est pas dans la whitelist.
         */
        if (
            '/admin' === $path
            || str_starts_with($path, '/admin/')
        ) {
            return true;
        }

        /*
         * Les assets doivent pouvoir être chargés afin que
         * la page maintenance conserve son graphisme.
         */
        $allowedPrefixes = [
            '/assets',
            '/build',
            '/bundles',
            '/_wdt',
            '/_profiler',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (
                $path === $prefix
                || str_starts_with($path, $prefix.'/')
            ) {
                return true;
            }
        }

        return false;
    }
}
