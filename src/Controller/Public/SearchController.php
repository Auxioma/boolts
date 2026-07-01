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

use App\Entity\CategoryBienTransaction;
use App\Entity\Filter\ModalFilter;
use App\Entity\Search\PropertySearchSession;
use App\Entity\SearchBar\FilterCityCountry;
use App\Form\Filter\ModalFilterType;
use App\Form\SearchBar\FilterCityCountryType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        /*if (!$form->isSubmitted()) {
            return $this->redirectToRoute('app_home');
        }*/

        /*if (!$form->isValid()) {
            $this->addFlash('warning', 'Recherche invalide.');

            return $this->redirectToRoute('app_home');
        }*/

        $transactionType = $filter->getTransactionType();

        /**
         * On construit les critères une seule fois.
         *
         * Important :
         * Si selectedCityName est vide mais que selectedValue vaut "Le Havre",
         * alors criteria['ville'] vaudra bien "Le Havre".
         */
        $criteria = $this->buildCriteriaFromFilter($filter);

        $ville = $criteria['ville'];
        $cp = $criteria['cp'];
        $pays = $criteria['pays'];

        if (null === $transactionType || null === $pays) {
            $this->addFlash('warning', 'Veuillez sélectionner un type de transaction et un pays.');

            return $this->redirectToRoute('app_home');
        }

        $searchToken = bin2hex(random_bytes(16));

        $request->getSession()->set('property_search_'.$searchToken, $criteria);

        $sessionRecherche = new PropertySearchSession();
        $sessionRecherche->setUuid(Uuid::v7());
        $sessionRecherche->setTransactionTypeId($transactionType->getId());
        $sessionRecherche->setVille($ville);
        $sessionRecherche->setCp($cp);
        $sessionRecherche->setPays($pays);
        $sessionRecherche->setFilters([
            'uuid' => $sessionRecherche->getUuid()->toRfc4122(),
            'transactionTypeId' => $transactionType->getId(),
            'transactionType' => $transactionType->getName(),
            'filter' => $criteria['filter'],
            'selectedValue' => $criteria['selectedValue'],
            'ville' => $ville,
            'cp' => $cp,
            'pays' => $pays,
            'selectedCountryCode' => $criteria['selectedCountryCode'],
            'selectedRegionName' => $criteria['selectedRegionName'],
            'selectedLatitude' => $criteria['selectedLatitude'],
            'selectedLongitude' => $criteria['selectedLongitude'],
            'selectedFullAddress' => $criteria['selectedFullAddress'],
            'selectedMapboxId' => $criteria['selectedMapboxId'],
            'selectedFeatureType' => $criteria['selectedFeatureType'],
        ]);

        $this->entityManager->persist($sessionRecherche);
        $this->entityManager->flush();

        $response = $this->redirectToRoute('app_public_search_results', [
            'searchToken' => $searchToken,
            'view' => 'map',
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

        if (!\in_array($view, ['list', 'map'], true)) {
            $view = 'list';
        }

        $locale = $request->getLocale();

        $criteria = $request->getSession()->get('property_search_'.$searchToken);

        if (null === $criteria) {
            throw $this->createNotFoundException('Cette recherche est introuvable ou expirée.');
        }

        /**
         * Ici, on reconstruit l'objet FilterCityCountry
         * avec les valeurs sauvegardées en session grâce au token.
         */
        $filter = $this->buildFilterFromCriteria($criteria);

        /**
         * Le formulaire garde donc les anciennes valeurs.
         */
        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        $mapBounds = $this->getValidMapBoundsFromRequest($request);

        if ('map' === $view && null !== $mapBounds) {
            $queryBuilder = $this->propertyRepository->findBySearchAndMapBoundsQueryBuilder(
                $criteria['transactionTypeId'],
                $criteria['ville'],
                $criteria['cp'],
                $criteria['pays'],
                $locale,
                $mapBounds['north'],
                $mapBounds['south'],
                $mapBounds['east'],
                $mapBounds['west']
            );
        } else {
            $queryBuilder = $this->propertyRepository->findBySearchQueryBuilder(
                $criteria['transactionTypeId'],
                $criteria['ville'],
                $criteria['cp'],
                $criteria['pays'],
                $locale
            );
        }

        $search = $this->paginator->paginate(
            $queryBuilder,
            max(1, $request->query->getInt('page', 1)),
            8
        );

        /**
         * Filtre de recherche de la modal.
         */
        $filtreModal = new ModalFilter();

        $formModal = $this->createForm(ModalFilterType::class, $filtreModal, [
            'action' => $this->generateUrl('app_public_search_results', [
                'searchToken' => $searchToken,
                'view' => $view,
            ]),
            'method' => 'GET',
        ]);

        return $this->render('public/search/index.html.twig', [
            'form' => $form->createView(),
            'formModal' => $formModal->createView(),
            'search' => $search,
            'criteria' => $criteria,
            'searchToken' => $searchToken,
            'view' => $view,
            'mapBounds' => $mapBounds,

            'mapboxPublicToken' => $this->mapboxPublicToken,
            'mapboxPublicTokenCard' => $this->mapboxPublicTokenCard,

            'totalResults' => $search->getTotalItemCount(),
            'favoritePropertyIds' => [],
        ]);
    }

    #[Route('/public/search/{searchToken}/map-bounds', name: 'app_public_search_map_bounds', methods: ['GET'])]
    public function mapBounds(Request $request, string $searchToken): JsonResponse
    {
        $criteria = $request->getSession()->get('property_search_'.$searchToken);

        if (null === $criteria) {
            return $this->json([
                'success' => false,
                'message' => 'Cette recherche est introuvable ou expirée.',
            ], Response::HTTP_NOT_FOUND);
        }

        $mapBounds = $this->getValidMapBoundsFromRequest($request);

        if (null === $mapBounds) {
            return $this->json([
                'success' => false,
                'message' => 'Coordonnées de carte invalides.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $north = $mapBounds['north'];
        $south = $mapBounds['south'];
        $east = $mapBounds['east'];
        $west = $mapBounds['west'];

        $page = max(1, $request->query->getInt('page', 1));
        $locale = $request->getLocale();

        $queryBuilder = $this->propertyRepository->findBySearchAndMapBoundsQueryBuilder(
            $criteria['transactionTypeId'],
            $criteria['ville'],
            $criteria['cp'],
            $criteria['pays'],
            $locale,
            $north,
            $south,
            $east,
            $west
        );

        $search = $this->paginator->paginate(
            $queryBuilder,
            $page,
            12
        );

        return $this->json([
            'success' => true,
            'total' => $search->getTotalItemCount(),
            'page' => $page,
            'html' => $this->renderView('public/search/_cards.html.twig', [
                'search' => $search,
                'favoritePropertyIds' => [],
            ]),
            'pagination' => $this->renderView('public/search/_pagination.html.twig', [
                'search' => $search,
                'searchToken' => $searchToken,
                'currentView' => 'map',
                'mapBounds' => $mapBounds,
            ]),
        ]);
    }

    /**
     * Construit les critères de recherche à sauvegarder en session.
     *
     * Ces données permettent de recharger la recherche avec un token :
     * /public/search/{searchToken}
     *
     * @return array{
     *     transactionTypeId: int|null,
     *     filter: string|null,
     *     selectedValue: string|null,
     *     ville: string|null,
     *     cp: string|null,
     *     pays: string|null,
     *     selectedCountryCode: string|null,
     *     selectedRegionName: string|null,
     *     selectedLatitude: string|null,
     *     selectedLongitude: string|null,
     *     selectedFullAddress: string|null,
     *     selectedMapboxId: string|null,
     *     selectedFeatureType: string|null
     * }
     */
    private function buildCriteriaFromFilter(FilterCityCountry $filter): array
    {
        $transactionType = $filter->getTransactionType();

        $selectedValue = $this->cleanValue($filter->getSelectedValue());
        $selectedCityName = $this->cleanValue($filter->getSelectedCityName());
        $selectedPostalCode = $this->cleanValue($filter->getSelectedPostalCode());
        $selectedCountryName = $this->cleanValue($filter->getSelectedCountryName());

        /**
         * Correction importante.
         *
         * Cas Mapbox / Stimulus :
         * selectedCityName peut être vide.
         *
         * Exemple :
         * selectedValue: "Le Havre"
         * selectedCityName: null
         *
         * Dans ce cas, on veut bien enregistrer :
         * ville = "Le Havre"
         */
        $ville = $selectedCityName ?: $selectedValue;

        return [
            'transactionTypeId' => $transactionType?->getId(),

            /*
             * Champ visible du formulaire.
             */
            'filter' => $this->cleanValue($filter->getFilter()),

            /*
             * Valeur sélectionnée par ton Stimulus.
             */
            'selectedValue' => $selectedValue,

            /*
             * Valeurs principales utilisées par la recherche Doctrine.
             */
            'ville' => $ville,
            'cp' => $selectedPostalCode,
            'pays' => $selectedCountryName,

            /*
             * Valeurs cachées utiles pour recharger proprement le formulaire.
             */
            'selectedCountryCode' => $this->cleanValue($filter->getSelectedCountryCode()),
            'selectedRegionName' => $this->cleanValue($filter->getSelectedRegionName()),
            'selectedLatitude' => $this->cleanValue($filter->getSelectedLatitude()),
            'selectedLongitude' => $this->cleanValue($filter->getSelectedLongitude()),
            'selectedFullAddress' => $this->cleanValue($filter->getSelectedFullAddress()),
            'selectedMapboxId' => $this->cleanValue($filter->getSelectedMapboxId()),
            'selectedFeatureType' => $this->cleanValue($filter->getSelectedFeatureType()),
        ];
    }

    /**
     * Reconstruit l'objet FilterCityCountry depuis les critères stockés en session.
     */
    private function buildFilterFromCriteria(array $criteria): FilterCityCountry
    {
        $filter = new FilterCityCountry();

        $transactionType = null;

        if (!empty($criteria['transactionTypeId'])) {
            $transactionType = $this->entityManager->find(
                CategoryBienTransaction::class,
                $criteria['transactionTypeId']
            );
        }

        $filter->setTransactionType($transactionType);

        /*
         * Champ visible.
         */
        $filter->setFilter($criteria['filter'] ?? null);

        /*
         * Champs cachés.
         */
        $filter->setSelectedValue($criteria['selectedValue'] ?? null);
        $filter->setSelectedCityName($criteria['ville'] ?? null);
        $filter->setSelectedPostalCode($criteria['cp'] ?? null);
        $filter->setSelectedCountryName($criteria['pays'] ?? null);
        $filter->setSelectedCountryCode($criteria['selectedCountryCode'] ?? null);
        $filter->setSelectedRegionName($criteria['selectedRegionName'] ?? null);
        $filter->setSelectedLatitude($criteria['selectedLatitude'] ?? null);
        $filter->setSelectedLongitude($criteria['selectedLongitude'] ?? null);
        $filter->setSelectedFullAddress($criteria['selectedFullAddress'] ?? null);
        $filter->setSelectedMapboxId($criteria['selectedMapboxId'] ?? null);
        $filter->setSelectedFeatureType($criteria['selectedFeatureType'] ?? null);

        return $filter;
    }

    /**
     * Retourne les limites visibles de la carte Mapbox si elles sont présentes et valides.
     * Ces limites sont aussi conservées dans les liens de pagination avec ?page=.
     *
     * @return array{north: float, south: float, east: float, west: float}|null
     */
    private function getValidMapBoundsFromRequest(Request $request): ?array
    {
        $north = $request->query->get('north');
        $south = $request->query->get('south');
        $east = $request->query->get('east');
        $west = $request->query->get('west');

        if (
            null === $north
            || null === $south
            || null === $east
            || null === $west
            || !is_numeric($north)
            || !is_numeric($south)
            || !is_numeric($east)
            || !is_numeric($west)
        ) {
            return null;
        }

        return [
            'north' => (float) $north,
            'south' => (float) $south,
            'east' => (float) $east,
            'west' => (float) $west,
        ];
    }

    private function cleanValue(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = mb_trim((string) $value);

        if ('' === $value) {
            return null;
        }

        return $value;
    }
}
