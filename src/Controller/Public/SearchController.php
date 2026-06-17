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
        private readonly PropertyRepository $propertyRepository
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

        dd($filter);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectToRoute('app_home');
        }

        $transactionType = $filter->getTransactionType();
        $ville  = $filter->getSelectedCityName();
        $cp = $filter->getSelectedPostalCode();
        $pays = $filter->getSelectedCountryName();

        /** creation de la requete SQL pour affiché les bien immobilier */
        $search = $this->propertyRepository->findBySearch($transactionType, $ville, $cp, $pays);

        return $this->render('public/search/index.html.twig', [
            'form' => $form->createView(),
            'search' => $search,
        ]);
    }
}