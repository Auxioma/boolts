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

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\CategoryBienTransactionTranslation;
use App\Entity\CategoryBienTranslation;
use App\Entity\Filter\ModalFilter;
use App\Entity\SearchBar\FilterCityCountry;
use App\Form\Filter\ModalFilterType;
use App\Form\SearchBar\FilterCityCountryType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Page de résultats « type de bien × transaction » (liens du footer).
 *
 * Exemple : /fr/biens/france/louer/appartement ou /properties/germany/rent/apartment.
 * Le pays est celui du visiteur (IP), nommé dans la langue du site.
 * Réutilise le design de la recherche publique (public/search/index.html.twig) :
 * les critères sont stockés en session sous un searchToken afin que la pagination,
 * la vue carte, le changement de vue et la modale de filtres fonctionnent comme
 * sur app_public_search_results.
 */
final class ResultFilterController extends AbstractController
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
        path: [
            'en' => '/properties/{countrySlug}/{transactionSlug}/{categorySlug}',
            'fr' => '/fr/biens/{countrySlug}/{transactionSlug}/{categorySlug}',
        ],
        name: 'app_public_search_type',
        requirements: [
            'countrySlug' => '[a-z0-9-]+',
            'transactionSlug' => '[a-z0-9-]+',
            'categorySlug' => '[a-z0-9-]+',
        ],
        methods: ['GET']
    )]
    public function index(Request $request, string $countrySlug, string $transactionSlug, string $categorySlug): Response
    {
        $locale = $request->getLocale();

        $transaction = $this->findTransactionBySlug($transactionSlug, $locale);
        $category = $this->findCategoryBySlug($categorySlug, $locale);
        $countryCode = $this->findCountryCodeBySlug($countrySlug, $locale);

        if (null === $transaction || null === $category || null === $countryCode) {
            throw $this->createNotFoundException('Ce type de bien est introuvable.');
        }

        $view = $request->query->get('view', 'list');

        if (!\in_array($view, ['list', 'map'], true)) {
            $view = 'list';
        }

        /*
         * Critères de recherche au même format que SearchController::buildCriteriaFromFilter(),
         * localisés sur le pays de l'URL.
         */
        $countryName = Countries::getName($countryCode, $locale);

        $criteria = [
            'transactionTypeId' => $transaction->getId(),
            'filter' => null,
            'selectedValue' => null,
            'ville' => null,
            'cp' => null,
            'pays' => $countryName,
            'selectedCountryCode' => $countryCode,
            'selectedRegionName' => null,
            'selectedLatitude' => null,
            'selectedLongitude' => null,
            'selectedFullAddress' => null,
            'selectedMapboxId' => null,
            'selectedFeatureType' => null,
            'selectedCityName' => null,
            'selectedPostalCode' => null,
            'selectedCountryName' => $countryName,
            'selectedLocale' => null,
            'selectedLocationJson' => null,
        ];

        /*
         * Token de session : utilisé par la pagination, la carte (map-bounds),
         * le switch liste/carte et l'action de la modale (app_public_search_results).
         */
        $searchToken = bin2hex(random_bytes(16));

        $request->getSession()->set('property_search_'.$searchToken, $criteria);

        /*
         * Filtres de la modale correspondant au lien : pays + transaction + type de bien.
         * Le pays est passé en français et en anglais (noms stockés sur les annonces).
         * Ils sont propagés par les liens de pagination / changement de vue.
         */
        $modalFilter = [
            'natureDeLaPropriete' => (string) $transaction->getId(),
            'typeDePropriete' => [(string) $category->getId()],
            'pays' => array_values(array_unique([
                $countryName,
                Countries::getName($countryCode, 'fr'),
                Countries::getName($countryCode, 'en'),
            ])),
        ];

        /*
         * Barre de recherche principale : transaction présélectionnée.
         */
        $filter = new FilterCityCountry();
        $filter->setTransactionType($transaction);

        $form = $this->createForm(FilterCityCountryType::class, $filter, [
            'action' => $this->generateUrl('app_public_search'),
            'method' => 'POST',
        ]);

        /*
         * Modale de filtres : transaction et type de bien précochés.
         */
        $filtreModal = new ModalFilter();
        $filtreModal->setNatureDeLaPropriete($transaction);
        $filtreModal->setTypeDePropriete([$category]);

        $formModal = $this->createForm(ModalFilterType::class, $filtreModal, [
            'action' => $this->generateUrl('app_public_search_results', [
                'searchToken' => $searchToken,
                'view' => $view,
            ]),
            'method' => 'GET',
        ]);

        $properties = $this->propertyRepository->findForPublicSearch($modalFilter, $locale);

        $search = $this->paginator->paginate(
            $properties,
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
            'mapBounds' => null,
            'mapboxPublicToken' => $this->mapboxPublicToken,
            'mapboxPublicTokenCard' => $this->mapboxPublicTokenCard,
            'totalResults' => $search->getTotalItemCount(),
            'favoritePropertyIds' => [],
            'modal_filter' => $modalFilter,
        ]);
    }

    /**
     * Cherche la transaction par slug dans la locale courante, puis dans n'importe quelle locale.
     */
    private function findTransactionBySlug(string $slug, string $locale): ?CategoryBienTransaction
    {
        $repository = $this->entityManager->getRepository(CategoryBienTransactionTranslation::class);

        $translation = $repository->findOneBy(['slug' => $slug, 'locale' => $locale])
            ?? $repository->findOneBy(['slug' => $slug]);

        $transaction = $translation?->getTranslatable();

        return $transaction instanceof CategoryBienTransaction ? $transaction : null;
    }

    /**
     * Code ISO du pays dont le nom slugifié correspond, dans la locale courante puis en français / anglais.
     */
    private function findCountryCodeBySlug(string $slug, string $locale): ?string
    {
        $slugger = new AsciiSlugger();

        foreach (array_unique([$locale, 'fr', 'en']) as $countryLocale) {
            foreach (Countries::getNames($countryLocale) as $code => $name) {
                if ($slugger->slug($name)->lower()->toString() === $slug) {
                    return $code;
                }
            }
        }

        return null;
    }

    /**
     * Cherche le type de bien par slug dans la locale courante, puis dans n'importe quelle locale.
     */
    private function findCategoryBySlug(string $slug, string $locale): ?CategoryBien
    {
        $repository = $this->entityManager->getRepository(CategoryBienTranslation::class);

        $translation = $repository->findOneBy(['slug' => $slug, 'locale' => $locale])
            ?? $repository->findOneBy(['slug' => $slug]);

        $category = $translation?->getTranslatable();

        return $category instanceof CategoryBien ? $category : null;
    }
}
