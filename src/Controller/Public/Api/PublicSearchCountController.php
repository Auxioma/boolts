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

namespace App\Controller\Public\Api;

use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PublicSearchCountController extends AbstractController
{
    #[Route('/public/search/count', name: 'app_public_search_count', methods: ['GET'])]
    public function __invoke(
        Request $request,
        PropertyRepository $propertyRepository,
    ): JsonResponse {
        /*
         * Comme ton formulaire Symfony est en GET,
         * les valeurs arrivent dans l'URL.
         *
         * Exemple :
         * /public/search/count?modal_filter[natureDeLaPropriete]=1
         */
        $filters = $this->extractFormFilters($request);

        // dd($filters);

        /*
         * On demande au repository de compter les biens
         * selon les filtres reçus.
         */
        $total = $propertyRepository->countForPublicSearch($filters);

        /*
         * Réponse envoyée à Stimulus.
         */
        return $this->json([
            'total' => $total,
        ]);
    }

    private function extractFormFilters(Request $request): array
    {
        $query = $request->query->all();

        /*
         * Cas Symfony classique :
         *
         * modal_filter[natureDeLaPropriete]
         * modal_filter[typeDePropriete][]
         * modal_filter[pays]
         * modal_filter[ville]
         * etc.
         *
         * Dans ce cas, on récupère directement le tableau intérieur.
         */
        foreach ($query as $value) {
            if (\is_array($value)) {
                return $value;
            }
        }

        /*
         * Sécurité :
         * si jamais les paramètres arrivent sans nom de formulaire,
         * on retourne directement toute la query.
         */
        return $query;
    }
}
