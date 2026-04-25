<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereLinkMotDePasse extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/lien-mots-de-passe-professionnelle',
            'en' => '/professional-login-aa'
        ],
        name: 'app_professionnelle_link_mot_de_passe'
    )]
    public function login(): Response
    {
        return $this->render('authentification/agence_immobiliere/link_mot_de_passe.html.twig');
    }
}
