<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurCodeRegisterontroller extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/verification-code',
            'en' => '/verify-code'
        ],
        name: 'app_visiteur_verification_code'
    )]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/code_verification.html.twig');
    }
}
