<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AgenceImmobiliereLoginController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/connexion-professionnelle',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_connexion'
    )]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_visiteur_dashboard');
        }

        return $this->render('authentification/agence_immobiliere/login.html.twig',
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError()
            ]
        );
    }
}
