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
use App\Repository\Search\PropertySearchSessionRepository;
use App\Service\IpLocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class HomeController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(MAPBOX_PUBLIC_TOKEN)%')]
        private readonly string $mapboxPublicToken,
        private readonly PropertyRepository $propertyRepository,
        private readonly PropertySearchSessionRepository $propertySearchSessionRepository,
    ) {
    }

    #[Route(
        path: [
            'en' => '/',
            'fr' => '/fr',
        ],
        name: 'app_home',
        methods: ['GET']
    )]
    public function index(
        CategoryBienTransactionRepository $categoryBienTransactionRepository,
        Request $request,
        IpLocationService $ipLocationService,
    ): Response {
        /**
         * Je récupere l'IP de l'utilisateur pour localisé les biens.
         */
        $ip = $request->getClientIp();
        $location = $ipLocationService->locate($ip);

        if (null === $location) {
            $country = 'France';
        } else {
            $country = $location['country'];
        }
dd($country);
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
        $logementPopulaireVente = $this->propertyRepository->logementPopulaire($country, $request->getLocale(), '1');
        $logementAjouterRecementVente = $this->propertyRepository->logemntRecementAjouter($country, $request->getLocale(), '1');

        $logementPopulaireLocation = $this->propertyRepository->logementPopulaire($country, $request->getLocale(), '2');
        $logementAjouterRecementLocation = $this->propertyRepository->logemntRecementAjouter($country, $request->getLocale(), '2');
dd($logementPopulaireVente, $logementAjouterRecementVente, $logementPopulaireLocation, $logementAjouterRecementLocation);
        /**
         * je vais vérifier si l'utilisateur a un cookie de session pour retrouver ses recherches récentes.
         * Si le cookie existe, je vais récupérer l'UUID  et le nom de la ville de la recherche et je vais vérifier si la recherche existe dans la base de données.
         */
        $verificationCookie = $request->cookies->get('property_search_token');

        $lastSearchSession = null;

        if (null !== $verificationCookie && Uuid::isValid($verificationCookie)) {
            $lastSearchSession = $this->propertySearchSessionRepository->findOneBy([
                'uuid' => Uuid::fromString($verificationCookie),
            ])->getVille();
        }

        return $this->render('public/home/index.html.twig', [
            'form' => $form->createView(),
            'transactions' => $transactions,
            'mapbox_public_token' => $this->mapboxPublicToken,
            'logementPopulaireVente' => $logementPopulaireVente,
            'logementAjouterRecementVente' => $logementAjouterRecementVente,
            'logementPopulaireLocation' => $logementPopulaireLocation,
            'logementAjouterRecementLocation' => $logementAjouterRecementLocation,
            'lastSearchSession' => $lastSearchSession,
        ]);
    }
}
