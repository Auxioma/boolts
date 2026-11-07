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

use App\Entity\User;
use App\Repository\FavorisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FavorisBienController extends AbstractController
{
    #[Route('/dashboard/visiteur/favoris/bien', name: 'app_dashboard_visiteur_favoris_bien')]
    #[IsGranted('ROLE_USER')]
    public function index(FavorisRepository $favorisRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return $this->render('dashboard/visiteur/favoris_bien/index.html.twig', [
            'properties' => $favorisRepository->findPublishedPropertiesByUser($user),
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
