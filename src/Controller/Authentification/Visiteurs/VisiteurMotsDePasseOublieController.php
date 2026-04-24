<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurMotsDePasseOublieController  extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/mot-de-passe-oublie',
            'en' => '/forgot-password'
        ],
        name: 'app_visiteur_mots_de_passe_oublie'
    )]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/visiteur_mots_de_passe_oublie.html.twig');
    }
}
