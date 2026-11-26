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

namespace App\Controller\Authentification;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Registration\RegistrationLocaleResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\Traits\TranslatesMessages;

/**
 * Reçoit les indices de langue / fuseau horaire du navigateur pendant le tunnel
 * d'inscription (l'utilisateur n'est pas encore authentifié via le pare-feu :
 * il est identifié par la clé de session « auth_user_id »).
 */
final class RegistrationLocaleController extends AbstractController
{
    use TranslatesMessages;

    #[Route(
        path: [
            'en' => '/inscription/preferences-locales',
            'fr' => '/fr/inscription/preferences-locales',
            'es' => '/es/inscription/preferences-locales',
            'de' => '/de/inscription/preferences-locales',
            'pt' => '/pt/inscription/preferences-locales',
            'it' => '/it/inscription/preferences-locales',
            'nl' => '/nl/inscription/preferences-locales',
            'hu' => '/hu/inscription/preferences-locales',
        ],
        name: 'app_registration_locale',
        methods: ['POST']
    )]
    public function save(
        Request $request,
        UserRepository $userRepository,
        RegistrationLocaleResolver $registrationLocaleResolver,
    ): JsonResponse {
        $authUserId = $request->getSession()->get('auth_user_id');

        if (null === $authUserId) {
            return $this->json(['success' => false, 'message' => $this->t('auth.flash.no_registration')], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            return $this->json(['success' => false, 'message' => $this->t('api.error.invalid_data')], 400);
        }

        if (!$this->isCsrfTokenValid('registration_locale', (string) ($data['_token'] ?? ''))) {
            return $this->json(['success' => false, 'message' => $this->t('api.error.invalid_csrf')], 403);
        }

        $user = $userRepository->find($authUserId);

        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => $this->t('auth.flash.user_not_found')], 404);
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
