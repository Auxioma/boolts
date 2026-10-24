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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Shared login page for individual and professional accounts.
 */
final class LoginController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/login',
            'en' => '/login',
        ],
        name: 'app_login',
        defaults: ['loginType' => 'particulier']
    )]
    #[Route(
        path: [
            'fr' => '/fr/pro/login',
            'en' => '/pro/login',
        ],
        name: 'app_professionnelle_connexion',
        defaults: ['loginType' => 'professionnel']
    )]
    public function index(
        AuthenticationUtils $authenticationUtils,
        string $loginType,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectForRole();
        }

        return $this->render('authentification/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'loginType' => $loginType,
            'forgotPasswordRoute' => 'professionnel' === $loginType
                ? 'agence_immobiliereforgot_password_request'
                : 'app_forgot_password_request',
        ]);
    }

    private function redirectForRole(): Response
    {
        $user = $this->getUser();
        $roles = $user instanceof User ? $user->getRoles() : [];

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            return $this->redirectToRoute('admin');
        }

        if (\in_array('ROLE_AGENCE', $roles, true)) {
            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        return $this->redirectToRoute('app_visiteur_dashboard');
    }
}
