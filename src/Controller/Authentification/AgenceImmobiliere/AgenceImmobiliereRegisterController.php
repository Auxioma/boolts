<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereRegisterController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/register-professionnelle',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_register'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/register.html.twig');
    }
}
