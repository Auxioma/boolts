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

use App\Entity\Billing\Enum\PaymentFeeType;
use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentFee;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class PaymentFeeFixtures extends Fixture implements DependentFixtureInterface
{
    public const PAYMENT_FEE_REFERENCE_PREFIX = 'payment_fee_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            if ('free' !== BillingFixtureData::agencyPlanCode($position)) {
                $this->createFee(
                    $manager,
                    $currency,
                    BillingFixtureData::subscriptionPaymentReference($agencyKey),
                    'subscription_'.$agencyKey,
                );
            }

            $this->createFee(
                $manager,
                $currency,
                BillingFixtureData::boosterPaymentReference($agencyKey),
                'booster_'.$agencyKey,
            );
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PaymentFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function createFee(
        ObjectManager $manager,
        Devise $currency,
        string $paymentReference,
        string $referenceKey,
    ): void {
        $payment = $this->getReference($paymentReference, Payment::class);

        $fee = (new PaymentFee())
            ->setPayment($payment)
            ->setType(PaymentFeeType::STRIPE_PROCESSING_FEE)
            ->setAmountMinor($payment->getFeeSettlementAmountMinor())
            ->setCurrency($currency)
            ->setProviderBalanceTransactionId($payment->getProviderBalanceTransactionId())
            ->setDescription('Frais de traitement Stripe fixture')
            ->setIsRefundable(false);

        $manager->persist($fee);
        $this->addReference(self::PAYMENT_FEE_REFERENCE_PREFIX.$referenceKey, $fee);
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les frais de paiement.');
        }

        return $currency;
    }
}
