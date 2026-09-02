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

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Connexion / déconnexion du back-office EasyAdmin.
 *
 * Le formulaire est rendu par le template natif « @EasyAdmin/page/login.html.twig ».
 * Le POST est traité par App\Security\AdminAuthenticator (firewall "main").
 */
final class AdminSecurityController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        /*
         * Un administrateur déjà connecté n'a rien à faire sur la page de
         * connexion : on l'envoie directement sur le tableau de bord.
         */
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),

            // Titre affiché dans l'en-tête de la page de connexion.
            'page_title' => 'Boolts — Administration',

            // Jeton CSRF : doit correspondre à l'intention utilisée par
            // App\Security\AdminAuthenticator (CsrfTokenBadge('authenticate')).
            'csrf_token_intention' => 'authenticate',

            // Redirection après connexion (champ caché _target_path).
            'target_path' => $this->generateUrl('admin'),

            // Le formulaire poste sur cette même route.
            'action' => $this->generateUrl('admin_login'),

            // Noms des champs attendus par l'authentificateur.
            'username_parameter' => 'email',
            'password_parameter' => 'password',

            // Libellés en français.
            'username_label' => 'Adresse e-mail',
            'password_label' => 'Mot de passe',
            'sign_in_label' => 'Se connecter',

            // Pas de « mot de passe oublié » pour le back-office.
            'forgot_password_enabled' => false,

            // « Se souvenir de moi » géré par le firewall (always_remember_me).
            'remember_me_enabled' => false,
        ]);
    }
}
