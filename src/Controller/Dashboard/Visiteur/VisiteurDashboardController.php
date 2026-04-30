<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

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
