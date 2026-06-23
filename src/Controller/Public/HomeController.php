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
use App\Repository\CategoryBienTransactionRepository;
use App\Repository\PropertyRepository;
use App\Service\IpLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(MAPBOX_PUBLIC_TOKEN)%')]
        private readonly string $mapboxPublicToken,
        private readonly PropertyRepository $propertyRepository,
    ) {
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(
        CategoryBienTransactionRepository $categoryBienTransactionRepository,
        Request $request,
        IpLocationService $ipLocationService
    ): Response {

        /**
         * Je récupere l'IP de l'utilisateur pour localisé les biens
         */
        $ip = $request->getClientIp();
        $location = $ipLocationService->locate($ip);
dd($location['country'] ?? null);
        $transactions = $categoryBienTransactionRepository->findBy([], [
            'id' => 'ASC',
        ]);

        $filter = new FilterCityCountry();

        /*
         * transactionType est un EntityType.
         * Donc ici, on met directement l'objet CategoryBienTransaction,
         * pas un slug, pas un id.
         */
        if ([] !== $transactions) {
            $filter->setTransactionType($transactions[0]);
        }

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        /**
         * Logement plus populaire a paris filtré par le nombre de vue.
         */
        $logementPopulaire = $this->propertyRepository->logementPopulaire();

        /**
         * logement resament ajouter.
         */
        $logementAjouterResament = $this->propertyRepository->logemntRecementAjouter();

        return $this->render('public/home/index.html.twig', [
            'form' => $form->createView(),
            'transactions' => $transactions,
            'mapbox_public_token' => $this->mapboxPublicToken,
            'logementPopulaire' => $logementPopulaire,
            'logementAjouterResament' => $logementAjouterResament,
        ]);
    }
}
