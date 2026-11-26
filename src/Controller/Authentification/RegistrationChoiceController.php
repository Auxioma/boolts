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

namespace App\Controller\Authentification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for the profile choice at the beginning of registration.
 */
final class RegistrationChoiceController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/signup-type',
            'en' => '/signup-type',
            'es' => '/es/signup-type',
            'de' => '/de/signup-type',
            'pt' => '/pt/signup-type',
            'it' => '/it/signup-type',
            'nl' => '/nl/signup-type',
            'hu' => '/hu/signup-type',
        ],
        name: 'app_registration_choice'
    )]
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('authentification/registration_choice.html.twig');
    }
}
