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

use App\Entity\Filter\ModalFilter;
use App\Entity\Property;
use App\Entity\User;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Form\Dashboard\AgenceImmobiliere\MesBiensType;
use App\Form\Filter\ModalFilterType;
use App\Repository\PropertyRepository;
use App\Service\MapboxAddressTranslator;
use App\Service\NumericSlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mes/biens', name: 'agence_immobiliere_')]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / AgenceImmobiliereMesBiensController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereMesBiensController extends AbstractController
{
    #[Route('/liste', name: 'mes_biens_list', methods: ['GET'])]
    public function list(
        PropertyRepository $propertyRepository,
        PaginatorInterface $paginator,
        Request $request,
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
            'p.createdAt'
        );

        $direction = $request->query->getString(
            'direction',
            'DESC'
        );

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
            10
        );

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_mes_biens/list.html.twig',
            [
                'properties' => $properties,
                'filterForm' => $filterForm->createView(),
                'modal_filter' => $filters,
                'searchValue' => $search,
                'sortValue' => $sort,
                'directionValue' => $direction,
                'totalResults' => $properties->getTotalItemCount(),
            ]
        );
    }

    #[Route('/liste/filtres/count',name: 'mes_biens_filters_count',methods: ['GET']
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
            throw $this->createAccessDeniedException(
                'Token CSRF invalide.'
            );
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
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas modifier cette annonce.'
            );
        }

        $session = $request->getSession();

        $session->set(
            'mes_biens_property_id',
            $property->getId()
        );

        $session->set(
            'mes_biens_reached_step',
            8
        );

        $transaction = $property->getTypeTransaction();

        if ($transaction) {
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

        return $this->redirectToRoute(
            'agence_immobiliere_mes_biens',
            [
                'step' => 1,
            ]
        );
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
            throw $this->createAccessDeniedException(
                'Token CSRF invalide.'
            );
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
            throw $this->createAccessDeniedException(
                'Token CSRF invalide.'
            );
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

    // action groupey 
    #[Route('/actions-groupees',name: 'mes_biens_bulk_action',methods: ['POST'])]
    public function bulkAction(Request $request,PropertyRepository $propertyRepository,EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid(
            'property_bulk_action',
            $request->request->getString('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Token CSRF invalide.'
            );
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
    /**
     * Handles the index controller action.
     */
    public function index(
        Request $request,
        PropertyRepository $propertyRepository,
        EntityManagerInterface $entityManager,
        NumericSlugGenerator $numericSlugGenerator,
        MapboxAddressTranslator $mapboxAddressTranslator,
    ): Response {
        $session = $request->getSession();
        $step = $request->query->getInt('step', 1);

        /*
        |--------------------------------------------------------------------------
        | Étape maximale atteinte
        |--------------------------------------------------------------------------
        |
        | step        = étape réellement affichée
        | stepperStep = étape maximale atteinte pour garder la barre bleue
        */
        if (!$session->has('mes_biens_reached_step')) {
            $session->set('mes_biens_reached_step', 1);
        }

        $propertyId = $session->get(
            'mes_biens_property_id'
        );

        if ($propertyId) {
            $mesBiens = $propertyRepository->find(
                $propertyId
            );

            if (!$mesBiens) {
                $this->clearMesBiensSession($session);

                $mesBiens = new Property();
            } else {
                $user = $this->getUser();

                if (
                    !$user instanceof User
                    || $mesBiens->getUser()?->getId() !== $user->getId()
                ) {
                    $this->clearMesBiensSession($session);

                    throw $this->createAccessDeniedException(
                        'Vous ne pouvez pas modifier cette annonce.'
                    );
                }
            }
        } else {
            $mesBiens = new Property();
        }

        $typeTransaction = $session->get('typeTransaction');

        $form = $this->createForm(MesBiensType::class, $mesBiens, [
            'step' => $step,
            'typeTransaction' => $typeTransaction,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /*
            |--------------------------------------------------------------------------
            | Step 1 : type de bien
            |--------------------------------------------------------------------------
            */

            if (1 === $step) {
                if (null === $mesBiens->getId()) {
                    $mesBiens->setSlug(
                        $numericSlugGenerator->generate(16)
                    );

                    $entityManager->persist(
                        $mesBiens
                    );
                }

                $entityManager->flush();

                $session->set(
                    'mes_biens_property_id',
                    $mesBiens->getId()
                );

                $this->updateReachedStep(
                    $session,
                    2
                );

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    [
                        'step' => 2,
                        'typeTransaction' => $mesBiens
                            ->getTypeTransaction()
                            ?->getId() ?? '',
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Step 2 : type de transaction
            |--------------------------------------------------------------------------
            */
            if (2 === $step) {
                $entityManager->flush();

                $transaction = $mesBiens->getTypeTransaction();

                if ($transaction) {
                    $slugFr = $transaction->translate('fr')->getSlug();

                    $typeTransactionCode = match ($slugFr) {
                        'vente' => '1',
                        'location' => '2',
                        default => null,
                    };

                    $session->set('typeTransaction', $typeTransactionCode);
            }

                $this->updateReachedStep($session, 3);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 3,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 3 : adresse
            |--------------------------------------------------------------------------
            */
            if (3 === $step) {
                $currentLocale = $request->getLocale();
                $mapboxId = $mesBiens->getMapboxId();
                $sessionToken = $mesBiens->getSessionIdMapbox();

                $isFixtureMapboxId = null !== $mapboxId
                    && str_starts_with(
                        $mapboxId,
                        'fixture-'
                    );

                if (
                    $mapboxId
                    && !$isFixtureMapboxId
                ) {
                    foreach (['fr', 'en'] as $locale) {
                        $address = $mapboxAddressTranslator->translateByMapboxId(
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
                $entityManager->flush();

                $this->updateReachedStep(
                    $session,
                    4
                );
                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens',
                    [
                        'step' => 4,
                        'typeTransaction' => $mesBiens
                            ->getTypeTransaction()
                            ?->getId(),
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Step 4 : caractéristiques
            |--------------------------------------------------------------------------
            */
            if (4 === $step) {
                $entityManager->flush();

                /*
                |--------------------------------------------------------------------------
                | Si le pays n’est pas la France, on saute le bilan énergétique
                |--------------------------------------------------------------------------
                */
                if ('FR' !== $mesBiens->getPays()) {
                    $this->updateReachedStep($session, 6);

                    return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                        'step' => 6,
                        'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                    ]);
                }

                $this->updateReachedStep($session, 5);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 5,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 5 : bilan énergétique
            |--------------------------------------------------------------------------
            */
            if (5 === $step) {
                $entityManager->flush();

                $this->updateReachedStep($session, 6);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 6,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 6 : photos
            |--------------------------------------------------------------------------
            */
            if (6 === $step) {
                foreach ($mesBiens->getPropertyImages() as $index => $propertyImage) {
                    $propertyImage->setProperty($mesBiens);
                    $propertyImage->setPosition($index + 1);
                }

                $entityManager->persist($mesBiens);
                $entityManager->flush();

                $this->updateReachedStep($session, 7);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 7,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 7 : description
            |--------------------------------------------------------------------------
            */
            if (7 === $step) {
                /*
                 * traduit le titre et description
                 */
                $mesBiens->translate('fr')->setTitreDuLogement($form->get('titreDuLogement')->getData());
                $mesBiens->translate('fr')->setDescriptionLogement($form->get('descriptionLogement')->getData());

                $mesBiens->translate('en')->setTitreDuLogement($form->get('titreDuLogement')->getData());
                $mesBiens->translate('en')->setDescriptionLogement($form->get('descriptionLogement')->getData());

                $mesBiens->mergeNewTranslations();

                $entityManager->flush();

                $typeTransaction = $session->get('typeTransaction');

                if (null === $typeTransaction) {
                    return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                        'step' => 2,
                    ]);
                }

                $this->updateReachedStep($session, 8);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 8,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 8 : prix
            |--------------------------------------------------------------------------
            */

            if (8 === $step) {
                $entityManager->flush();

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

                return $this->redirectToRoute(
                    'agence_immobiliere_mes_biens_status',
                    [
                        'typeTransaction' => $mesBiens
                            ->getTypeTransaction()
                            ?->getId(),
                    ]
                );
            }
        }

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/index.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
            'stepperStep' => $session->get('mes_biens_reached_step', $step),
            'typeTransaction' => $typeTransaction,

        ]);
    }

    #[Route('/status', name: 'mes_biens_status')]
    /**
     * Handles the status controller action.
     */
    public function status(): Response
    {
        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/status.html.twig');
    }

    private function updateReachedStep(SessionInterface $session, int $step): void
    {
        $currentReachedStep = $session->get('mes_biens_reached_step', 1);

        if ($step > $currentReachedStep) {
            $session->set('mes_biens_reached_step', $step);
        }
    }

    private function clearMesBiensSession(
        SessionInterface $session
    ): void {
        $session->remove(
            'mes_biens_property_id'
        );

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
