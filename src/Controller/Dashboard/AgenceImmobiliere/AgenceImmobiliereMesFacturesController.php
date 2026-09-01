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

use App\Entity\User;
use App\Repository\Billing\InvoiceRepository;
use App\Security\Voter\AgencyDocumentVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/mes/factures', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
#[IsGranted(
    AgencyDocumentVoter::ACCESS_RESTRICTED_DASHBOARD,
    message: 'Vos documents doivent être validés pour accéder à cette page.',
)]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / AgenceImmobiliereMesFacturesController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereMesFacturesController extends AbstractController
{
    #[Route('/', name: 'mes_factures')]
    /**
     * Handles the index controller action.
     */
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $search = $request->query->getString('q');

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_mes_factures/index.html.twig', [
            'controller_name' => 'AgenceImmobiliereMesFacturesController',
            'invoices' => $invoiceRepository->findForAgency($user, $search),
            'search' => $search,
        ]);
    }
}
