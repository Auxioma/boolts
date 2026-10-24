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

namespace App\Controller\Authentification\Visiteurs;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Authentification\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module Authentification / Visiteurs / VisiteurRequestNewCode.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class VisiteurRequestNewCode extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/request-new-code',
            'en' => '/request-new-code',
        ],
        name: 'app_auth_resend_code',
        defaults: ['registrationType' => 'particulier']
    )]
    #[Route(
        path: [
            'fr' => '/fr/pro/request-new-code',
            'en' => '/pro/request-new-code',
        ],
        name: 'app_professionnelle_resend_code',
        defaults: ['registrationType' => 'professionnel']
    )]
    /**
     * Handles the index controller action.
     */
    public function index(
        Request $request,
        UserRepository $userRepository,
        EmailVerificationService $emailVerificationService,
        string $registrationType,
    ): Response {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');
        $isProfessional = 'professionnel' === $registrationType;
        $verificationRoute = $isProfessional
            ? 'app_professionnelle_otp'
            : 'app_visiteur_verification_code';

        if (!$authUserId) {
            $this->addFlash('warning', 'Votre session a expiré. Veuillez recommencer.');

            return $this->redirectToRoute($verificationRoute);
        }

        $user = $userRepository->find($authUserId);

        if (!$user instanceof User) {
            $session->remove('auth_user_id');
            $session->remove('auth_step');

            $this->addFlash('danger', 'Utilisateur introuvable.');

            return $this->redirectToRoute($verificationRoute);
        }

        $lastRequestAt = $user->getEmailAuthCodeRequestedAt();

        if (
            $lastRequestAt instanceof \DateTimeImmutable
            && $lastRequestAt > new \DateTimeImmutable('-60 seconds')
        ) {
            $this->addFlash('warning', 'Veuillez patienter 60 secondes avant de demander un nouveau code.');

            return $this->redirectToRoute($verificationRoute);
        }

        $emailVerificationService->prepare($user);
        $emailVerificationService->send($user);

        $this->addFlash('success', 'Un nouveau code vous a été envoyé.');

        return $this->redirectToRoute($verificationRoute);
    }
}
