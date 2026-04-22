<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurController extends AbstractController
{
    #[Route('/connexion-utilisateur', name: 'app_visiteur_connexion')]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/index.html.twig');
    }
}
