<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Dashboard\AgenceImmobiliere;

use App\Entity\AgencyNotification;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Filter\ModalFilter;
use App\Entity\Property;
use App\Entity\User;
use App\Form\Dashboard\AgenceImmobiliere\MesBiensType;
use App\Form\Filter\ModalFilterType;
use App\Repository\Booster\BoosterTransactionRepository;
use App\Repository\PropertyRepository;
use App\Service\Billing\AgencyPropertyQuotaCalculator;
use App\Service\Booster\BoostException;
use App\Service\Booster\PropertyBoostService;
use App\Service\MapboxAddressTranslator;
use App\Service\NumericSlugGenerator;
use App\Service\Property\AgencyPropertySubmissionMailer;
use App\Service\Property\PropertyNotificationLabeler;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mes/biens', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
final class AgenceImmobiliereMesBiensController extends AbstractController
{
    #[Route('/liste', name: 'mes_biens_list', methods: ['GET'])]
    public function list(
        PropertyRepository $propertyRepository,
        PaginatorInterface $paginator,
        Request $request,
        AgencyPropertyQuotaCalculator $agencyPropertyQuotaCalculator,
        BoosterTransactionRepository $boosterTransactionRepository,
        PropertyBoostService $propertyBoostService,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $search = mb_trim(
            $request->query->getString('search')
        );

        $sort = $request->query->getString(
            'sort',
            'p.updatedAt'
        );

        if (!\in_array($sort, PropertyRepository::MES_BIENS_SORTS, true)) {
            $sort = 'p.updatedAt';
        }

        $direction = mb_strtoupper(
            $request->query->getString('direction', 'DESC')
        );

        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        $filter = new ModalFilter();

        $filterForm = $this->createForm(
            ModalFilterType::class,
            $filter,
            [
                'action' => $this->generateUrl(
                    'agence_immobiliere_mes_biens_list'
                ),
                'method' => 'GET',
            ]
        );

        $filterForm->handleRequest($request);

        $filters = $request->query->has('modal_filter')
            ? $request->query->all('modal_filter')
            : [];

        $queryBuilder = $propertyRepository
            ->findPropertysByUserWithFiltersQuery(
                user: $user,
                search: '' !== $search ? $search : null,
                filters: $filters,
                sort: $sort,
                direction: $direction,
                locale: $request->getLocale(),
            );

        $properties = $paginator->paginate(
            $queryBuilder,
            max(
                1,
                $request->query->getInt('page', 1)
            ),
            10,
            [
                /*
                 * Le tri est géré manuellement par le repository via les
                 * paramètres "sort" / "direction". On déplace les paramètres
                 * de tri automatique du KnpPaginator pour l'empêcher
                 * d'ajouter un "ORDER BY p.views" (champ non mappé) et de
                 * lever "There is no such field [views]".
                 */
                'sortFieldParameterName' => '_sort',
                'sortDirectionParameterName' => '_direction',
            ]
        );

        /*
         * Identifie, parmi les annonces de la page courante, celles qui
         * disposent d'un boost actif afin d'afficher le badge "Boostée"
         * et de masquer le bouton "Booster".
         */
        $pageItems = $properties->getItems();

        if (!\is_array($pageItems)) {
            $pageItems = iterator_to_array($pageItems);
        }

        $pagePropertyIds = array_values(
            array_filter(
                array_map(
                    static fn (Property $property): ?int => $property->getId(),
                    $pageItems
                )
            )
        );

        $boostedPropertyIds = $propertyRepository->findBoostedPropertyIds(
            $pagePropertyIds
        );

        /*
         * Brouillons : affichés dans leur propre section.
         * Règle métier : 4 brouillons maximum par utilisateur, purge
         * automatique au bout de 3 mois (mail de prévenance 30 jours avant).
         */
        $drafts = $propertyRepository->findBy(
            [
                'user' => $user,
                'statut' => StatutAnnonceImmobiliere::BROUILLON,
            ],
            ['updatedAt' => 'DESC'],
            4
        );

        $quota = $agencyPropertyQuotaCalculator->calculate($user);
        $boostBalance = $boosterTransactionRepository
            ->countAvailableBySourceForAgency($user);
        $boostPreview = $propertyBoostService->preview($user);

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_mes_biens/list.html.twig',
            [
                'properties' => $properties,
                'drafts' => $drafts,
                'boostedPropertyIds' => $boostedPropertyIds,
                'annoncesRestantes' => $quota['remaining'],
                'boostsRestants' => $boostBalance['total'],
                'boostPreview' => $boostPreview,
                'filterForm' => $filterForm->createView(),
                'modal_filter' => $filters,
                'searchValue' => $search,
                'sortValue' => $sort,
                'directionValue' => $direction,
                'totalResults' => $properties->getTotalItemCount(),
            ]
        );
    }

    #[Route(
        '/liste/filtres/count',
        name: 'mes_biens_filters_count',
        methods: ['GET']
    )]
    public function filtersCount(
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                [
                    'count' => 0,
                    'total' => 0,
                    'totalResults' => 0,
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $filters = $request->query->has('modal_filter')
            ? $request->query->all('modal_filter')
            : [];

        $properties = $propertyRepository
            ->findPropertysByUserWithFiltersQuery(
                user: $user,
                search: null,
                filters: $filters,
                locale: $request->getLocale(),
            )
            ->getQuery()
            ->getResult();

        $count = \count($properties);

        return $this->json([
            'count' => $count,
            'total' => $count,
            'totalResults' => $count,
        ]);
    }

    /**
     * Auto-complétion du filtre « Localisation » (modale de filtres de la
     * page « Mes biens ») : les suggestions proviennent uniquement des pays
     * réellement saisis par l'agence sur ses propres annonces.
     */
    #[Route(
        '/liste/filtres/pays',
        name: 'mes_biens_filter_countries',
        methods: ['GET']
    )]
    public function filterCountries(
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['results' => []],
                Response::HTTP_FORBIDDEN
            );
        }

        $query = mb_trim($request->query->getString('q'));

        $results = [];

        foreach (
            $propertyRepository->findAgencyFilterCountries(
                $user,
                '' !== $query ? $query : null,
                $request->getLocale()
            ) as $name
        ) {
            $code = $this->resolveCountryCode($name) ?? mb_strtoupper($name);

            $results[] = [
                'label' => $name,
                'name' => $name,
                'country_name' => $name,
                'code' => $code,
                'country_code' => $code,
                'display_name' => $name,
            ];
        }

        return $this->json(['results' => $results]);
    }

    /**
     * Auto-complétion des villes saisies par l'agence, restreinte au pays
     * éventuellement sélectionné dans la modale de filtres.
     */
    #[Route(
        '/liste/filtres/villes',
        name: 'mes_biens_filter_cities',
        methods: ['GET']
    )]
    public function filterCities(
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['results' => []],
                Response::HTTP_FORBIDDEN
            );
        }

        $query = mb_trim($request->query->getString('q'));
        $countryName = mb_trim($request->query->getString('country_name'));

        $results = [];

        foreach (
            $propertyRepository->findAgencyFilterCities(
                $user,
                '' !== $query ? $query : null,
                '' !== $countryName ? $countryName : null,
                $request->getLocale()
            ) as $ville
        ) {
            $results[] = [
                'city_name' => $ville,
                'name' => $ville,
                'label' => $ville,
                'country_name' => $countryName,
                'display_name' => '' !== $countryName
                    ? $ville.' — '.$countryName
                    : $ville,
            ];
        }

        return $this->json(['results' => $results]);
    }

    /**
     * Auto-complétion des quartiers saisis par l'agence, restreinte à la
     * ville éventuellement sélectionnée dans la modale de filtres.
     */
    #[Route(
        '/liste/filtres/quartiers',
        name: 'mes_biens_filter_districts',
        methods: ['GET']
    )]
    public function filterDistricts(
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['results' => []],
                Response::HTTP_FORBIDDEN
            );
        }

        $query = mb_trim($request->query->getString('q'));
        $cityName = mb_trim($request->query->getString('city_name'));

        $results = [];

        foreach (
            $propertyRepository->findAgencyFilterDistricts(
                $user,
                '' !== $query ? $query : null,
                '' !== $cityName ? $cityName : null,
                $request->getLocale()
            ) as $quartier
        ) {
            $results[] = [
                'name' => $quartier,
                'district_name' => $quartier,
                'city_name' => $cityName,
                'display_name' => '' !== $cityName
                    ? $quartier.' — '.$cityName
                    : $quartier,
            ];
        }

        return $this->json(['results' => $results]);
    }

    /**
     * Auto-suggestion de la barre de recherche libre de la liste « Mes biens ».
     *
     * Les propositions sont extraites des mêmes colonnes que celles balayées
     * par le LIKE de PropertyRepository::findPropertysByUserWithFiltersQuery()
     * (référence, titre, ville, pays, adresse), limitées aux biens de l'agence.
     */
    #[Route(
        '/liste/recherche/suggestions',
        name: 'mes_biens_search_suggestions',
        methods: ['GET']
    )]
    public function searchSuggestions(
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(
                ['results' => []],
                Response::HTTP_FORBIDDEN
            );
        }

        $query = mb_trim($request->query->getString('q'));

        $results = $propertyRepository->findAgencySearchSuggestions(
            $user,
            '' !== $query ? $query : null,
            $request->getLocale()
        );

        return $this->json(['results' => $results]);
    }

    /**
     * Résout le code ISO 3166-1 alpha-2 d'un pays à partir de son nom
     * (français ou anglais). Retourne null si aucun code ne correspond.
     */
    private function resolveCountryCode(string $name): ?string
    {
        static $map = null;

        if (null === $map) {
            $map = [];

            foreach (['fr', 'en'] as $locale) {
                try {
                    foreach (Countries::getNames($locale) as $code => $countryName) {
                        $map[$this->normalizeCountryKey($countryName)] = $code;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return $map[$this->normalizeCountryKey($name)] ?? null;
    }

    private function normalizeCountryKey(string $value): string
    {
        $value = mb_strtolower(mb_trim($value));

        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D) ?: $value;
        }

        $value = preg_replace('/[\x{0300}-\x{036f}]/u', '', $value) ?? $value;

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    #[Route(
        '/{id}/pause',
        name: 'mes_biens_pause',
        methods: ['POST']
    )]
    public function pause(
        Property $property,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $property->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'property_pause_'.$property->getId(),
            $request->request->getString('_property_token')
        )) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $property->setStatut(
            StatutAnnonceImmobiliere::DEPUBLIEE
        );

        $entityManager->flush();

        $this->addFlash(
            'success',
            'L’annonce a été mise en pause.'
        );

        return $this->redirectToRoute(
            'agence_immobiliere_mes_biens_list'
        );
    }

    #[Route(
        '/{id}/booster',
        name: 'mes_biens_boost',
        methods: ['POST']
    )]
    public function boost(
        Property $property,
        Request $request,
        PropertyBoostService $propertyBoostService,
        EntityManagerInterface $entityManager,
        PropertyNotificationLabeler $propertyNotificationLabeler,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $property->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'property_boost_'.$property->getId(),
            $request->request->getString('_property_token')
        )) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $wasPaused = StatutAnnonceImmobiliere::DEPUBLIEE === $property->getStatut();

        try {
            $boost = $propertyBoostService->boost($property, $user);

            /*
             * Notification agence : le Boost vient d'être activé sur l'annonce.
             */
            $entityManager->persist(
                (new AgencyNotification())
                    ->setAgency($user)
                    ->setNom(
                        $propertyNotificationLabeler->boostActiveLabel($property)
                    )
            );
            $entityManager->flush();

            $this->addFlash(
                'success',
                \sprintf(
                    $wasPaused
                        ? 'L’annonce a été republiée et boostée jusqu’au %s.'
                        : 'L’annonce est boostée jusqu’au %s.',
                    $boost->getEndsAt()->format('d/m/Y')
                )
            );
        } catch (BoostException $exception) {
            $this->addFlash(
                'danger',
                $exception->getMessage()
            );
        }

        return $this->redirectToRoute(
            $this->boostRedirectRoute($request)
        );
    }

    private function boostRedirectRoute(Request $request): string
    {
        return match ($request->request->getString('_redirect_route')) {
            'agence_immobiliere_dashboard' => 'agence_immobiliere_dashboard',
            default => 'agence_immobiliere_mes_biens_list',
        };
    }

    #[Route(
        '/{id}/modifier',
        name: 'mes_biens_edit',
        methods: ['GET']
    )]
    public function edit(
        Property $property,
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $property->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette annonce.');
        }

        $session = $request->getSession();

        $session->set(
            'mes_biens_property_id',
            $property->getId()
        );

        /*
         * On positionne le stepper sur l'étape correspondant aux données
         * déjà enregistrées en base pour ce bien, au lieu de repartir de
         * l'étape 1. Une annonce complète (publiée) reprend donc directement
         * à l'étape 8, un brouillon partiel à la première étape encore vide.
         */
        $resumeStep = $this->resolveEditResumeStep($property);

        $session->set(
            'mes_biens_reached_step',
            $resumeStep
        );

        $transaction = $property->getTypeTransaction();

        if ($transaction) {
            /*
             * ID réel et dynamique de la transaction.
             */
            $session->set(
                'typeTransactionId',
                $transaction->getId()
            );

            /*
             * Code métier utilisé par MesBiensType
             * pour savoir quels champs afficher à l'étape prix.
             *
             * 1 = vente
             * 2 = location
             */
            $slugFr = $transaction
                ->translate('fr')
                ->getSlug();

            $typeTransactionCode = match ($slugFr) {
                'vente' => '1',
                'location' => '2',
                default => null,
            };

            if (null !== $typeTransactionCode) {
                $session->set(
                    'typeTransaction',
                    $typeTransactionCode
                );
            }
        }

        $session->set(
            'mes_biens_edit_mode',
            true
        );

        $parameters = [
            'step' => $resumeStep,
        ];

        if ($transaction && $transaction->getId()) {
            $parameters['typeTransaction'] = $transaction->getId();
        }

        return $this->redirectToRoute(
            'agence_immobiliere_mes_biens',
            $parameters
        );
    }

    /**
     * Détermine l'étape du tunnel « Mes biens » à laquelle reprendre la
     * modification d'un bien, en fonction des champs déjà renseignés en base.
     *
     * La valeur retournée correspond à la première étape encore vide (ou à
     * l'étape 8 lorsque tout est renseigné). Les étapes 5 (bilan énergétique)
     * n'existe que pour un bien situé en France.
     */
    private function resolveEditResumeStep(Property $property): int
    {
        $isFrance = $this->isFranceCountry($property->getPays());

        // Étape 8 : prix / loyer.
        if (null !== $property->getPrix()
            || null !== $property->getMontantLoyerHorsCharge()
        ) {
            return 8;
        }

        // Étape 7 : description.
        if (null !== $property->getTitreDuLogement()
            || null !== $property->getDescriptionLogement()
        ) {
            return 8;
        }

        // Étape 6 : photos.
        if (!$property->getPropertyImages()->isEmpty()) {
            return 7;
        }

        // Étape 5 : bilan énergétique (France uniquement).
        if ($isFrance
            && (null !== $property->getDpeLettre()
                || null !== $property->getDpe()
                || null !== $property->getGes())
        ) {
            return 6;
        }

        // Étape 4 : caractéristiques.
        if (null !== $property->getSurfaceTotal()
            || null !== $property->getAnneeConstruction()
            || !$property->getCaracteristique()->isEmpty()
        ) {
            return $isFrance ? 5 : 6;
        }

        // Étape 3 : adresse.
        if (null !== $property->getMapboxId()
            || null !== $property->getFullAddress()
            || null !== $property->getVille()
        ) {
            return 4;
        }

        // Étape 2 : type de transaction.
        if (null !== $property->getTypeTransaction()) {
            return 3;
        }

        // Étape 1 : type de bien.
        if (null !== $property->getTypeBien()) {
            return 2;
        }

        return 1;
    }

    #[Route(
        '/{id}/reactiver',
        name: 'mes_biens_reactivate',
        methods: ['POST']
    )]
    public function reactivate(
        Property $property,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $property->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'property_reactivate_'.$property->getId(),
            $request->request->getString('_property_token')
        )) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $property->setStatut(
            StatutAnnonceImmobiliere::PUBLIEE
        );

        $entityManager->flush();

        $this->addFlash(
            'success',
            'L’annonce a été réactivée.'
        );

        return $this->redirectToRoute(
            'agence_immobiliere_mes_biens_list'
        );
    }

    #[Route(
        '/{id}/supprimer',
        name: 'mes_biens_delete',
        methods: ['POST']
    )]
    public function delete(
        Property $property,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $property->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'property_delete_'.$property->getId(),
            $request->request->getString('_property_token')
        )) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $property->setStatut(
            StatutAnnonceImmobiliere::SUPPRIMEE
        );

        $entityManager->flush();

        $this->addFlash(
            'success',
            'L’annonce a été supprimée.'
        );

        return $this->redirectToRoute(
            'agence_immobiliere_mes_biens_list'
        );
    }

    #[Route(
        '/actions-groupees',
        name: 'mes_biens_bulk_action',
        methods: ['POST']
    )]
    public function bulkAction(
        Request $request,
        PropertyRepository $propertyRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'property_bulk_action',
            $request->request->getString('_token')
        )) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $propertyIds = $request->request->all(
            'properties'
        );

        $action = $request->request->getString(
            'action'
        );

        if ([] === $propertyIds) {
            $this->addFlash(
                'warning',
                'Sélectionnez au moins une annonce.'
            );

            return $this->redirectToRoute(
                'agence_immobiliere_mes_biens_list'
            );
        }

        $propertyIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        $propertyIds
                    )
                )
            )
        );

        $properties = $propertyRepository
            ->createQueryBuilder('p')
            ->andWhere('p.id IN (:ids)')
            ->andWhere('p.user = :user')
            ->setParameter('ids', $propertyIds)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        if ([] === $properties) {
            $this->addFlash(
                'warning',
                'Aucune annonce valide sélectionnée.'
            );

            return $this->redirectToRoute(
                'agence_immobiliere_mes_biens_list'
            );
        }

        $newStatus = match ($action) {
            'pause' => StatutAnnonceImmobiliere::DEPUBLIEE,
            'reactivate' => StatutAnnonceImmobiliere::PUBLIEE,
            'delete' => StatutAnnonceImmobiliere::SUPPRIMEE,
            default => null,
        };

        if (null === $newStatus) {
            $this->addFlash(
                'danger',
                'Action inconnue.'
            );

            return $this->redirectToRoute(
                'agence_immobiliere_mes_biens_list'
            );
        }

        foreach ($properties as $property) {
            $property->setStatut(
                $newStatus
            );
        }

        $entityManager->flush();

        $message = match ($action) {
            'pause' => \sprintf(
                '%d annonce(s) mise(s) en pause.',
                \count($properties)
            ),

            'reactivate' => \sprintf(
                '%d annonce(s) réactivée(s).',
                \count($properties)
            ),

            'delete' => \sprintf(
                '%d annonce(s) supprimée(s).',
                \count($properties)
            ),

            default => 'Action effectuée.',
        };

        $this->addFlash(
            'success',
            $message
        );

        return $this->redirectToRoute(
            'agence_immobiliere_mes_biens_list'
        );
    }

    #[Route('/', name: 'mes_biens')]
    public function index(
        Request $request,
        PropertyRepository $propertyRepository,
        AgencyPropertyQuotaCalculator $agencyPropertyQuotaCalculator,
        EntityManagerInterface $entityManager,
        NumericSlugGenerator $numericSlugGenerator,
        MapboxAddressTranslator $mapboxAddressTranslator,
        AgencyPropertySubmissionMailer $agencyPropertySubmissionMailer,
        PropertyNotificationLabeler $propertyNotificationLabeler,
    ): Response {
        $session = $request->getSession();

        if ($request->isMethod('GET') && $request->query->has('token')) {
            $this->clearMesBiensSession(
                $session
            );
        }

        $step = $request->query->getInt(
            'step',
            1
        );

        /*
         * ------------------------------------------------------------------
         * Utilisateur connecté
         * ------------------------------------------------------------------
         */
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour gérer vos biens.');
        }

        /*
         * ------------------------------------------------------------------
         * ID dynamique de la transaction provenant de l'URL
         * ------------------------------------------------------------------
         *
         * Exemple :
         *
         * /mes/biens/?step=3&typeTransaction=5
         *
         * 5 correspond réellement à :
         *
         * CategoryBienTransaction::getId()
         */
        $typeTransactionId = $request->query->getInt(
            'typeTransaction',
            0
        );

        if ($typeTransactionId > 0) {
            $session->set(
                'typeTransactionId',
                $typeTransactionId
            );
        } else {
            $typeTransactionId = (int) $session->get(
                'typeTransactionId',
                0
            );
        }

        /*
         * ------------------------------------------------------------------
         * Étape maximale atteinte
         * ------------------------------------------------------------------
         */
        if (!$session->has('mes_biens_reached_step')) {
            $session->set(
                'mes_biens_reached_step',
                1
            );
        }

        /*
         * ------------------------------------------------------------------
         * Récupération du bien en cours
         * ------------------------------------------------------------------
         */
        $propertyId = $session->get(
            'mes_biens_property_id'
        );

        if ($propertyId) {
            $mesBiens = $propertyRepository->find(
                $propertyId
            );

            if (!$mesBiens) {
                $this->clearMesBiensSession(
                    $session
                );

                $mesBiens = new Property();
            } else {
                /*
                 * =========================================================
                 * SÉCURITÉ
                 * =========================================================
                 *
                 * Après un refresh :
                 *
                 * - le Property est rechargé depuis la BDD ;
                 * - son user doit correspondre au user connecté.
                 */
                if (
                    null === $mesBiens->getUser()
                    || $mesBiens->getUser()?->getId() !== $user->getId()
                ) {
                    $this->clearMesBiensSession(
                        $session
                    );

                    throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette annonce.');
                }
            }
        } else {
            $mesBiens = new Property();
        }

        /*
         * ------------------------------------------------------------------
         * Transaction déjà enregistrée sur le Property
         * ------------------------------------------------------------------
         */
        $existingTransaction = $mesBiens
            ->getTypeTransaction();

        if ($existingTransaction) {
            $typeTransactionId = $existingTransaction
                ->getId();

            if ($typeTransactionId) {
                $session->set(
                    'typeTransactionId',
                    $typeTransactionId
                );
            }
        }

        /*
         * ------------------------------------------------------------------
         * Si typeTransaction est présent dans l'URL
         * ------------------------------------------------------------------
         *
         * Permet notamment de conserver la sélection lors d'un refresh.
         */
        if (
            $typeTransactionId > 0
            && null === $mesBiens->getTypeTransaction()
        ) {
            $transactionFromUrl = $entityManager
                ->getRepository(
                    CategoryBienTransaction::class
                )
                ->find(
                    $typeTransactionId
                );

            if (
                $transactionFromUrl instanceof CategoryBienTransaction
            ) {
                /*
                 * Modification seulement en mémoire.
                 *
                 * Aucun flush sur un GET.
                 */
                $mesBiens->setTypeTransaction(
                    $transactionFromUrl
                );
            } else {
                $session->remove(
                    'typeTransactionId'
                );

                $typeTransactionId = 0;
            }
        }

        /*
         * ------------------------------------------------------------------
         * Code métier transaction
         * ------------------------------------------------------------------
         *
         * ATTENTION :
         *
         * typeTransactionId = ID Doctrine réel et dynamique
         *
         * typeTransaction   = code métier :
         *
         * 1 = vente
         * 2 = location
         *
         * Le code métier est utilisé par MesBiensType à l'étape 8.
         */
        $typeTransaction = $session->get(
            'typeTransaction'
        );

        /*
         * ------------------------------------------------------------------
         * Formulaire
         * ------------------------------------------------------------------
         */
        $form = $this->createForm(
            MesBiensType::class,
            $mesBiens,
            [
                'step' => $step,
                'typeTransaction' => $typeTransaction,
            ]
        );

        $form->handleRequest(
            $request
        );

        $propertyQuota = $agencyPropertyQuotaCalculator->calculate($user);
        $showLimitAnnonceModal = $propertyQuota['reached']
            && null === $mesBiens->getId()
            && true !== $session->get(
                'mes_biens_edit_mode',
                false
            );

        if ($form->isSubmitted() && !$showLimitAnnonceModal) {
            if (
                $form->has('saveAndExit')
                && $form->get('saveAndExit')->isClicked()
            ) {
                $this->saveMesBiensDraftStep(
                    step: $step,
                    mesBiens: $mesBiens,
                    user: $user,
                    form: $form,
                    session: $session,
                    entityManager: $entityManager,
                    numericSlugGenerator: $numericSlugGenerator,
                    mapboxAddressTranslator: $mapboxAddressTranslator,
                );

                $this->clearMesBiensSession(
                    $session
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens_list'
                );
            }

            /*
             * ==============================================================
             * STEP 1 : TYPE DE BIEN
             * ==============================================================
             */
            if (1 === $step) {
                /*
                 * Nouveau Property.
                 */
                if (null === $mesBiens->getId()) {
                    $mesBiens->setSlug(
                        $numericSlugGenerator->generate(
                            16
                        )
                    );

                    /*
                     * =====================================================
                     * CORRECTION IMPORTANTE
                     * =====================================================
                     *
                     * On rattache immédiatement le bien
                     * à l'utilisateur connecté AVANT le premier flush.
                     *
                     * Sans ceci :
                     *
                     * property.user = NULL
                     *
                     * et lors d'un refresh :
                     *
                     * $mesBiens->getUser()?->getId()
                     *
                     * retourne NULL.
                     */
                    $mesBiens->setUser(
                        $user
                    );

                    $entityManager->persist(
                        $mesBiens
                    );
                } else {
                    /*
                     * Si le Property existe déjà,
                     * on vérifie encore son propriétaire.
                     */
                    if (
                        null === $mesBiens->getUser()
                        || $mesBiens->getUser()?->getId() !== $user->getId()
                    ) {
                        throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette annonce.');
                    }
                }

                $entityManager->flush();

                /*
                 * Le Property possède maintenant :
                 *
                 * - un ID
                 * - un user
                 * - un typeBien
                 * - un slug
                 */
                $session->set(
                    'mes_biens_property_id',
                    $mesBiens->getId()
                );

                $this->updateReachedStep(
                    $session,
                    2
                );

                /*
                 * Transaction éventuellement déjà existante
                 * en modification ou après retour arrière.
                 */
                $typeTransactionId = $mesBiens
                    ->getTypeTransaction()
                    ?->getId();

                if (!$typeTransactionId) {
                    $typeTransactionId = (int) $session->get(
                        'typeTransactionId',
                        0
                    );
                }

                /*
                 * Première création :
                 *
                 * /mes/biens/?step=2
                 *
                 * Si une transaction est déjà connue :
                 *
                 * /mes/biens/?step=2&typeTransaction=5
                 */
                $parameters = [
                    'step' => 2,
                ];

                if ($typeTransactionId > 0) {
                    $parameters['typeTransaction'] = $typeTransactionId;
                }

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    $parameters
                );
            }

            /*
             * ==============================================================
             * STEP 2 : TYPE DE TRANSACTION
             * ==============================================================
             */
            if (2 === $step) {
                /*
                 * Transaction choisie réellement
                 * par l'utilisateur.
                 */
                $transaction = $mesBiens
                    ->getTypeTransaction();

                if (!$transaction instanceof CategoryBienTransaction) {
                    $this->addFlash(
                        'danger',
                        'Veuillez sélectionner un type de transaction.'
                    );

                    return $this->redirectToRoute(
                        'agence_immobiliere_mes_biens',
                        [
                            'step' => 2,
                        ]
                    );
                }

                /*
                 * =========================================================
                 * ID DYNAMIQUE
                 * =========================================================
                 *
                 * Aucun ID n'est écrit en dur.
                 *
                 * Exemple :
                 *
                 * Vente      ID = 1
                 * Location   ID = 4
                 * Viager     ID = 8
                 *
                 * Le système récupère réellement :
                 *
                 * $transaction->getId()
                 */
                $typeTransactionId = $transaction
                    ->getId();

                if (!$typeTransactionId) {
                    throw new \LogicException('La transaction sélectionnée ne possède pas d’identifiant.');
                }

                $session->set(
                    'typeTransactionId',
                    $typeTransactionId
                );

                /*
                 * Code métier uniquement utilisé
                 * pour savoir si l'étape prix est
                 * une vente ou une location.
                 */
                $slugFr = $transaction
                    ->translate('fr')
                    ->getSlug();

                $typeTransactionCode = match ($slugFr) {
                    'vente' => '1',
                    'location' => '2',
                    default => null,
                };

                if (null !== $typeTransactionCode) {
                    $session->set(
                        'typeTransaction',
                        $typeTransactionCode
                    );

                    $typeTransaction = $typeTransactionCode;
                } else {
                    $session->remove(
                        'typeTransaction'
                    );

                    $typeTransaction = null;
                }

                /*
                 * Enregistrement du choix de transaction.
                 */
                $entityManager->flush();

                $this->updateReachedStep(
                    $session,
                    3
                );

                /*
                 * URL dynamique :
                 *
                 * /mes/biens/?step=3&typeTransaction=ID
                 *
                 * Exemple si ID réellement choisi = 4 :
                 *
                 * /mes/biens/?step=3&typeTransaction=4
                 */
                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    [
                        'step' => 3,
                        'typeTransaction' => $typeTransactionId,
                    ]
                );
            }

            /*
             * ==============================================================
             * STEP 3 : ADRESSE
             * ==============================================================
             */
            if (3 === $step) {
                $this->syncAddressTranslationsFromMapbox(
                    $mesBiens,
                    $mapboxAddressTranslator
                );

                $entityManager->flush();

                $this->syncTransactionSession(
                    $mesBiens,
                    $session
                );

                $this->updateReachedStep(
                    $session,
                    4
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    $this->buildStepParameters(
                        step: 4,
                        property: $mesBiens,
                        session: $session
                    )
                );
            }

            /*
             * ==============================================================
             * STEP 4 : CARACTÉRISTIQUES
             * ==============================================================
             */
            if (4 === $step) {
                $entityManager->flush();

                $this->syncTransactionSession(
                    $mesBiens,
                    $session
                );

                /*
                 * Si le pays n'est pas la France,
                 * on saute le bilan énergétique.
                 */
                if (!$this->isFranceCountry($mesBiens->getPays())) {
                    $this->updateReachedStep(
                        $session,
                        6
                    );

                    return $this->redirectToRoute(
                        'agence_immobiliere_mes_biens',
                        $this->buildStepParameters(
                            step: 6,
                            property: $mesBiens,
                            session: $session
                        )
                    );
                }

                $this->updateReachedStep(
                    $session,
                    5
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    $this->buildStepParameters(
                        step: 5,
                        property: $mesBiens,
                        session: $session
                    )
                );
            }

            /*
             * ==============================================================
             * STEP 5 : BILAN ÉNERGÉTIQUE
             * ==============================================================
             */
            if (5 === $step) {
                $entityManager->flush();

                $this->syncTransactionSession(
                    $mesBiens,
                    $session
                );

                $this->updateReachedStep(
                    $session,
                    6
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    $this->buildStepParameters(
                        step: 6,
                        property: $mesBiens,
                        session: $session
                    )
                );
            }

            /*
             * ==============================================================
             * STEP 6 : PHOTOS
             * ==============================================================
             */
            if (6 === $step) {
                $this->syncPropertyImages(
                    $mesBiens,
                    $entityManager
                );

                $entityManager->flush();

                $this->syncTransactionSession(
                    $mesBiens,
                    $session
                );

                $this->updateReachedStep(
                    $session,
                    7
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    $this->buildStepParameters(
                        step: 7,
                        property: $mesBiens,
                        session: $session
                    )
                );
            }

            /*
             * ==============================================================
             * STEP 7 : DESCRIPTION
             * ==============================================================
             */
            if (7 === $step) {
                $this->syncDescriptionTranslationsFromForm(
                    $mesBiens,
                    $form
                );

                $entityManager->flush();

                $this->syncTransactionSession(
                    $mesBiens,
                    $session
                );

                $typeTransaction = $session->get(
                    'typeTransaction'
                );

                $typeTransactionId = $this->getTypeTransactionId(
                    $mesBiens,
                    $session
                );

                /*
                 * Transaction obligatoire.
                 */
                if (
                    null === $typeTransaction
                    || null === $typeTransactionId
                ) {
                    return $this->redirectToRoute(
                        'agence_immobiliere_mes_biens',
                        [
                            'step' => 2,
                        ]
                    );
                }

                $this->updateReachedStep(
                    $session,
                    8
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    [
                        'step' => 8,
                        'typeTransaction' => $typeTransactionId,
                    ]
                );
            }

            /*
             * ==============================================================
             * STEP 8 : PRIX
             * ==============================================================
             */
            if (8 === $step) {
                $shouldSendAgencySubmissionEmail = false;

                /*
                 * Dernière étape : l'annonce quitte l'état brouillon (ou
                 * refusé, après correction) et repasse en attente de
                 * validation. On ne touche pas au statut d'une annonce déjà
                 * validée / publiée que l'on ne fait que modifier.
                 */
                if (\in_array(
                    $mesBiens->getStatut(),
                    [
                        StatutAnnonceImmobiliere::BROUILLON,
                        StatutAnnonceImmobiliere::REFUSEE,
                    ],
                    true
                )) {
                    $mesBiens->setStatut(
                        StatutAnnonceImmobiliere::PENDING
                    );

                    $shouldSendAgencySubmissionEmail = true;

                    /*
                     * Notification agence : l'annonce vient d'être soumise
                     * et attend la validation d'un administrateur.
                     */
                    $entityManager->persist(
                        (new AgencyNotification())
                            ->setAgency($user)
                            ->setNom(
                                $propertyNotificationLabeler->pendingLabel($mesBiens)
                            )
                    );
                }

                $entityManager->flush();

                if ($shouldSendAgencySubmissionEmail) {
                    $agencyPropertySubmissionMailer->sendSubmissionPendingNotification(
                        $user,
                        $mesBiens
                    );
                }

                /*
                 * On récupère l'ID AVANT de nettoyer la session.
                 */
                $typeTransactionId = $this->getTypeTransactionId(
                    $mesBiens,
                    $session
                );

                $isEditMode = true === $session->get(
                    'mes_biens_edit_mode',
                    false
                );

                $this->clearMesBiensSession(
                    $session
                );

                if ($isEditMode) {
                    $this->addFlash(
                        'success',
                        'L’annonce a été modifiée avec succès.'
                    );

                    return $this->redirectToRoute(
                        'agence_immobiliere_mes_biens_list'
                    );
                }

                $parameters = [];

                if (null !== $typeTransactionId) {
                    $parameters['typeTransaction'] = $typeTransactionId;
                }

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens_status',
                    $parameters
                );
            }
        }

        /*
         * ------------------------------------------------------------------
         * RENDER
         * ------------------------------------------------------------------
         */
        $typeTransactionId = $this->getTypeTransactionId(
            $mesBiens,
            $session
        );

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_mes_biens/index.html.twig',
            [
                'form' => $form->createView(),

                'step' => $step,

                'stepperStep' => $session->get(
                    'mes_biens_reached_step',
                    $step
                ),

                /*
                 * Code métier :
                 *
                 * 1 = vente
                 * 2 = location
                 */
                'typeTransaction' => $typeTransaction,

                /*
                 * ID réel et dynamique.
                 */
                'typeTransactionId' => $typeTransactionId,

                /*
                 * Le bilan énergétique est disponible uniquement
                 * pour une adresse située en France.
                 */
                'showBilanEnergetique' => $this->isFranceCountry(
                    $mesBiens->getPays()
                ),

                'showLimitAnnonceModal' => $showLimitAnnonceModal,
                'annoncesRestantes' => $propertyQuota['remaining'],
                'annoncesUtilisees' => $propertyQuota['used'],
                'limiteAnnonces' => $propertyQuota['limit'],
                'abonnementUrl' => $this->generateUrl(
                    'agence_immobiliere_options'
                ),
            ]
        );
    }

    #[Route(
        '/status',
        name: 'mes_biens_status'
    )]
    public function status(
        Request $request,
    ): Response {
        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_mes_biens/status.html.twig',
            [
                'typeTransactionId' => $request
                    ->query
                    ->getInt(
                        'typeTransaction',
                        0
                    ),
            ]
        );
    }

    private function saveMesBiensDraftStep(
        int $step,
        Property $mesBiens,
        User $user,
        FormInterface $form,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        NumericSlugGenerator $numericSlugGenerator,
        MapboxAddressTranslator $mapboxAddressTranslator,
    ): void {
        if (null === $mesBiens->getId()) {
            if (null === $mesBiens->getTypeBien()) {
                return;
            }

            $mesBiens->setSlug(
                $numericSlugGenerator->generate(
                    16
                )
            );

            $mesBiens->setUser(
                $user
            );

            $entityManager->persist(
                $mesBiens
            );
        } elseif (
            null === $mesBiens->getUser()
            || $mesBiens->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette annonce.');
        }

        if (3 === $step) {
            $this->syncAddressTranslationsFromMapbox(
                $mesBiens,
                $mapboxAddressTranslator
            );
        }

        if (6 === $step) {
            $this->syncPropertyImages(
                $mesBiens,
                $entityManager
            );
        }

        if (7 === $step) {
            $this->syncDescriptionTranslationsFromForm(
                $mesBiens,
                $form
            );
        }

        $entityManager->flush();

        if (null !== $mesBiens->getId()) {
            $session->set(
                'mes_biens_property_id',
                $mesBiens->getId()
            );
        }

        $this->syncTransactionSession(
            $mesBiens,
            $session
        );
    }

    private function syncAddressTranslationsFromMapbox(
        Property $mesBiens,
        MapboxAddressTranslator $mapboxAddressTranslator,
    ): void {
        $mapboxId = $mesBiens
            ->getMapboxId();

        $sessionToken = $mesBiens
            ->getSessionIdMapbox();

        $isFixtureMapboxId = null !== $mapboxId
            && str_starts_with(
                $mapboxId,
                'fixture-'
            );

        if (
            !$mapboxId
            || $isFixtureMapboxId
        ) {
            return;
        }

        foreach (
            ['fr', 'en'] as $locale
        ) {
            $address = $mapboxAddressTranslator
                ->translateByMapboxId(
                    mapboxId: $mapboxId,
                    sessionToken: $sessionToken,
                    locale: $locale
                );

            if (null === $address) {
                continue;
            }

            $translation = $mesBiens->translate(
                $locale
            );

            $translation->setAdresse(
                $address['adresse']
            );

            $translation->setVille(
                $address['ville']
            );

            $translation->setPays(
                $address['pays']
            );

            $translation->setFullAddress(
                $address['fullAddress']
            );

            $translation->setRegion(
                $address['region']
            );

            $translation->setDistrict(
                $address['district']
            );

            $translation->setLocality(
                $address['locality']
            );

            $translation->setNeighborhood(
                $address['neighborhood']
            );

            $translation->setPoi(
                $address['poi']
            );
        }

        $mesBiens->mergeNewTranslations();
    }

    private function syncPropertyImages(
        Property $mesBiens,
        EntityManagerInterface $entityManager,
    ): void {
        foreach (
            $mesBiens->getPropertyImages() as $index => $propertyImage
        ) {
            $propertyImage->setProperty(
                $mesBiens
            );

            $propertyImage->setPosition(
                $index + 1
            );
        }

        $entityManager->persist(
            $mesBiens
        );
    }

    private function syncDescriptionTranslationsFromForm(
        Property $mesBiens,
        FormInterface $form,
    ): void {
        $title = $form
            ->get('titreDuLogement')
            ->getData();

        $description = $form
            ->get('descriptionLogement')
            ->getData();

        foreach (
            ['fr', 'en'] as $locale
        ) {
            $translation = $mesBiens->translate(
                $locale
            );

            $translation->setTitreDuLogement(
                $title
            );

            $translation->setDescriptionLogement(
                $description
            );
        }

        $mesBiens->mergeNewTranslations();
    }

    /**
     * Retourne l'ID réel et dynamique
     * de la transaction.
     */
    private function getTypeTransactionId(
        Property $property,
        SessionInterface $session,
    ): ?int {
        /*
         * Priorité à l'entité.
         */
        $typeTransactionId = $property
            ->getTypeTransaction()
            ?->getId();

        if ($typeTransactionId) {
            $session->set(
                'typeTransactionId',
                $typeTransactionId
            );

            return $typeTransactionId;
        }

        /*
         * Sinon récupération depuis la session.
         */
        $typeTransactionId = (int) $session->get(
            'typeTransactionId',
            0
        );

        if ($typeTransactionId > 0) {
            return $typeTransactionId;
        }

        return null;
    }

    /**
     * Synchronise les informations de transaction
     * avec la session.
     */
    private function syncTransactionSession(
        Property $property,
        SessionInterface $session,
    ): void {
        $transaction = $property
            ->getTypeTransaction();

        if (!$transaction) {
            return;
        }

        /*
         * ID Doctrine réel.
         */
        $typeTransactionId = $transaction
            ->getId();

        if ($typeTransactionId) {
            $session->set(
                'typeTransactionId',
                $typeTransactionId
            );
        }

        /*
         * Code métier :
         *
         * 1 = vente
         * 2 = location
         */
        $slugFr = $transaction
            ->translate('fr')
            ->getSlug();

        $typeTransactionCode = match ($slugFr) {
            'vente' => '1',
            'location' => '2',
            default => null,
        };

        if (null !== $typeTransactionCode) {
            $session->set(
                'typeTransaction',
                $typeTransactionCode
            );
        }
    }

    /**
     * Génère les paramètres d'URL d'une étape.
     *
     * Exemple :
     *
     * /mes/biens/?step=4&typeTransaction=5
     */
    private function buildStepParameters(
        int $step,
        Property $property,
        SessionInterface $session,
    ): array {
        $parameters = [
            'step' => $step,
        ];

        $typeTransactionId = $this->getTypeTransactionId(
            $property,
            $session
        );

        if (null !== $typeTransactionId) {
            $parameters['typeTransaction'] = $typeTransactionId;
        }

        return $parameters;
    }

    /**
     * Mise à jour de l'étape maximale atteinte.
     */
    private function updateReachedStep(
        SessionInterface $session,
        int $step,
    ): void {
        $currentReachedStep = $session->get(
            'mes_biens_reached_step',
            1
        );

        if ($step > $currentReachedStep) {
            $session->set(
                'mes_biens_reached_step',
                $step
            );
        }
    }

    /**
     * Indique si le pays du bien correspond à la France.
     *
     * La valeur actuelle provenant de l'adresse peut être "France"
     * alors que certaines anciennes données peuvent contenir "FR".
     */
    private function isFranceCountry(?string $country): bool
    {
        if (null === $country) {
            return false;
        }

        $country = mb_strtolower(
            mb_trim($country),
            'UTF-8'
        );

        return \in_array(
            $country,
            ['fr', 'france'],
            true
        );
    }

    /**
     * Nettoyage des informations de session
     * utilisées pendant la création/modification d'un bien.
     */
    private function clearMesBiensSession(
        SessionInterface $session,
    ): void {
        $session->remove(
            'mes_biens_property_id'
        );

        /*
         * ID réel de la transaction.
         */
        $session->remove(
            'typeTransactionId'
        );

        /*
         * Code métier vente/location.
         */
        $session->remove(
            'typeTransaction'
        );

        $session->remove(
            'mes_biens_reached_step'
        );

        $session->remove(
            'mes_biens_edit_mode'
        );
    }
}
