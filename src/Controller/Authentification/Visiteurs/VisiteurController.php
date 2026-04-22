<?php

namespace App\Controller\Authentification\Visiteurs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurController extends AbstractController
{
    #[Route('/login', name: 'app_make_controller')]
    public function index(): Response
    {
        return $this->render('authentification/visiteurs/index.html.twig', [
            'controller_name' => 'MakeControllerController',
        ]);
    }
}
