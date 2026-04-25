<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereLoginController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/connexion-professionnelle',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_connexion'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/login.html.twig');
    }
}
