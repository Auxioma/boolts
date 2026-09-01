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

use App\Entity\Billing\Invoice;
use App\Entity\User;
use App\Repository\Billing\InvoiceRepository;
use App\Service\Billing\InvoicePdfGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/factures', name: 'agence_immobiliere_facture_')]
#[IsGranted('ROLE_AGENCE')]
/**
 * Consultation et téléchargement des factures d'abonnement d'une agence.
 */
final class AgenceImmobiliereFactureController extends AbstractController
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoicePdfGenerator $pdfGenerator,
    ) {
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        return $this->render(
            'dashboard/agence_immobiliere/facture/show.html.twig',
            [
                'invoice' => $this->invoice($id),
                'pdf_mode' => false,
            ],
        );
    }

    #[Route('/{id}/telecharger', name: 'download', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function download(int $id): Response
    {
        $invoice = $this->invoice($id);

        $response = new Response($this->pdfGenerator->render($invoice));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                \sprintf('facture-%s.pdf', $invoice->getNumber()),
            ),
        );

        return $response;
    }

    private function invoice(int $id): Invoice
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $invoice = $this->invoiceRepository->findOneForAgency($id, $user);

        if (!$invoice instanceof Invoice) {
            throw $this->createNotFoundException('Facture introuvable.');
        }

        return $invoice;
    }
}
