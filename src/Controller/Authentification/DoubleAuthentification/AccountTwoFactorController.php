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

namespace App\Controller\Authentification\DoubleAuthentification;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

// Décommente si tu veux vérifier CSRF en header
// use Symfony\Component\Security\Csrf\CsrfToken;
// use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/account/2fa')]
/**
 * HTTP controller for module Authentification / DoubleAuthentification / AccountTwoFactorController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
class AccountTwoFactorController extends AbstractController
{
    #[Route('/status', name: 'account_2fa_status', methods: ['GET'])]
    /**
     * Handles the status controller action.
     */
    public function status(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'enabled' => method_exists($user, 'isEmailAuthEnabled') ? $user->isEmailAuthEnabled() : false,
        ]);
    }

    #[Route('/toggle', name: 'account_2fa_toggle', methods: ['POST'])]
    /**
     * Handles the toggle controller action.
     */
    public function toggle(
        Request $request,
        EntityManagerInterface $em,
        // CsrfTokenManagerInterface $csrfTokenManager, // si CSRF
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Vérification CSRF (optionnelle, si activée côté firewall/form_login)
        // $csrfHeader = $request->headers->get('X-CSRF-TOKEN');
        // if (!$csrfHeader || !$csrfTokenManager->isTokenValid(new CsrfToken('toggle_2fa', $csrfHeader))) {
        //     return $this->json(['error' => 'invalid_csrf'], 419);
        // }

        $raw = $request->getContent() ?? '';
        if ('' === $raw) {
            // Souvent causé par une redirection (302 → GET) ou mauvais Content-Type
            return $this->json([
                'error' => 'empty_body',
                'hint' => 'Vérifie Content-Type application/json, credentials same-origin, et l’URL exacte sans slash final.',
            ], 400);
        }

        $data = json_decode($raw, true);
        if (\JSON_ERROR_NONE !== json_last_error() || !\is_array($data)) {
            return $this->json([
                'error' => 'invalid_json',
                'json_error' => json_last_error_msg(),
                'received' => $raw,
            ], 400);
        }

        // Conversion booléenne robuste: true/false, "true"/"false", 1/0, "1"/"0", on/off
        $enabledRaw = $data['enabled'] ?? null;
        $enabled = filter_var($enabledRaw, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
        if (null === $enabled) {
            return $this->json([
                'error' => 'invalid_enabled',
                'received' => $enabledRaw,
                'hint' => 'Envoie enabled: true/false (booléen) côté JS',
            ], 400);
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!method_exists($user, 'setEmailAuthEnabled')) {
            return $this->json(['error' => 'user_not_toggleable'], 400);
        }

        $user->setEmailAuthEnabled($enabled);
        $em->persist($user);
        $em->flush();

        return $this->json(['enabled' => $user->isEmailAuthEnabled()]);
    }
}
