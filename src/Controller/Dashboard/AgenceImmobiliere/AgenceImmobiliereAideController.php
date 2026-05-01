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

namespace App\Controller\Dashboard\AgenceImmobiliere;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agence/aide', name: 'agence_immobiliere_')]
final class AgenceImmobiliereAideController extends AbstractController
{
    #[Route('/', name: 'aide')]
    public function index(): Response
    {
        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_aide/index.html.twig', [
            'controller_name' => 'AgenceImmobiliereAideController',
        ]);
    }
}
