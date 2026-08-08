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

use App\Entity\Billing\Invoice;
use App\Entity\Billing\InvoiceLine;
use App\Entity\Billing\InvoiceTax;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class InvoiceTaxFixtures extends Fixture implements DependentFixtureInterface
{
    public const INVOICE_TAX_REFERENCE_PREFIX = 'invoice_tax_';

    public function load(ObjectManager $manager): void
    {
        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            if ('free' !== BillingFixtureData::agencyPlanCode($position)) {
                $this->createTax($manager, $agencyKey, 'subscription');
            }

            $this->createTax($manager, $agencyKey, 'booster');
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
            InvoiceLineFixtures::class,
        ];
    }

    private function createTax(ObjectManager $manager, string $agencyKey, string $kind): void
    {
        $invoiceReference = 'subscription' === $kind
            ? BillingFixtureData::subscriptionInvoiceReference($agencyKey)
            : BillingFixtureData::boosterInvoiceReference($agencyKey);
        $invoice = $this->getReference($invoiceReference, Invoice::class);
        $line = $this->getReference(
            InvoiceLineFixtures::INVOICE_LINE_REFERENCE_PREFIX.$kind.'_'.$agencyKey,
            InvoiceLine::class,
        );

        $tax = (new InvoiceTax())
            ->setInvoice($invoice)
            ->setInvoiceLine($line)
            ->setName('TVA non applicable')
            ->setType('vat')
            ->setCountryCode('FR')
            ->setRate('0.00000')
            ->setTaxableAmountMinor($line->getTaxableAmountMinor())
            ->setAmountMinor(0)
            ->setInclusive(false)
            ->setTaxBehavior('exclusive')
            ->setProviderTaxRateId('txr_fixture_'.$kind.'_'.$agencyKey);

        $manager->persist($tax);
        $this->addReference(self::INVOICE_TAX_REFERENCE_PREFIX.$kind.'_'.$agencyKey, $tax);
    }
}
