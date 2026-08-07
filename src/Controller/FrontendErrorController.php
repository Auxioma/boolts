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

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final class FrontendErrorController extends AbstractController
{
    #[Route('/_frontend-errors', name: 'app_frontend_error', methods: ['POST'])]
    public function report(
        Request $request,
        #[Autowire(service: 'monolog.logger.frontend')] LoggerInterface $logger,
        #[Autowire(service: 'limiter.frontend_error')] RateLimiterFactory $limiter,
    ): Response {
        if (!$this->isCsrfTokenValid('frontend_error', (string) $request->headers->get('X-CSRF-TOKEN'))) {
            return new JsonResponse(['message' => 'Jeton CSRF invalide.'], Response::HTTP_FORBIDDEN);
        }

        if (!$limiter->create($request->getClientIp() ?? 'unknown')->consume()->isAccepted()) {
            return new Response(status: Response::HTTP_TOO_MANY_REQUESTS);
        }

        $payload = json_decode($request->getContent(), true);

        if (!\is_array($payload) || JSON_ERROR_NONE !== json_last_error()) {
            return new JsonResponse(['message' => 'Payload JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $logger->error('Erreur frontend détectée.', [
            'type' => $this->string($payload, 'type', 32),
            'message' => $this->string($payload, 'message', 2_000),
            'source' => $this->string($payload, 'source', 2_000),
            'line' => $this->integer($payload, 'line'),
            'column' => $this->integer($payload, 'column'),
            'stack' => $this->string($payload, 'stack', 8_000),
            'page' => $this->string($payload, 'page', 2_000),
            'user_agent' => mb_substr((string) $request->headers->get('User-Agent'), 0, 1_000),
        ]);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function string(array $payload, string $key, int $maxLength): ?string
    {
        if (!isset($payload[$key]) || !\is_string($payload[$key])) {
            return null;
        }

        return mb_substr($payload[$key], 0, $maxLength);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function integer(array $payload, string $key): ?int
    {
        return \is_int($payload[$key] ?? null) ? $payload[$key] : null;
    }
}
