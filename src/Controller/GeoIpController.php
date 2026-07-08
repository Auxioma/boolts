<?php

namespace App\Controller;

use App\Service\GeoIpLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GeoIpController extends AbstractController
{
    #[Route('/debug/ip-location', name: 'app_debug_ip_location', methods: ['GET'])]
    public function locateCurrentVisitor(
        Request $request,
        GeoIpLocationService $geoIpLocationService
    ): JsonResponse {
        $ip = $request->headers->get('cf-connecting-ip')
            ?: $request->getClientIp();

        return $this->json([
            'ip_used' => $ip,
            'result' => $geoIpLocationService->locateIp($ip),
        ]);
    }

    #[Route('/debug/ip-location/{ip}', name: 'app_debug_ip_location_by_ip', methods: ['GET'])]
    public function locateSpecificIp(
        string $ip,
        GeoIpLocationService $geoIpLocationService
    ): JsonResponse {
        return $this->json([
            'ip_used' => $ip,
            'result' => $geoIpLocationService->locateIp($ip),
        ]);
    }
}
