<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
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

final class FavorisBienController extends AbstractController
{
    #[Route('/dashboard/visiteur/favoris/bien', name: 'app_dashboard_visiteur_favoris_bien')]
    public function index(): Response
    {
        return $this->render('dashboard/visiteur/favoris_bien/index.html.twig', [
            'controller_name' => 'FavorisBienController',
        ]);
    }

    #[Route('/dashboard/visiteur/favoris/bien/intermediaire', name: 'app_dashboard_visiteur_favoris_bien_intermediaire')]
    public function intermediaire(): Response
    {
        return $this->render('dashboard/visiteur/favoris_bien/intermediaire.html.twig', [
            'controller_name' => 'FavorisBienController',
        ]);
    }

}
