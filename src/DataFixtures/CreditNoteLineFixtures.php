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

namespace App\DataFixtures;

use App\Entity\Billing\CreditNote;
use App\Entity\Billing\CreditNoteLine;
use App\Entity\Billing\InvoiceLine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class CreditNoteLineFixtures extends Fixture implements DependentFixtureInterface
{
    public const CREDIT_NOTE_LINE_REFERENCE_PREFIX = 'credit_note_line_';

    public function load(ObjectManager $manager): void
    {
        $creditNote = $this->getReference(CreditNoteFixtures::MAIN_CREDIT_NOTE_REFERENCE, CreditNote::class);
        $invoiceLine = $this->getReference(
            InvoiceLineFixtures::INVOICE_LINE_REFERENCE_PREFIX.'booster_main',
            InvoiceLine::class,
        );

        $line = (new CreditNoteLine())
            ->setCreditNote($creditNote)
            ->setInvoiceLine($invoiceLine)
            ->setDescription('Remboursement partiel du pack boost fixture')
            ->setQuantity('1.000')
            ->setUnitAmountMinor(500)
            ->setSubtotalMinor(500)
            ->setTaxAmountMinor(0)
            ->setTotalMinor(500)
            ->setPosition(1);

        $manager->persist($line);
        $this->addReference(self::CREDIT_NOTE_LINE_REFERENCE_PREFIX.'main_booster', $line);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CreditNoteFixtures::class,
            InvoiceLineFixtures::class,
        ];
    }
}
