<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller;

use App\Service\CloudflareLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CloudflareTestController extends AbstractController
{
    #[Route('/test/cloudflare/location', name: 'app_test_cloudflare_location')]
    public function index(CloudflareLocationService $cloudflareLocation): JsonResponse
    {
        return $this->json($cloudflareLocation->getLocation());
    }
}
