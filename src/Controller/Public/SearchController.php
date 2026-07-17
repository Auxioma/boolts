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

    #[Route(
        '/public/search',
        name: 'app_public_search',
        methods: ['POST']
    )]
    public function index(Request $request): Response
    {
        $filter = new FilterCityCountry();

        $form = $this->createForm(
            FilterCityCountryType::class,
            $filter,
            [
                'action' => $this->generateUrl('app_public_search'),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        $transactionType = $filter->getTransactionType();

        $criteria = $this->buildCriteriaFromFilter($filter);

        $ville = $criteria['ville'];
        $cp = $criteria['cp'];
        $pays = $criteria['pays'];

        if (null === $transactionType || null === $pays) {
            $this->addFlash(
                'warning',
                'Veuillez sélectionner un type de transaction et un pays.'
            );

            return $this->redirectToRoute('app_home');
        }

        /*
         * Création d’un token temporaire permettant de retrouver
         * les critères de recherche dans la session.
         */
        $searchToken = bin2hex(random_bytes(16));

        $request->getSession()->set(
            'property_search_'.$searchToken,
            $criteria
        );

        /*
         * Enregistrement de la recherche en base de données.
         */
        $sessionRecherche = new PropertySearchSession();

        $sessionRecherche->setUuid(Uuid::v7());
        $sessionRecherche->setTransactionTypeId(
            $transactionType->getId()
        );
        $sessionRecherche->setVille($ville);
        $sessionRecherche->setCp($cp);
        $sessionRecherche->setPays($pays);

        $sessionRecherche->setFilters([
            'uuid' => $sessionRecherche
                ->getUuid()
                ->toRfc4122(),

            'transactionTypeId' => $transactionType->getId(),
            'transactionType' => $transactionType->getName(),

            'filter' => $criteria['filter'],
            'selectedValue' => $criteria['selectedValue'],

            'ville' => $ville,
            'cp' => $cp,
            'pays' => $pays,

            'selectedCountryCode' => $criteria[
            'selectedCountryCode'
            ],

            'selectedRegionName' => $criteria[
            'selectedRegionName'
            ],

            'selectedLatitude' => $criteria[
            'selectedLatitude'
            ],

            'selectedLongitude' => $criteria[
            'selectedLongitude'
            ],

            'selectedFullAddress' => $criteria[
            'selectedFullAddress'
            ],

            'selectedMapboxId' => $criteria[
            'selectedMapboxId'
            ],

            'selectedFeatureType' => $criteria[
            'selectedFeatureType'
            ],
        ]);

        $this->entityManager->persist($sessionRecherche);
        $this->entityManager->flush();

        /*
         * IMPORTANT :
         * La première vue affichée après la recherche est maintenant
         * obligatoirement la vue "list".
         */
        $response = $this->redirectToRoute(
            'app_public_search_results',
            [
                'searchToken' => $searchToken,
                'view' => 'list',
            ]
        );

        $response->headers->setCookie(
            Cookie::create('property_search_token')
                ->withValue(
                    $sessionRecherche
                        ->getUuid()
                        ->toRfc4122()
                )
                ->withExpires(
                    new \DateTimeImmutable('+30 days')
                )
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX)
        );

        return $response;
    }

    #[Route(
        '/public/search/{searchToken}',
        name: 'app_public_search_results',
        methods: ['GET']
    )]
    public function results(
        Request $request,
        string $searchToken,
    ): Response {
        $locale = $request->getLocale();

        /*
         * La vue par défaut est également "list" lorsqu’aucun
         * paramètre view n’est présent dans l’URL.
         */
        $view = $request->query->get('view', 'list');

        /*
         * Sécurité : seules les valeurs list et map sont autorisées.
         */
        if (!\in_array($view, ['list', 'map'], true)) {
            $view = 'list';
        }

        $criteria = $request
            ->getSession()
            ->get('property_search_'.$searchToken);

        if (null === $criteria) {
            throw $this->createNotFoundException('Cette recherche est introuvable ou expirée.');
        }

        $filter = $this->buildFilterFromCriteria($criteria);

        $form = $this->createForm(
            FilterCityCountryType::class,
            $filter,
            [
                'action' => $this->generateUrl(
                    'app_public_search'
                ),
                'method' => 'POST',
            ]
        );

        $filtreModal = new ModalFilter();

        /*
         * Préremplissage de la localisation de la modale
         * avec les données de la recherche principale.
         *
         * Lorsque modal_filter existe déjà dans l’URL,
         * les valeurs saisies dans la modale sont prioritaires.
         */
        $locationPrefill = $request
            ->query
            ->has('modal_filter')
            ? null
            : $this->buildModalLocationPrefill(
                $criteria
            );

        $formModal = $this->createForm(
            ModalFilterType::class,
            $filtreModal,
            [
                'location_prefill' => $locationPrefill,

                'action' => $this->generateUrl(
                    'app_public_search_results',
                    [
                        'searchToken' => $searchToken,
                        'view' => $view,
                    ]
                ),

                'method' => 'GET',
            ]
        );

        $formModal->handleRequest($request);

        $mapBounds = $this->getValidMapBoundsFromRequest(
            $request
        );

        $modalFilter = $request
            ->query
            ->has('modal_filter')
            ? $request
                ->query
                ->all('modal_filter')
            : [];

        /*
         * Recherche avec les filtres de la modale.
         */
        if ($request->query->has('modal_filter')) {
            $filters = $this->extractFormFilters($request);

            $properties = $this
                ->propertyRepository
                ->findForPublicSearch(
                    $filters,
                    $locale
                );

            /*
             * Les limites de carte ne sont appliquées
             * que lorsque la vue active est map.
             */
            if ('map' === $view && null !== $mapBounds) {
                $properties = $this
                    ->filterPropertiesByMapBounds(
                        $properties,
                        $mapBounds
                    );
            }

            $paginationTarget = $properties;
        } else {
            /*
             * Recherche limitée aux coordonnées visibles
             * uniquement lorsque la vue carte est active.
             */
            if ('map' === $view && null !== $mapBounds) {
                $paginationTarget = $this
                    ->propertyRepository
                    ->findBySearchAndMapBoundsQueryBuilder(
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
                /*
                 * Recherche classique pour la vue liste.
                 */
                $paginationTarget = $this
                    ->propertyRepository
                    ->findBySearchQueryBuilder(
                        $criteria['transactionTypeId'],
                        $criteria['ville'],
                        $criteria['cp'],
                        $criteria['pays'],
                        $locale
                    );
            }
        }

        /*
         * Pagination principale.
         */
        $search = $this->paginator->paginate(
            $paginationTarget,
            max(
                1,
                $request->query->getInt('page', 1)
            ),
            9
        );

        return $this->render(
            'public/search/index.html.twig',
            [
                'form' => $form->createView(),

                'formModal' => $formModal->createView(),

                'search' => $search,

                'criteria' => $criteria,

                'searchToken' => $searchToken,

                /*
                 * On transmet toujours la vue contrôlée.
                 */
                'view' => $view,

                'mapBounds' => $mapBounds,

                'mapboxPublicToken' => $this
                    ->mapboxPublicToken,

                'mapboxPublicTokenCard' => $this
                    ->mapboxPublicTokenCard,

                'totalResults' => $search
                    ->getTotalItemCount(),

                'favoritePropertyIds' => [],

                'modal_filter' => $modalFilter,
            ]
        );
    }

    #[Route(
        '/public/search/{searchToken}/map-bounds',
        name: 'app_public_search_map_bounds',
        methods: ['GET']
    )]
    public function mapBounds(
        Request $request,
        string $searchToken,
    ): JsonResponse {
        $criteria = $request
            ->getSession()
            ->get('property_search_'.$searchToken);

        if (null === $criteria) {
            return $this->json(
                [
                    'success' => false,

                    'message' => \sprintf(
                        '%s',
                        'Cette recherche est introuvable ou expirée.'
                    ),
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $mapBounds = $this->getValidMapBoundsFromRequest(
            $request
        );

        if (null === $mapBounds) {
            return $this->json(
                [
                    'success' => false,

                    'message' => \sprintf(
                        '%s',
                        'Coordonnées de carte invalides.'
                    ),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $north = $mapBounds['north'];
        $south = $mapBounds['south'];
        $east = $mapBounds['east'];
        $west = $mapBounds['west'];

        $page = max(
            1,
            $request->query->getInt('page', 1)
        );

        $locale = $request->getLocale();

        $modalFilter = $request
            ->query
            ->has('modal_filter')
            ? $request
                ->query
                ->all('modal_filter')
            : [];

        /*
         * Recherche filtrée depuis la modale.
         */
        if ($request->query->has('modal_filter')) {
            $filters = $this->extractFormFilters($request);

            $properties = $this
                ->propertyRepository
                ->findForPublicSearch(
                    $filters,
                    $locale
                );

            $paginationTarget = $this
                ->filterPropertiesByMapBounds(
                    $properties,
                    $mapBounds
                );
        } else {
            $paginationTarget = $this
                ->propertyRepository
                ->findBySearchAndMapBoundsQueryBuilder(
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
        }

        $search = $this->paginator->paginate(
            $paginationTarget,
            $page,
            12
        );

        return $this->json([
            'success' => true,

            'total' => $search->getTotalItemCount(),

            'page' => $page,

            'html' => $this->renderView(
                'public/search/_cards.html.twig',
                [
                    'search' => $search,

                    'favoritePropertyIds' => [],
                ]
            ),

            'pagination' => $this->renderView(
                'public/search/_pagination.html.twig',
                [
                    'search' => $search,

                    'searchToken' => $searchToken,

                    /*
                     * Cette route est exclusivement utilisée
                     * par la vue carte.
                     */
                    'currentView' => 'map',

                    'mapBounds' => $mapBounds,

                    'modal_filter' => $modalFilter,
                ]
            ),
        ]);
    }

    /**
     * Construit les critères de recherche à partir
     * du formulaire principal.
     *
     * @return array<string, mixed>
     */
    private function buildCriteriaFromFilter(
        FilterCityCountry $filter,
    ): array {
        $transactionType = $filter
            ->getTransactionType();

        $selectedValue = $this->cleanValue(
            $filter->getSelectedValue()
        );

        $selectedCityName = $this->cleanValue(
            $filter->getSelectedCityName()
        );

        $selectedPostalCode = $this->cleanValue(
            $filter->getSelectedPostalCode()
        );

        $selectedCountryName = $this->cleanValue(
            $filter->getSelectedCountryName()
        );

        /*
         * La ville sélectionnée est prioritaire.
         * selectedValue sert de valeur de secours.
         */
        $ville = $selectedCityName ?: $selectedValue;

        return [
            'transactionTypeId' => $transactionType?->getId(),

            /*
             * Champ visible du formulaire.
             */
            'filter' => $this->cleanValue(
                $filter->getFilter()
            ),

            /*
             * Valeur principale sélectionnée par Stimulus.
             */
            'selectedValue' => $selectedValue,

            /*
             * Valeurs utilisées par la recherche Doctrine.
             */
            'ville' => $ville,
            'cp' => $selectedPostalCode,
            'pays' => $selectedCountryName,

            /*
             * Données Mapbox utilisées pour reconstruire
             * le formulaire et préremplir la modale.
             */
            'selectedCountryCode' => $this->cleanValue(
                $filter->getSelectedCountryCode()
            ),

            'selectedRegionName' => $this->cleanValue(
                $filter->getSelectedRegionName()
            ),

            'selectedLatitude' => $this->cleanValue(
                $filter->getSelectedLatitude()
            ),

            'selectedLongitude' => $this->cleanValue(
                $filter->getSelectedLongitude()
            ),

            'selectedFullAddress' => $this->cleanValue(
                $filter->getSelectedFullAddress()
            ),

            'selectedMapboxId' => $this->cleanValue(
                $filter->getSelectedMapboxId()
            ),

            'selectedFeatureType' => $this->cleanValue(
                $filter->getSelectedFeatureType()
            ),
        ];
    }

    /**
     * Reconstruit le formulaire principal depuis
     * les critères stockés dans la session.
     */
    private function buildFilterFromCriteria(
        array $criteria,
    ): FilterCityCountry {
        $filter = new FilterCityCountry();

        $transactionType = null;

        if (!empty($criteria['transactionTypeId'])) {
            $transactionType = $this->entityManager->find(
                CategoryBienTransaction::class,
                $criteria['transactionTypeId']
            );
        }

        $filter->setTransactionType(
            $transactionType
        );

        /*
         * Champ visible.
         */
        $filter->setFilter(
            $criteria['filter'] ?? null
        );

        /*
         * Champs cachés.
         */
        $filter->setSelectedValue(
            $criteria['selectedValue'] ?? null
        );

        $filter->setSelectedCityName(
            $criteria['ville'] ?? null
        );

        $filter->setSelectedPostalCode(
            $criteria['cp'] ?? null
        );

        $filter->setSelectedCountryName(
            $criteria['pays'] ?? null
        );

        $filter->setSelectedCountryCode(
            $criteria['selectedCountryCode'] ?? null
        );

        $filter->setSelectedRegionName(
            $criteria['selectedRegionName'] ?? null
        );

        $filter->setSelectedLatitude(
            $criteria['selectedLatitude'] ?? null
        );

        $filter->setSelectedLongitude(
            $criteria['selectedLongitude'] ?? null
        );

        $filter->setSelectedFullAddress(
            $criteria['selectedFullAddress'] ?? null
        );

        $filter->setSelectedMapboxId(
            $criteria['selectedMapboxId'] ?? null
        );

        $filter->setSelectedFeatureType(
            $criteria['selectedFeatureType'] ?? null
        );

        return $filter;
    }

    /**
     * Construit les valeurs JSON permettant de préremplir
     * les champs pays, ville et quartier de la modale.
     *
     * @return array{
     *     pays: string,
     *     ville: string,
     *     quartier: string
     * }
     */
    private function buildModalLocationPrefill(
        array $criteria,
    ): array {
        $countryName = $this->cleanValue(
            $criteria['pays'] ?? null
        );

        $countryCode = $this->cleanValue(
            $criteria['selectedCountryCode'] ?? null
        );

        $cityName = $this->cleanValue(
            $criteria['ville'] ?? null
        );

        $regionName = $this->cleanValue(
            $criteria['selectedRegionName'] ?? null
        );

        $latitude = $this->cleanValue(
            $criteria['selectedLatitude'] ?? null
        );

        $longitude = $this->cleanValue(
            $criteria['selectedLongitude'] ?? null
        );

        $selectedValue = $this->cleanValue(
            $criteria['selectedValue'] ?? null
        );

        $featureType = mb_strtolower(
            (string) $this->cleanValue(
                $criteria['selectedFeatureType'] ?? null
            )
        );

        /*
         * GeoNames utilise généralement les codes ISO2
         * en lettres majuscules.
         */
        $countryCode = null !== $countryCode
            ? mb_strtoupper($countryCode)
            : null;

        /*
         * =========================
         * PAYS
         * =========================
         */
        $countries = [];

        if (
            null !== $countryName
            || null !== $countryCode
        ) {
            $countries[] = [
                'label' => $countryName
                    ?? $countryCode,

                'code' => $countryCode
                    ?? mb_strtoupper(
                        (string) $countryName
                    ),

                'country_code' => $countryCode
                    ?? mb_strtoupper(
                        (string) $countryName
                    ),

                'country_name' => $countryName
                    ?? $countryCode,
            ];
        }

        /*
         * =========================
         * VILLE
         * =========================
         */
        $cities = [];

        $isCityLevel = null !== $cityName
            && [] !== $countries
            && !\in_array(
                $featureType,
                [
                    'country',
                    'region',
                    'postcode',
                    'district',
                ],
                true
            );

        if ($isCityLevel) {
            $cities[] = [
                'city_name' => $cityName,

                'name' => $cityName,

                'country_code' => $countries[0]['code'],

                'country_name' => $countries[0]['label'],

                'admin_name_1' => $regionName ?? '',

                'lat' => $latitude ?? '',

                'lng' => $longitude ?? '',
            ];
        }

        /*
         * =========================
         * QUARTIER
         * =========================
         */
        $districts = [];

        $isDistrictLevel = $isCityLevel
            && null !== $selectedValue
            && \in_array(
                $featureType,
                [
                    'neighborhood',
                    'locality',
                ],
                true
            )
            && $this->normalizeLocationKey(
                $selectedValue
            ) !== $this->normalizeLocationKey(
                $cityName
            );

        if ($isDistrictLevel) {
            $districts[] = [
                'name' => $selectedValue,

                'district_name' => $selectedValue,

                'city_name' => $cityName,

                'country_code' => $countries[0]['code'],

                'country_name' => $countries[0]['label'],

                'lat' => $latitude ?? '',

                'lng' => $longitude ?? '',
            ];
        }

        return [
            'pays' => json_encode(
                $countries,
                \JSON_UNESCAPED_UNICODE
            ) ?: '[]',

            'ville' => json_encode(
                $cities,
                \JSON_UNESCAPED_UNICODE
            ) ?: '[]',

            'quartier' => json_encode(
                $districts,
                \JSON_UNESCAPED_UNICODE
            ) ?: '[]',
        ];
    }

    /**
     * Normalise une valeur de localisation pour permettre
     * une comparaison fiable.
     */
    private function normalizeLocationKey(
        ?string $value,
    ): string {
        $value = mb_strtolower(
            mb_trim((string) $value)
        );

        $transliterator = \Transliterator::create(
            'NFD; [:Nonspacing Mark:] Remove; NFC'
        );

        $transliterated = $transliterator
            ?->transliterate($value);

        if (\is_string($transliterated)) {
            $value = $transliterated;
        }

        return preg_replace(
            '/\s+/',
            ' ',
            $value
        ) ?? $value;
    }

    /**
     * Récupère les limites visibles de la carte.
     *
     * @return array{
     *     north: float,
     *     south: float,
     *     east: float,
     *     west: float
     * }|null
     */
    private function getValidMapBoundsFromRequest(
        Request $request,
    ): ?array {
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

    /**
     * Filtre les logements pour ne conserver que ceux
     * présents dans la zone visible de la carte.
     *
     * @param array<int, object> $properties
     * @param array{
     *     north: float,
     *     south: float,
     *     east: float,
     *     west: float
     * } $mapBounds
     *
     * @return array<int, object>
     */
    private function filterPropertiesByMapBounds(
        array $properties,
        array $mapBounds,
    ): array {
        return array_values(
            array_filter(
                $properties,
                static function (
                    object $property,
                ) use ($mapBounds): bool {
                    if (
                        !method_exists(
                            $property,
                            'getLatitude'
                        )
                        || !method_exists(
                            $property,
                            'getLongitude'
                        )
                    ) {
                        return false;
                    }

                    $latitude = $property
                        ->getLatitude();

                    $longitude = $property
                        ->getLongitude();

                    if (
                        null === $latitude
                        || null === $longitude
                    ) {
                        return false;
                    }

                    $latitude = (float) str_replace(
                        ',',
                        '.',
                        (string) $latitude
                    );

                    $longitude = (float) str_replace(
                        ',',
                        '.',
                        (string) $longitude
                    );

                    if (
                        $latitude < $mapBounds['south']
                        || $latitude > $mapBounds['north']
                    ) {
                        return false;
                    }

                    /*
                     * Cas classique : la carte ne traverse pas
                     * le méridien de changement de date.
                     */
                    if (
                        $mapBounds['west']
                        <= $mapBounds['east']
                    ) {
                        return $longitude
                            >= $mapBounds['west']
                            && $longitude
                            <= $mapBounds['east'];
                    }

                    /*
                     * Cas où la carte traverse la longitude 180°.
                     */
                    return $longitude
                        >= $mapBounds['west']
                        || $longitude
                        <= $mapBounds['east'];
                }
            )
        );
    }

    /**
     * Nettoie une valeur reçue depuis un formulaire.
     */
    private function cleanValue(
        mixed $value,
    ): ?string {
        if (null === $value) {
            return null;
        }

        $value = mb_trim(
            (string) $value
        );

        if ('' === $value) {
            return null;
        }

        return $value;
    }

    /**
     * Extrait les filtres de la modale depuis l’URL.
     *
     * @return array<string, mixed>
     */
    private function extractFormFilters(
        Request $request,
    ): array {
        if (
            $request->query->has('modal_filter')
        ) {
            return $request
                ->query
                ->all('modal_filter');
        }

        $query = $request->query->all();

        foreach ($query as $value) {
            if (\is_array($value)) {
                return $value;
            }
        }

        return $query;
    }
}
