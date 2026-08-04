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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class InvoiceLineFixtures extends Fixture implements DependentFixtureInterface
{
    public const INVOICE_LINE_REFERENCE_PREFIX = 'invoice_line_';

    public function load(ObjectManager $manager): void
    {
        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            if ('free' !== BillingFixtureData::agencyPlanCode($position)) {
                $invoice = $this->getReference(
                    BillingFixtureData::subscriptionInvoiceReference($agencyKey),
                    Invoice::class,
                );
                $subscriptionName = $invoice->getSubscription()?->getPlan()->getName() ?? 'forfait';
                $line = $this->line($invoice, 'subscription', 'Abonnement '.$subscriptionName, 1);

                $manager->persist($line);
                $this->addReference(self::INVOICE_LINE_REFERENCE_PREFIX.'subscription_'.$agencyKey, $line);
            }

            $boosterInvoice = $this->getReference(
                BillingFixtureData::boosterInvoiceReference($agencyKey),
                Invoice::class,
            );
            $boosterLine = $this->line($boosterInvoice, 'booster_pack', 'Pack boost', 1);

            $manager->persist($boosterLine);
            $this->addReference(self::INVOICE_LINE_REFERENCE_PREFIX.'booster_'.$agencyKey, $boosterLine);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
        ];
    }

    private function line(Invoice $invoice, string $type, string $description, int $position): InvoiceLine
    {
        return (new InvoiceLine())
            ->setInvoice($invoice)
            ->setType($type)
            ->setDescription($description)
            ->setQuantity('1.000')
            ->setUnitAmountMinor($invoice->getSubtotalMinor())
            ->setSubtotalMinor($invoice->getSubtotalMinor())
            ->setDiscountAmountMinor($invoice->getDiscountTotalMinor())
            ->setTaxableAmountMinor($invoice->getTaxableTotalMinor())
            ->setTaxAmountMinor($invoice->getTaxTotalMinor())
            ->setTotalMinor($invoice->getTotalMinor())
            ->setPeriodStart($invoice->getSubscriptionPeriod()?->getPeriodStart())
            ->setPeriodEnd($invoice->getSubscriptionPeriod()?->getPeriodEnd())
            ->setPosition($position);
    }
}
