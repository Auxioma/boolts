<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Authentification;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Registration\RegistrationLocaleResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Reçoit les indices de langue / fuseau horaire du navigateur pendant le tunnel
 * d'inscription (l'utilisateur n'est pas encore authentifié via le pare-feu :
 * il est identifié par la clé de session « auth_user_id »).
 */
final class RegistrationLocaleController extends AbstractController
{
    #[Route('/inscription/preferences-locales', name: 'app_registration_locale', methods: ['POST'])]
    public function save(
        Request $request,
        UserRepository $userRepository,
        RegistrationLocaleResolver $registrationLocaleResolver,
    ): JsonResponse {
        $authUserId = $request->getSession()->get('auth_user_id');

        if (null === $authUserId) {
            return $this->json(['success' => false, 'message' => 'Aucune inscription en cours.'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            return $this->json(['success' => false, 'message' => 'Données invalides.'], 400);
        }

        if (!$this->isCsrfTokenValid('registration_locale', (string) ($data['_token'] ?? ''))) {
            return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 403);
        }

        $user = $userRepository->find($authUserId);

        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Utilisateur introuvable.'], 404);
        }

        $hints = array_filter(
            [
                'language' => $data['language'] ?? null,
                'locale' => $data['locale'] ?? null,
                'timeZone' => $data['timeZone'] ?? null,
            ],
            static fn (mixed $value): bool => \is_string($value) && '' !== $value,
        );

        $registrationLocaleResolver->apply($user, $hints);

        return $this->json(['success' => true]);
    }
}
