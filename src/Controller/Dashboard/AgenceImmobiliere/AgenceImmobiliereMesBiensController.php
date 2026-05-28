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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mes/biens', name: 'agence_immobiliere_')]
final class AgenceImmobiliereMesBiensController extends AbstractController
{
    #[Route('/', name: 'mes_biens')]
    public function index(Request $request): Response
    {
        $step = $request->query->getInt('step', 1);

        $mesBiens = new Property();
        $form = $this->createForm(MesBiensType::class, $mesBiens, [
            'step' => $step,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /* mettre en session step 1 */
            if (1 === $step) {
                $formName = $form->getName();
                $typeBienId = $request->request->all($formName)['typeBien'] ?? null;

                $request->getSession()->set('mes_biens_step_1', [
                    'typeBien' => $typeBienId,
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 2,
                ]);
            }
            /* mettre en session step 2 */
            if (2 === $step) {
                $formName = $form->getName();
                $typeTransactionId = $request->request->all($formName)['typeTransaction'] ?? null;

                $request->getSession()->set('mes_biens_step_2', [
                    'typeTransaction' => $typeTransactionId,
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 3,
                ]);
            }

            /* mettre en session le step 3 */
            if(3 === $step) {
                $formName = $form->getName();
                $adresseId = $request->request->all($formName)['adresse'] ?? null;
                $codePostal = $request->request->all($formName)['codePostal'] ?? null;
                $ville = $request->request->all($formName)['ville'] ?? null;
                $pays = $request->request->all($formName)['pays'] ?? null;

                $request->getSession()->set('mes_biens_step_3', [
                    'adresse' => $adresseId,
                    'codePostal' => $codePostal,
                    'ville' => $ville,
                    'pays' => $pays,
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 4,
                ]);
            }

                if (4 === $step) {
                    $formName = $form->getName();
                    $data = $request->request->all($formName);

                    $chambres = $data['chambres'] ?? null;
                    $salleDeBains = $data['salleDeBains'] ?? null;
                    $surfaceTotal = $data['surfaceTotal'] ?? null;
                    $anneeConstruction = $data['anneeConstruction'] ?? null;
                    $caracteristique = $data['caracteristique'] ?? [];

                    $request->getSession()->set('mes_biens_step_4', [
                        'chambres' => $chambres,
                        'salleDeBains' => $salleDeBains,
                        'surfaceTotal' => $surfaceTotal,
                        'anneeConstruction' => $anneeConstruction,
                        'caracteristique' => $caracteristique,
                    ]);

                    dd($request->getSession()->all());

                    return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                        'step' => 5,
                    ]);
                }
        }

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/index.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
        ]);
    }
}
