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

namespace App\Controller\Public;

use App\Entity\Property;
use App\Repository\PropertyRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailBienController extends AbstractController
{
    #[Route('/public/detail/bien/{slug}', name: 'app_public_detail_bien')]
    public function index(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Property $property,
        PropertyRepository $propertyRepository): Response
    {
        /**
         * Je récupere des données pour les biens similaire.
         */
        $bienSimilaire = $propertyRepository->getBienSimilaire($property);

        return $this->render('public/detail_bien/index.html.twig', [
            'property' => $property,
        ]);
    }
}
