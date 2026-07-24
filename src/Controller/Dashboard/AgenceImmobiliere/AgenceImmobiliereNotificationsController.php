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

#[Route('/immobiliere/notifications', name: 'agence_immobiliere_')]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / AgenceImmobiliereNotificationsController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereNotificationsController extends AbstractController
{
    #[Route('/dashboard/agence/immobiliere/agence/immobiliere/notifications', name: 'notifications')]
    /**
     * Handles the index controller action.
     */
    public function index(): Response
    {
        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_notifications/index.html.twig', [
            'controller_name' => 'AgenceImmobiliereNotificationsController',
        ]);
    }
}
