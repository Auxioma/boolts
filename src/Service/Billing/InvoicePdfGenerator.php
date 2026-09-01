<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Service\Billing;

use App\Entity\Billing\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

/**
 * Rend une facture Boolts en PDF (A4) à partir du gabarit Twig partagé avec
 * l’affichage HTML, afin que la version consultée et la version téléchargée
 * soient strictement identiques.
 */
final readonly class InvoicePdfGenerator
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function render(Invoice $invoice): string
    {
        $html = $this->twig->render(
            'dashboard/agence_immobiliere/facture/show.html.twig',
            [
                'invoice' => $invoice,
                'pdf_mode' => true,
            ],
        );

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('defaultPaperSize', 'A4');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output() ?? '';
    }
}
