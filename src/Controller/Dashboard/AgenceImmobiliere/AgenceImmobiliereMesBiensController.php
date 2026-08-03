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

namespace App\Controller\Dashboard\AgenceImmobiliere;

use App\Entity\Filter\ModalFilter;
use App\Entity\Property;
use App\Entity\User;
use App\Form\Dashboard\AgenceImmobiliere\MesBiensType;
use App\Form\Filter\ModalFilterType;
use App\Repository\PropertyRepository;
use App\Service\MapboxAddressTranslator;
use App\Service\NumericSlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
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
        Request $request,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $search = $request
            ->query
            ->getString('search');

        $sort = $request
            ->query
            ->getString(
                'sort',
                'p.createdAt'
            );

        $direction = $request
            ->query
            ->getString(
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

        $filters = $request
            ->query
            ->has('modal_filter')
            ? $request
                ->query
                ->all('modal_filter')
            : [];

        $properties = $propertyRepository
            ->findPropertysByUserWithFiltersQuery(
                user: $user,
                search: $search,
                filters: $filters,
                sort: $sort,
                direction: $direction,
                locale: $request->getLocale(),
            )
            ->getQuery()
            ->getResult();

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_mes_biens/list.html.twig',
            [
                'properties' => $properties,
                'filterForm' => $filterForm->createView(),
                'modal_filter' => $filters,
                'totalResults' => \count($properties),

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

        $propertyId = $session->get('mes_biens_property_id');

        if ($propertyId) {
            $mesBiens = $propertyRepository->find($propertyId);

            if (!$mesBiens) {
                $this->clearMesBiensSession($session);
                $mesBiens = new Property();
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
                $mesBiens->setSlug($numericSlugGenerator->generate(16));
                $entityManager->persist($mesBiens);
                $entityManager->flush();

                $session->set('mes_biens_property_id', $mesBiens->getId());

                $this->updateReachedStep($session, 2);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 2,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId() ?? '',
                ]);
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

                if ($mapboxId) {
                    foreach (['fr', 'en'] as $locale) {
                        $address = $mapboxAddressTranslator->translateByMapboxId(
                            mapboxId: $mapboxId,
                            sessionToken: $sessionToken,
                            locale: $locale
                        );

                        if (null === $address) {
                            continue;
                        }

                        $translation = $mesBiens->translate($locale);

                        $translation->setAdresse($address['adresse']);
                        $translation->setVille($address['ville']);
                        $translation->setPays($address['pays']);
                        $translation->setFullAddress($address['fullAddress']);
                        $translation->setRegion($address['region']);
                        $translation->setDistrict($address['district']);
                        $translation->setLocality($address['locality']);
                        $translation->setNeighborhood($address['neighborhood']);
                        $translation->setPoi($address['poi']);
                    }

                    $mesBiens->mergeNewTranslations();
                }

                $entityManager->flush();

                $this->updateReachedStep($session, 4);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 4,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
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

                $this->clearMesBiensSession($session);

                return $this->redirectToRoute('agence_immobiliere_mes_biens_status', [
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getId(),
                ]);
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

    private function clearMesBiensSession(SessionInterface $session): void
    {
        $session->remove('mes_biens_property_id');
        $session->remove('typeTransaction');
        $session->remove('mes_biens_reached_step');
    }
}
