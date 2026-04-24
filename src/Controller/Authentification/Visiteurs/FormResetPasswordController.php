<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FormResetPasswordController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/reinitialisation-mot-de-passe.html',
            'en' => '/reset-password.html'
        ],
        name: 'app_visiteur_reset_password_form',
    )]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/visiteur_reset_password_form.html.twig');
    }
}
