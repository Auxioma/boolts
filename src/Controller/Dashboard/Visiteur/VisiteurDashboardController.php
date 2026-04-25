<?php

namespace App\Controller\Dashboard\Visiteur;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurDashboardController extends AbstractController
{
    #[Route('/visiteur/dashboard', name: 'app_visiteur_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/visiteur/dashboard/dashboard.html.twig', [
            'controller_name' => 'VisiteurDashboardController',
        ]);
    }
}
