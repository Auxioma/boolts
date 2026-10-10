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

namespace App\Controller\Public;

use App\Entity\AgencyProfileDailyVisit;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Filter\ModalFilter;
use App\Entity\FormContact\Contact;
use App\Entity\User;
use App\Form\Filter\ModalFilterType;
use App\Form\FormContact\ContactType;
use App\Repository\AgencyProfileDailyVisitRepository;
use App\Repository\FavorisRepository;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use App\Service\ContactForm\ContactMailer;
use App\Service\Property\CountryCodeResolver;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module Public / DetailAgenceController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class DetailAgenceController extends AbstractController
{
    /**
     * Seules les annonces publiées sont visibles sur la page publique
     * de l'agence (liste, filtres, compteur et auto-complétion).
     */
    private const array PUBLIC_STATUTS = [StatutAnnonceImmobiliere::PUBLIEE];

    /**
     * Handles the __construct controller action.
     */
    public function __construct(
        private readonly ContactMailer $contactMailer,
    ) {
    }

    #[Route(
        path: [
            'en' => '/agency/{slug}',
            'fr' => '/fr/agency/{slug}',
        ],
        name: 'app_public_detail_agence'
    )]
    /**
     * Handles the index controller action.
     */
    public function index(
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        FavorisRepository $favorisRepository,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
        string $slug,
        PaginatorInterface $paginator,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            throw $this->createNotFoundException('Agence introuvable.');
        }

        $this->recordProfileVisit($user, $agencyProfileDailyVisitRepository, $entityManager);

        /**
         * Gestion des filtre avec la pagination.
         */
        $sort = $request->query->get('sort', 'p.createdAt');
        $direction = mb_strtolower($request->query->get('direction', 'desc'));

        $allowedSorts = [
            'p.createdAt',
            'p.views',
            'favorisCount',
        ];

        if (!\in_array($sort, $allowedSorts, true)) {
            $sort = 'p.createdAt';
        }

        if (!\in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $filterForm = $this->createForm(ModalFilterType::class, new ModalFilter(), [
            'action' => $this->generateUrl('app_public_detail_agence', ['slug' => $slug]),
            'method' => 'GET',
        ]);
        $filterForm->handleRequest($request);

        $filters = $request->query->has('modal_filter')
            ? $request->query->all('modal_filter')
            : [];

        $properties = $paginator->paginate(
            $propertyRepository->findPropertysByUserWithFiltersQuery(
                user: $user,
                filters: $filters,
                sort: $sort,
                direction: mb_strtoupper($direction),
                locale: $request->getLocale(),
                statuts: self::PUBLIC_STATUTS,
            ),
            $request->query->getInt('page', 1),
            8,
            [
                'sortFieldParameterName' => '_sort',
                'sortDirectionParameterName' => '_direction',
            ]
        );

        /*
         * Liste des biens déjà ajoutés en favoris
         * par l'utilisateur connecté.
         *
         * Si le visiteur n'est pas connecté : tableau vide.
         * Si c'est une agence : tableau vide.
         */
        $favoritePropertyIds = [];

        if ($this->getUser() && !$this->isGranted('ROLE_AGENCE')) {
            $favoritePropertyIds = $favorisRepository->findPropertyIdsByUser($this->getUser());
        }

        $contactForm = new Contact();
        $form = $this->createForm(ContactType::class, $contactForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactMailer->sendContactMessage(
                contact: $contactForm,
                agencyEmail: $user->getEmail()
            );

            /* enregistrement dans la base de donnée */
            $contactForm->setAgence($user);
            $entityManager->persist($contactForm);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');

            return $this->redirectToRoute('app_public_detail_agence', [
                'slug' => $slug,
            ]);
        }

        return $this->render('public/detail_agence/index.html.twig', [
            'user' => $user,
            'properties' => $properties,
            'form' => $form->createView(),
            'favoritePropertyIds' => $favoritePropertyIds,
            'filterForm' => $filterForm->createView(),
            'modal_filter' => $filters,
        ]);
    }

    /**
     * Compteur « Voir les X logements » de la modale de filtres.
     */
    #[Route(
        path: [
            'en' => '/agency/{slug}/filtres/count',
            'fr' => '/fr/agency/{slug}/filtres/count',
        ],
        name: 'app_public_detail_agence_filters_count',
        methods: ['GET']
    )]
    public function filtersCount(
        string $slug,
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $agency = $this->findAgency($slug, $userRepository);

        $filters = $request->query->has('modal_filter')
            ? $request->query->all('modal_filter')
            : [];

        $count = \count(
            $propertyRepository
                ->findPropertysByUserWithFiltersQuery(
                    user: $agency,
                    filters: $filters,
                    locale: $request->getLocale(),
                    statuts: self::PUBLIC_STATUTS,
                )
                ->getQuery()
                ->getResult()
        );

        return $this->json([
            'count' => $count,
            'total' => $count,
            'totalResults' => $count,
        ]);
    }

    /**
     * Auto-complétion « Pays » : uniquement les pays des annonces publiées de l'agence.
     */
    #[Route(
        path: [
            'en' => '/agency/{slug}/filtres/pays',
            'fr' => '/fr/agency/{slug}/filtres/pays',
        ],
        name: 'app_public_detail_agence_filter_countries',
        methods: ['GET']
    )]
    public function filterCountries(
        string $slug,
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        CountryCodeResolver $countryCodeResolver,
        Request $request,
    ): Response {
        $agency = $this->findAgency($slug, $userRepository);
        $query = mb_trim($request->query->getString('q'));

        $results = [];

        foreach (
            $propertyRepository->findAgencyFilterCountries(
                $agency,
                '' !== $query ? $query : null,
                $request->getLocale(),
                self::PUBLIC_STATUTS,
            ) as $name
        ) {
            $code = $countryCodeResolver->resolve($name) ?? mb_strtoupper($name);

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
     * Auto-complétion « Ville », restreinte au pays éventuellement sélectionné.
     */
    #[Route(
        path: [
            'en' => '/agency/{slug}/filtres/villes',
            'fr' => '/fr/agency/{slug}/filtres/villes',
        ],
        name: 'app_public_detail_agence_filter_cities',
        methods: ['GET']
    )]
    public function filterCities(
        string $slug,
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $agency = $this->findAgency($slug, $userRepository);
        $query = mb_trim($request->query->getString('q'));
        $countryName = mb_trim($request->query->getString('country_name'));

        $results = [];

        foreach (
            $propertyRepository->findAgencyFilterCities(
                $agency,
                '' !== $query ? $query : null,
                '' !== $countryName ? $countryName : null,
                $request->getLocale(),
                self::PUBLIC_STATUTS,
            ) as $ville
        ) {
            $results[] = [
                'city_name' => $ville,
                'name' => $ville,
                'label' => $ville,
                'country_name' => $countryName,
                'display_name' => '' !== $countryName ? $ville.' — '.$countryName : $ville,
            ];
        }

        return $this->json(['results' => $results]);
    }

    /**
     * Auto-complétion « Quartier », restreinte à la ville éventuellement sélectionnée.
     */
    #[Route(
        path: [
            'en' => '/agency/{slug}/filtres/quartiers',
            'fr' => '/fr/agency/{slug}/filtres/quartiers',
        ],
        name: 'app_public_detail_agence_filter_districts',
        methods: ['GET']
    )]
    public function filterDistricts(
        string $slug,
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        $agency = $this->findAgency($slug, $userRepository);
        $query = mb_trim($request->query->getString('q'));
        $cityName = mb_trim($request->query->getString('city_name'));

        $results = [];

        foreach (
            $propertyRepository->findAgencyFilterDistricts(
                $agency,
                '' !== $query ? $query : null,
                '' !== $cityName ? $cityName : null,
                $request->getLocale(),
                self::PUBLIC_STATUTS,
            ) as $quartier
        ) {
            $results[] = [
                'name' => $quartier,
                'district_name' => $quartier,
                'city_name' => $cityName,
                'display_name' => '' !== $cityName ? $quartier.' — '.$cityName : $quartier,
            ];
        }

        return $this->json(['results' => $results]);
    }

    private function findAgency(string $slug, UserRepository $userRepository): User
    {
        $agency = $userRepository->findOneBy(['slug' => $slug]);

        if (!$agency instanceof User) {
            throw $this->createNotFoundException('Agence introuvable.');
        }

        return $agency;
    }

    private function recordProfileVisit(
        User $agency,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
        EntityManagerInterface $entityManager,
    ): void {
        $viewer = $this->getUser();

        if ($viewer instanceof User && $viewer->getId() === $agency->getId()) {
            return;
        }

        $today = new \DateTimeImmutable('today');
        $dailyVisit = $agencyProfileDailyVisitRepository->findOneBy([
            'agency' => $agency,
            'viewedOn' => $today,
        ]);

        if (!$dailyVisit instanceof AgencyProfileDailyVisit) {
            $dailyVisit = new AgencyProfileDailyVisit();
            $dailyVisit
                ->setAgency($agency)
                ->setViewedOn($today)
                ->setVisits(0);
            $entityManager->persist($dailyVisit);
        }

        $dailyVisit->incrementVisits();
        $agency->setVisitAgency($agency->getVisitAgency() + 1);
        $entityManager->flush();
    }
}
