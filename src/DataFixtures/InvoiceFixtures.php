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

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\InvoiceStatus;
use App\Entity\Billing\Enum\InvoiceType;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Payment;
use App\Entity\Devise;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    public const INVOICE_SUBSCRIPTION_REFERENCE_PREFIX = 'invoice_subscription_';
    public const INVOICE_BOOSTER_PACK_REFERENCE_PREFIX = 'invoice_booster_pack_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $agency = $this->getReference(BillingFixtureData::agencyReferences()[$agencyKey], User::class);
            $billingProfile = $this->getReference(
                BillingFixtureData::profileReference($agencyKey),
                AgencyBillingProfile::class,
            );

            if ('free' !== BillingFixtureData::agencyPlanCode($position)) {
                $subscription = $this->getReference(
                    BillingFixtureData::subscriptionReference($agencyKey),
                    AgencySubscription::class,
                );
                $period = $this->getReference(
                    BillingFixtureData::subscriptionPeriodReference($agencyKey),
                    AgencySubscriptionPeriod::class,
                );
                $payment = $this->getReference(
                    BillingFixtureData::subscriptionPaymentReference($agencyKey),
                    Payment::class,
                );

                $invoice = $this->invoice(
                    agency: $agency,
                    billingProfile: $billingProfile,
                    payment: $payment,
                    currency: $currency,
                    number: 'FAC-SUB-'.mb_strtoupper($agencyKey),
                    type: InvoiceType::SUBSCRIPTION,
                    providerInvoiceId: 'in_fixture_subscription_'.$agencyKey,
                    subscription: $subscription,
                    period: $period,
                    customerKey: $agencyKey,
                );

                $manager->persist($invoice);
                $this->addReference(BillingFixtureData::subscriptionInvoiceReference($agencyKey), $invoice);
            }

            $boosterPayment = $this->getReference(
                BillingFixtureData::boosterPaymentReference($agencyKey),
                Payment::class,
            );

            $boosterInvoice = $this->invoice(
                agency: $agency,
                billingProfile: $billingProfile,
                payment: $boosterPayment,
                currency: $currency,
                number: 'FAC-BOOST-'.mb_strtoupper($agencyKey),
                type: InvoiceType::BOOSTER_PACK,
                providerInvoiceId: 'in_fixture_booster_'.$agencyKey,
                subscription: null,
                period: null,
                customerKey: $agencyKey,
            );

            $manager->persist($boosterInvoice);
            $this->addReference(BillingFixtureData::boosterInvoiceReference($agencyKey), $boosterInvoice);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AgencyBillingProfileFixtures::class,
            AgencySubscriptionFixtures::class,
            AgencySubscriptionPeriodFixtures::class,
            PaymentFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function invoice(
        User $agency,
        AgencyBillingProfile $billingProfile,
        Payment $payment,
        Devise $currency,
        string $number,
        InvoiceType $type,
        string $providerInvoiceId,
        ?AgencySubscription $subscription,
        ?AgencySubscriptionPeriod $period,
        string $customerKey,
    ): Invoice {
        $paidAt = $payment->getPaidAt() ?? new \DateTimeImmutable('-5 days');

        return (new Invoice())
            ->setNumber($number)
            ->setAgency($agency)
            ->setBillingProfile($billingProfile)
            ->setSubscription($subscription)
            ->setSubscriptionPeriod($period)
            ->setPayment($payment)
            ->setStatus(InvoiceStatus::PAID)
            ->setType($type)
            ->setCurrency($currency)
            ->setSubtotalMinor($payment->getAmountSubtotalMinor())
            ->setDiscountTotalMinor($payment->getDiscountAmountMinor())
            ->setTaxableTotalMinor($payment->getAmountSubtotalMinor() - $payment->getDiscountAmountMinor())
            ->setTaxTotalMinor($payment->getTaxAmountMinor())
            ->setTotalMinor($payment->getAmountTotalMinor())
            ->setAmountPaidMinor($payment->getAmountPaidMinor())
            ->setAmountDueMinor(0)
            ->setAmountRefundedMinor($payment->getAmountRefundedMinor())
            ->setSellerSnapshot(BillingFixtureData::sellerSnapshot())
            ->setCustomerSnapshot(BillingFixtureData::customerSnapshot($customerKey))
            ->setTaxSnapshot(BillingFixtureData::taxSnapshot())
            ->setProviderInvoiceId($providerInvoiceId)
            ->setProviderInvoicePdfUrl('https://stripe.test/'.$providerInvoiceId.'.pdf')
            ->setProviderHostedInvoiceUrl('https://stripe.test/'.$providerInvoiceId)
            ->setIssuedAt($paidAt)
            ->setDueAt($paidAt)
            ->setPaidAt($paidAt);
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les factures.');
        }

        return $currency;
    }
}
