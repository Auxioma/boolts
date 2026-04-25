<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereFormRestPasswordController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/mot-de-pass-oublier-professionnelle',
            'en' => '/professional-forget-password'
        ],
        name: 'app_professionnelle_reset_password'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/rest_password.html.twig');
    }
}
