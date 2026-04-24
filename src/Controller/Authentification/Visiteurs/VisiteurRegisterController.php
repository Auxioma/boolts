<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurRegisterController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/inscription-utilisateur',
            'en' => '/user-register'
        ],
        name: 'app_visiteur_inscription'
    )]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/inscription.html.twig');
    }
}
