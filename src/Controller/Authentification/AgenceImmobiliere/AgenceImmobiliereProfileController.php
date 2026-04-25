<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereProfileController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/register-professionnelle-profile',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_profile'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/profile.html.twig');
    }
}
