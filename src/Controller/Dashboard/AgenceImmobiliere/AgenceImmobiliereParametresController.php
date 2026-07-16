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

use App\Form\Dashboard\AgenceImmobiliere\ProfileAgenceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/parametres', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
final class AgenceImmobiliereParametresController extends AbstractController
{
    #[Route('/', name: 'parametres')]
    public function index(): Response
    {
        $user = $this->getUser();

        dd($user);

        $form = $this->createForm(ProfileAgenceType::class, $user);

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_parametres/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
