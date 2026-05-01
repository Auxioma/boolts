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

#[Route('/immobiliere/options', name: 'agence_immobiliere_')]
final class AgenceImmobiliereOptionsController extends AbstractController
{
    #[Route('/', name: 'options')]
    public function index(): Response
    {
        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_options/index.html.twig', [
            'controller_name' => 'AgenceImmobiliereOptionsController',
        ]);
    }
}
