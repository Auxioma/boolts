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

use App\Entity\Property;
use App\Form\Dashboard\AgenceImmobiliere\MesBiensType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\NumericSlugGenerator;

#[Route('/mes/biens', name: 'agence_immobiliere_')]
final class AgenceImmobiliereMesBiensController extends AbstractController
{
    #[Route('/', name: 'mes_biens')]
    public function index(
        Request $request,
        PropertyRepository $propertyRepository,
        EntityManagerInterface $entityManager,
        NumericSlugGenerator $numericSlugGenerator,
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
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName() ?? '',
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
                    $session->set('typeTransaction', mb_strtolower($transaction->getName()));
                }

                $this->updateReachedStep($session, 3);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 3,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 3 : adresse
            |--------------------------------------------------------------------------
            */
            if (3 === $step) {
                $entityManager->flush();

                $this->updateReachedStep($session, 4);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 4,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
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
                        'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
                    ]);
                }

                $this->updateReachedStep($session, 5);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 5,
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
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
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
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
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Step 7 : description
            |--------------------------------------------------------------------------
            */
            if (7 === $step) {
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
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
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
                    'typeTransaction' => $mesBiens->getTypeTransaction()?->getName(),
                ]);
            }
        }

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/index.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
            'stepperStep' => $session->get('mes_biens_reached_step', $step),
        ]);
    }

    #[Route('/status', name: 'mes_biens_status')]
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
