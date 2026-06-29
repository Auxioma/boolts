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

use App\Entity\Search\PropertySearchSession;
use App\Entity\SearchBar\FilterCityCountry;
use App\Form\SearchBar\FilterCityCountryType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class SearchController extends AbstractController
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

    #[Route('/public/search', name: 'app_public_search', methods: ['POST'])]
    public function index(Request $request): Response
    {
        $filter = new FilterCityCountry();

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

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

        $sessionRecherche = new PropertySearchSession();
        $sessionRecherche->setUuid(Uuid::v7());
        $sessionRecherche->setTransactionTypeId($transactionType->getId());
        $sessionRecherche->setVille($ville);
        $sessionRecherche->setCp($cp);
        $sessionRecherche->setPays($pays);
        $sessionRecherche->setFilters([
            'uuid' => $sessionRecherche->getUuid()->toRfc4122(),
            'transactionType' => $transactionType->getName(),
            'ville' => $ville,
            'cp' => $cp,
            'pays' => $pays,
        ]);

        $this->entityManager->persist($sessionRecherche);
        $this->entityManager->flush();

        $response = $this->redirectToRoute('app_public_search_results', [
            'searchToken' => $searchToken,
        ]);

        $response->headers->setCookie(
            Cookie::create('property_search_token')
                ->withValue($sessionRecherche->getUuid()->toRfc4122())
                ->withExpires(new \DateTimeImmutable('+30 days'))
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX)
        );

        return $response;
    }

    #[Route('/public/search/{searchToken}', name: 'app_public_search_results', methods: ['GET'])]
    public function results(Request $request, string $searchToken): Response
    {
        $view = $request->query->get('view', 'list');

        if (!in_array($view, ['list', 'map'], true)) {
            $view = 'list';
        }

        $locale = $request->getLocale();

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
            $criteria['pays'],
            $locale
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
            'view' => $view,

            // Token Mapbox principal : autocomplete / recherche adresse
            'mapboxPublicToken' => $this->mapboxPublicToken,

            // Token Mapbox dédié à la carte des cards / résultats
            'mapboxPublicTokenCard' => $this->mapboxPublicTokenCard,

            'totalResults' => $search->getTotalItemCount(),
        ]);
    }
}