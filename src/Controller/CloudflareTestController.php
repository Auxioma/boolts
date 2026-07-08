<?php

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
