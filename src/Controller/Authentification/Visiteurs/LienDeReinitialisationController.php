<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LienDeReinitialisationController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/reinitialisation-mot-de-passe',
            'en' => '/reset-password'
        ],
        name: 'app_visiteur_reset_password_request',
    )]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/reset_password_link.html.twig');
    }
}
