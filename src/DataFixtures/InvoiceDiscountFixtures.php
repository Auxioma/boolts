<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\Invoice;
use App\Entity\Billing\InvoiceDiscount;
use App\Entity\Billing\InvoiceLine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class InvoiceDiscountFixtures extends Fixture implements DependentFixtureInterface
{
    public const INVOICE_DISCOUNT_REFERENCE_PREFIX = 'invoice_discount_';

    public function load(ObjectManager $manager): void
    {
        $starterAgencyKey = BillingFixtureData::firstAgencyKeyForPlan('starter');
        $invoice = $this->getReference(
            BillingFixtureData::subscriptionInvoiceReference($starterAgencyKey),
            Invoice::class,
        );
        $line = $this->getReference(
            InvoiceLineFixtures::INVOICE_LINE_REFERENCE_PREFIX.'subscription_'.$starterAgencyKey,
            InvoiceLine::class,
        );

        $discount = (new InvoiceDiscount())
            ->setInvoice($invoice)
            ->setInvoiceLine($line)
            ->setCode('WELCOME')
            ->setDescription('Remise de bienvenue fixture')
            ->setType('coupon')
            ->setPercentage('0.0000')
            ->setAmountMinor(0)
            ->setProviderCouponId('coupon_fixture_welcome');

        $manager->persist($discount);
        $this->addReference(self::INVOICE_DISCOUNT_REFERENCE_PREFIX.'welcome', $discount);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
            InvoiceLineFixtures::class,
        ];
    }
}
