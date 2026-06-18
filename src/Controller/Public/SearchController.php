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

use App\Entity\SearchBar\FilterCityCountry;
use App\Form\SearchBar\FilterCityCountryType;
use App\Repository\PropertyRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(MAPBOX_PUBLIC_TOKEN)%')]
        private readonly string $mapboxPublicToken,
        private readonly PropertyRepository $propertyRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('/public/search', name: 'app_public_search', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $filter = new FilterCityCountry();

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        /*if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('public/search/index.html.twig', [
                'form' => $form->createView(),
                'search' => null,
            ]);
        }*/

        $transactionType = $filter->getTransactionType();
        $ville = $filter->getSelectedCityName();
        $cp = $filter->getSelectedPostalCode();
        $pays = $filter->getSelectedCountryName();

        if (null === $transactionType || empty($pays)) {
            $this->addFlash('warning', 'Veuillez sélectionner un type de transaction et un pays.');

            return $this->redirectToRoute('app_home');
        }

        $searchToken = bin2hex(random_bytes(16));

        $request->getSession()->set('property_search_'.$searchToken, [
            'transactionTypeId' => $transactionType->getId(),
            'ville' => $ville,
            'cp' => $cp,
            'pays' => $pays,
        ]);

        return $this->redirectToRoute('app_public_search_results', [
            'searchToken' => $searchToken,
        ]);
    }

    #[Route('/public/search/{searchToken}', name: 'app_public_search_results', methods: ['GET'])]
    public function results(Request $request, string $searchToken): Response
    {
        $criteria = $request->getSession()->get('property_search_'.$searchToken);

        if (null === $criteria) {
            throw $this->createNotFoundException('Cette recherche est introuvable ou expirée.');
        }

        $filter = new FilterCityCountry();

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        $queryBuilder = $this->propertyRepository->findBySearchQueryBuilder(
            $criteria['transactionTypeId'],
            $criteria['ville'],
            $criteria['cp'],
            $criteria['pays']
        );

        $search = $this->paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('public/search/index.html.twig', [
            'form' => $form->createView(),
            'search' => $search,
            'criteria' => $criteria,
            'searchToken' => $searchToken,
        ]);
    }
}
