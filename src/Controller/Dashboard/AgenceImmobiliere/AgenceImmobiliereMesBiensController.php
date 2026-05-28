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
            if (3 === $step) {
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
            /* mettre en session le step 4 */
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

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 5,
                ]);
            }
            /* mettre en session le step 5 */
            if (5 === $step) {
                $formName = $form->getName();
                $data = $request->request->all($formName);

                $request->getSession()->set('mes_biens_step_5', [
                    'dpe' => $data['dpe'] ?? null,
                    'ges' => $data['ges'] ?? null,
                    'dpeMax' => $data['dpeMax'] ?? null,
                    'dpeMin' => $data['dpeMin'] ?? null,
                    'dateIndexationEnergie' => $data['dateIndexationEnergie'] ?? null,
                    'dpeLettre' => $data['dpeLettre'] ?? null,
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 6,
                ]);
            }

            if (6 === $step) {
                $formName = $form->getName();
                $data = $request->request->all($formName);

                $request->getSession()->set('mes_biens_step_6', [
                    'propertyImages' => $data['propertyImages'] ?? [],
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 7,
                ]);
            }

            if (7 === $step) {
                $formName = $form->getName();
                $data = $request->request->all($formName);

                $request->getSession()->set('mes_biens_step_7', [
                    'titreDuLogement' => $data['titreDuLogement'] ?? null,
                    'descriptionLogement' => $data['descriptionLogement'] ?? null,
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 8,
                ]);
            }

            if (8 === $step) {
                $formName = $form->getName();
                $data = $request->request->all($formName);

                $request->getSession()->set('mes_biens_step_8', [
                    'prix' => $data['prix'] ?? null,
                    'referenceInterne' => $data['referenceInterne'] ?? null,
                    'montantDepotDeGarantie' => $data['montantDepotDeGarantie'] ?? null,
                    'montantLoyerHorsCharge' => $data['montantLoyerHorsCharge'] ?? null,
                    'montantDesCharges' => $data['montantDesCharges'] ?? null,
                ]);

                return $this->redirectToRoute('agence_immobiliere_mes_biens', [
                    'step' => 9,
                ]);
            }
        }

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_biens/index.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
        ]);
    }
}
