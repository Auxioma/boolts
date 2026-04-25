<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereOptController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/opt',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_otp'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/otp.html.twig');
    }
}
