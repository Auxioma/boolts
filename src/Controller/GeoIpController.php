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

use App\Service\GeoIpLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module GeoIpController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
class GeoIpController extends AbstractController
{
    #[Route('/debug/ip-location', name: 'app_debug_ip_location', methods: ['GET'])]
    /**
     * Handles the locateCurrentVisitor controller action.
     */
    public function locateCurrentVisitor(
        Request $request,
        GeoIpLocationService $geoIpLocationService,
    ): JsonResponse {
        $ip = $request->headers->get('cf-connecting-ip')
            ?: $request->getClientIp();

        return $this->json([
            'ip_used' => $ip,
            'result' => $geoIpLocationService->locateIp($ip),
        ]);
    }

    #[Route('/debug/ip-location/{ip}', name: 'app_debug_ip_location_by_ip', methods: ['GET'])]
    /**
     * Handles the locateSpecificIp controller action.
     */
    public function locateSpecificIp(
        string $ip,
        GeoIpLocationService $geoIpLocationService,
    ): JsonResponse {
        return $this->json([
            'ip_used' => $ip,
            'result' => $geoIpLocationService->locateIp($ip),
        ]);
    }
}
