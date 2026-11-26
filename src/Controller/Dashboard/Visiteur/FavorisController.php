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

use App\Entity\Favoris;
use App\Entity\Property;
use App\Repository\FavorisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Controller\Traits\TranslatesMessages;

#[IsGranted('ROLE_USER')]
/**
 * HTTP controller for module Dashboard / Visiteur / FavorisController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
class FavorisController extends AbstractController
{
    use TranslatesMessages;

    #[Route(
        path: [
            'en' => '/favoris/property/{id}/toggle',
            'fr' => '/fr/favoris/property/{id}/toggle',
            'es' => '/es/favoris/property/{id}/toggle',
            'de' => '/de/favoris/property/{id}/toggle',
            'pt' => '/pt/favoris/property/{id}/toggle',
            'it' => '/it/favoris/property/{id}/toggle',
            'nl' => '/nl/favoris/property/{id}/toggle',
            'hu' => '/hu/favoris/property/{id}/toggle',
        ],
        name: 'app_favoris_property_toggle',
        methods: ['POST']
    )]
    /**
     * Handles the toggle controller action.
     */
    public function toggle(
        Property $property,
        Request $request,
        FavorisRepository $favorisRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        if ($this->isGranted('ROLE_AGENCE')) {
            return $this->json([
                'success' => false,
                'message' => $this->t('favorites.agency_forbidden'),
            ], 403);
        }

        $token = $request->headers->get('X-CSRF-TOKEN');

        if (!$this->isCsrfTokenValid('favorite_property_'.$property->getId(), $token)) {
            return $this->json([
                'success' => false,
                'message' => $this->t('api.error.invalid_csrf'),
            ], 400);
        }

        $user = $this->getUser();

        $favori = $favorisRepository->findOneBy([
            'user' => $user,
            'property' => $property,
        ]);

        if ($favori) {
            $entityManager->remove($favori);
            $favorited = false;
        } else {
            $favori = new Favoris();
            $favori->setUser($user);
            $favori->setProperty($property);

            $entityManager->persist($favori);
            $favorited = true;
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'favorited' => $favorited,
        ]);
    }
}
