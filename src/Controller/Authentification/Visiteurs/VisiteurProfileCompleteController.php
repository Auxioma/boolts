<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurProfileCompleteController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/completez-votre-profil-utilisateur',
            'en' => '/user-profile-complete'
        ],
        name: 'app_visiteur_profile_complete'
    )]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/complete_profile_utilisateur.html.twig');
    }
}
