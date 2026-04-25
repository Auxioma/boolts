<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereFormulaireResetPassword extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/connexion-professionnelle.html',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_formulaire_reset_password'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/formulaire_reset_password.html.twig');
    }
}
