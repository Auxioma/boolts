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

namespace App\Controller;

use App\Service\CloudflareLocationService;
use App\Service\GeoIpLocationService;
use App\Service\IpLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Attribute\WhenNot;
use Symfony\Component\HttpFoundation\Response;

/**
 * DEBUG (tests) : panneau affichant la localisation déduite de l'IP du visiteur.
 *
 * Rendu uniquement en environnement de développement via
 * {{ render(controller('App\\Controller\\DebugIpLocationController::panel')) }}
 * dans templates/base.html.twig. À retirer avant la mise en production.
 */
#[WhenNot('prod')]
class DebugIpLocationController extends AbstractController
{
    public function panel(
        Request $request,
        CloudflareLocationService $cloudflareLocation,
        GeoIpLocationService $geoIpLocationService,
        IpLocationService $ipLocationService,
    ): Response {
        $ip = $request->headers->get('cf-connecting-ip') ?: $request->getClientIp();

        $isPrivateOrLocal = null === $ip || false === filter_var(
            $ip,
            \FILTER_VALIDATE_IP,
            \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE
        );

        return $this->render('debug/_ip_location_panel.html.twig', [
            'ip' => $ip,
            'client_ip' => $request->getClientIp(),
            'is_private_or_local' => $isPrivateOrLocal,
            'headers' => [
                'cf-connecting-ip' => $request->headers->get('cf-connecting-ip'),
                'cf-ipcountry' => $request->headers->get('cf-ipcountry'),
                'x-forwarded-for' => $request->headers->get('x-forwarded-for'),
                'x-real-ip' => $request->headers->get('x-real-ip'),
                'forwarded' => $request->headers->get('forwarded'),
                'remote_addr' => $request->server->get('REMOTE_ADDR'),
            ],
            'cloudflare' => $cloudflareLocation->getLocation(),
            'geoip' => $geoIpLocationService->locateIp($ip),
            'ip_api' => $ipLocationService->locate($ip),
        ]);
    }
}
