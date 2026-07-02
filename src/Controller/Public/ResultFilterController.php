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

use App\Entity\Filter\ModalFilter;
use App\Entity\SearchBar\FilterCityCountry;
use App\Form\Filter\ModalFilterType;
use App\Form\SearchBar\FilterCityCountryType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResultFilterController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(MAPBOX_PUBLIC_TOKEN)%')]
        private readonly string $mapboxPublicToken,

        #[Autowire('%env(MAPBOX_PUBLIC_TOKEN_CARD)%')]
        private readonly string $mapboxPublicTokenCard,

        private readonly PropertyRepository $propertyRepository,
        private readonly PaginatorInterface $paginator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/filter', name: 'app_public_search_card')]
    public function index(Request $request): Response
    {
        $view = $request->query->get('view', 'list');

        if (!\in_array($view, ['list', 'map'], true)) {
            $view = 'list';
        }

        $locale = $request->getLocale();

        $filter = new FilterCityCountry();

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        /**
         * Je vaix rechercher les biens du filtre pour les affiché sur la card
         */
        $filters = $this->extractFormFilters($request);

        $queryBuilder = $this->propertyRepository->findForPublicSearch($filters);

        $search = $this->paginator->paginate(
            $queryBuilder,
            max(1, $request->query->getInt('page', 1)),
            8
        );

        $filtreModal = new ModalFilter();

        $formModal = $this->createForm(ModalFilterType::class, $filtreModal, [
            'action' => $this->generateUrl('app_public_search_card', [
                /*'searchToken' => $searchToken,
                'view' => $view,*/
            ]),
            'method' => 'GET',
        ]);

        return $this->render('public/search/index.html.twig', [
            'form' => $form->createView(),
            'formModal' => $formModal->createView(),
            'search' => $search,
            'view' => $view,
            'mapboxPublicToken' => $this->mapboxPublicToken,
            'mapboxPublicTokenCard' => $this->mapboxPublicTokenCard,
            'searchToken' => $searchToken,
            'totalResults' => $search->getTotalItemCount(),
            'favoritePropertyIds' => [],
        ]);
    }

    private function extractFormFilters(Request $request): array
    {
        $query = $request->query->all();

        foreach ($query as $value) {
            if (\is_array($value)) {
                return $value;
            }
        }

        return $query;
    }
}
