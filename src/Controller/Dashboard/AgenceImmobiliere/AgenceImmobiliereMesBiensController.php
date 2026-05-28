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
use App\Repository\CaracteristiqueRepository;
use App\Repository\CategoryBienRepository;
use App\Repository\CategoryBienTransactionRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mes/biens', name: 'agence_immobiliere_')]
final class AgenceImmobiliereMesBiensController extends AbstractController
{
    #[Route('/', name: 'mes_biens')]
    public function index(
        Request $request,
        PropertyRepository $propertyRepository,
        EntityManagerInterface $entityManager): Response
    {
        $step = $request->query->getInt('step', 1);

        $propertyId = $request->getSession()->get('mes_biens_property_id');

        if ($propertyId) {
            $mesBiens = $propertyRepository->find($propertyId);
            if (!$mesBiens) {
                $mesBiens = new Property();
            }
        } else {
            $mesBiens = new Property();
        }

        $form = $this->createForm(MesBiensType::class, $mesBiens, [
            'step' => $step,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /* mettre en session step 1 */
            if (1 === $step) {
                $entityManager->persist($mesBiens);

                $entityManager->flush();

                $request->getSession()->set(
                    'mes_biens_property_id',
                    $mesBiens->getId()
                );

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 2,
                ]);
            }
            /* mettre en session step 2 */
            if (2 === $step) {
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 3,
                ]);
            }

            /* mettre en session le step 3 */
            if (3 === $step) {
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 4,
                ]);
            }
            /* mettre en session le step 4 */
            if (4 === $step) {
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 5,
                ]);
            }
            /* mettre en session le step 5 */
            if (5 === $step) {
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 6,
                ]);
            }

            if (6 === $step) {
                foreach ($mesBiens->getPropertyImages() as $index => $propertyImage) {
                    $propertyImage->setProperty($mesBiens);
                    $propertyImage->setPosition($index + 1);
                    $propertyImage->setSize(
                        $propertyImage->getImageFile()?->getSize()
                    );
                }

                $entityManager->persist($mesBiens);
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 7,
                ]);
            }

            if (7 === $step) {
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 8,
                ]);
            }

            if (8 === $step) {
                $entityManager->flush();

                return $this->redirectToRoute('agence_immobiliere_mes_biens_status');
            }
        }

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/index.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
        ]);
    }

    #[Route('/status', name: 'mes_biens_status')]
    public function status(): Response
    {
        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/status.html.twig');
    }
}
