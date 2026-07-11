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

        $transactionType = $filter->getTransactionType();

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
        $locale = $request->getLocale();
        $view = $request->query->get('view', 'list');

        if (!\in_array($view, ['list', 'map'], true)) {
            $view = 'list';
        }

        $criteria = $request->getSession()->get('property_search_'.$searchToken);

        if (null === $criteria) {
            throw $this->createNotFoundException('Cette recherche est introuvable ou expirée.');
        }

        $filter = $this->buildFilterFromCriteria($criteria);

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        $filtreModal = new ModalFilter();

        /*
         * Préremplissage de la localisation de la modale (pays / ville / quartier)
         * à partir de la recherche Mapbox de la page d'accueil.
         *
         * Injecté via l'option "location_prefill" du FormType : les champs
         * cachés étant "mapped => false" avec une option "data", c'est le seul
         * canal fiable. On ne préremplit que si l'utilisateur n'a pas encore
         * soumis la modale : si "modal_filter" est présent dans l'URL,
         * ce sont ses choix qui priment.
         */
        $locationPrefill = $request->query->has('modal_filter')
            ? null
            : $this->buildModalLocationPrefill($criteria);

        $formModal = $this->createForm(ModalFilterType::class, $filtreModal, [
            'location_prefill' => $locationPrefill,
            'action' => $this->generateUrl('app_public_search_results', [
                'searchToken' => $searchToken,
                'view' => $view,
            ]),
            'method' => 'GET',
        ]);

        $formModal->handleRequest($request);

        $mapBounds = $this->getValidMapBoundsFromRequest($request);
        $modalFilter = $request->query->has('modal_filter')
            ? $request->query->all('modal_filter')
            : [];

        if ($request->query->has('modal_filter')) {
            $filters = $this->extractFormFilters($request);

            $properties = $this->propertyRepository->findForPublicSearch($filters, $locale);

            if ('map' === $view && null !== $mapBounds) {
                $properties = $this->filterPropertiesByMapBounds($properties, $mapBounds);
            }

            $paginationTarget = $properties;
        } else {
            if ('map' === $view && null !== $mapBounds) {
                $paginationTarget = $this->propertyRepository->findBySearchAndMapBoundsQueryBuilder(
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
                $paginationTarget = $this->propertyRepository->findBySearchQueryBuilder(
                    $criteria['transactionTypeId'],
                    $criteria['ville'],
                    $criteria['cp'],
                    $criteria['pays'],
                    $locale
                );
            }
        }

        $search = $this->paginator->paginate(
            $paginationTarget,
            max(1, $request->query->getInt('page', 1)),
            9
        );

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
            'modal_filter' => $modalFilter,
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

        $modalFilter = $request->query->has('modal_filter')
            ? $request->query->all('modal_filter')
            : [];

        if ($request->query->has('modal_filter')) {
            $filters = $this->extractFormFilters($request);

            $properties = $this->propertyRepository->findForPublicSearch($filters, $locale);
            $paginationTarget = $this->filterPropertiesByMapBounds($properties, $mapBounds);
        } else {
            $paginationTarget = $this->propertyRepository->findBySearchAndMapBoundsQueryBuilder(
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
            'html' => $this->renderView('public/search/_cards.html.twig', [
                'search' => $search,
                'favoritePropertyIds' => [],
            ]),
            'pagination' => $this->renderView('public/search/_pagination.html.twig', [
                'search' => $search,
                'searchToken' => $searchToken,
                'currentView' => 'map',
                'mapBounds' => $mapBounds,
                'modal_filter' => $modalFilter,
            ]),
        ]);
    }

    private function buildCriteriaFromFilter(FilterCityCountry $filter): array
    {
        $transactionType = $filter->getTransactionType();

        $selectedValue = $this->cleanValue($filter->getSelectedValue());
        $selectedCityName = $this->cleanValue($filter->getSelectedCityName());
        $selectedPostalCode = $this->cleanValue($filter->getSelectedPostalCode());
        $selectedCountryName = $this->cleanValue($filter->getSelectedCountryName());

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
     * Construit les valeurs JSON de préremplissage de la modale
     * (pays / ville / quartier) à partir des critères Mapbox stockés en session.
     *
     * Les champs cibles sont des HiddenType "mapped => false" : ils attendent
     * des chaînes JSON, exactement comme celles produites par syncHiddenFields()
     * du Stimulus "boolts-location". Ce dernier les lit au connect()
     * (parseJsonValue) et affiche automatiquement les puces sélectionnées.
     *
     * Correspondance des featureType Mapbox :
     *  - country                 → pays uniquement
     *  - region / postcode       → pays uniquement (une région n'est pas une ville)
     *  - district                → pays uniquement (district administratif Mapbox = département, PAS un quartier)
     *  - place / address / poi   → pays + ville
     *  - neighborhood / locality → pays + ville + quartier
     *
     * @return array{pays: string, ville: string, quartier: string}
     */
    private function buildModalLocationPrefill(array $criteria): array
    {
        $countryName = $this->cleanValue($criteria['pays'] ?? null);
        $countryCode = $this->cleanValue($criteria['selectedCountryCode'] ?? null);
        $cityName = $this->cleanValue($criteria['ville'] ?? null);
        $regionName = $this->cleanValue($criteria['selectedRegionName'] ?? null);
        $latitude = $this->cleanValue($criteria['selectedLatitude'] ?? null);
        $longitude = $this->cleanValue($criteria['selectedLongitude'] ?? null);
        $selectedValue = $this->cleanValue($criteria['selectedValue'] ?? null);
        $featureType = mb_strtolower((string) $this->cleanValue($criteria['selectedFeatureType'] ?? null));

        /*
         * GeoNames utilise des codes ISO2 en MAJUSCULES,
         * alors que Mapbox renvoie souvent "fr" en minuscules.
         */
        $countryCode = null !== $countryCode ? mb_strtoupper($countryCode) : null;

        /*
         * ================= PAYS =================
         * Structure attendue par getCountryLabel() / getCountryCode() du JS.
         */
        $countries = [];

        if (null !== $countryName || null !== $countryCode) {
            $countries[] = [
                'label' => $countryName ?? $countryCode,
                'code' => $countryCode ?? mb_strtoupper((string) $countryName),
                'country_code' => $countryCode ?? mb_strtoupper((string) $countryName),
                'country_name' => $countryName ?? $countryCode,
            ];
        }

        /*
         * ================= VILLE =================
         * Uniquement si la recherche Mapbox descend au moins au niveau ville.
         * lat/lng sont indispensables : le JS les renvoie ensuite à l'API
         * GeoNames (city_lat / city_lng) pour chercher les quartiers.
         */
        $cities = [];

        $isCityLevel = null !== $cityName
            && [] !== $countries
            && !\in_array($featureType, ['country', 'region', 'postcode', 'district'], true);

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
         * ================= QUARTIER =================
         * Seulement si Mapbox a identifié un quartier (neighborhood/locality)
         * et que sa valeur est différente du nom de la ville.
         */
        $districts = [];

        $isDistrictLevel = $isCityLevel
            && null !== $selectedValue
            && \in_array($featureType, ['neighborhood', 'locality'], true)
            && $this->normalizeLocationKey($selectedValue) !== $this->normalizeLocationKey($cityName);

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

        /*
         * Les HiddenType attendent des chaînes JSON (parseJsonValue côté JS).
         * JSON_UNESCAPED_UNICODE conserve les accents lisibles ("Genève" et non "Gen\u00e8ve").
         * Valeur vide = '[]' pour rester cohérent avec le "data" par défaut du FormType.
         */
        return [
            'pays' => json_encode($countries, \JSON_UNESCAPED_UNICODE) ?: '[]',
            'ville' => json_encode($cities, \JSON_UNESCAPED_UNICODE) ?: '[]',
            'quartier' => json_encode($districts, \JSON_UNESCAPED_UNICODE) ?: '[]',
        ];
    }

    /**
     * Normalisation identique à normalizeKey() du Stimulus boolts-location :
     * trim + minuscules + suppression des accents + espaces multiples.
     */
    private function normalizeLocationKey(?string $value): string
    {
        $value = mb_strtolower(mb_trim((string) $value));

        $transliterated = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')
            ?->transliterate($value);

        if (\is_string($transliterated)) {
            $value = $transliterated;
        }

        return preg_replace('/\s+/', ' ', $value) ?? $value;
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

    /**
     * Garde seulement les biens qui sont dans la zone visible de la carte.
     *
     * @param array<int, object> $properties
     * @param array{north: float, south: float, east: float, west: float} $mapBounds
     *
     * @return array<int, object>
     */
    private function filterPropertiesByMapBounds(array $properties, array $mapBounds): array
    {
        return array_values(array_filter($properties, function (object $property) use ($mapBounds): bool {
            if (
                !method_exists($property, 'getLatitude')
                || !method_exists($property, 'getLongitude')
            ) {
                return false;
            }

            $latitude = $property->getLatitude();
            $longitude = $property->getLongitude();

            if (null === $latitude || null === $longitude) {
                return false;
            }

            $latitude = (float) str_replace(',', '.', (string) $latitude);
            $longitude = (float) str_replace(',', '.', (string) $longitude);

            if ($latitude < $mapBounds['south'] || $latitude > $mapBounds['north']) {
                return false;
            }

            if ($mapBounds['west'] <= $mapBounds['east']) {
                return $longitude >= $mapBounds['west'] && $longitude <= $mapBounds['east'];
            }

            return $longitude >= $mapBounds['west'] || $longitude <= $mapBounds['east'];
        }));
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

    private function extractFormFilters(Request $request): array
    {
        if ($request->query->has('modal_filter')) {
            return $request->query->all('modal_filter');
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