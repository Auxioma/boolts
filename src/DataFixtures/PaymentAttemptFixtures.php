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

use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\Enum\PaymentAttemptStatus;
use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentAttempt;
use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class PaymentAttemptFixtures extends Fixture implements DependentFixtureInterface
{
    public const PAYMENT_ATTEMPT_REFERENCE_PREFIX = 'payment_attempt_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $paymentMethod = $this->getReference(
                BillingFixtureData::paymentMethodReference($agencyKey),
                AgencyPaymentMethod::class,
            );

            if ('free' !== BillingFixtureData::agencyPlanCode($position)) {
                $this->createAttempt(
                    $manager,
                    $currency,
                    $paymentMethod,
                    BillingFixtureData::subscriptionPaymentReference($agencyKey),
                    'subscription_'.$agencyKey,
                );
            }

            $this->createAttempt(
                $manager,
                $currency,
                $paymentMethod,
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
            AgencyPaymentMethodFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function createAttempt(
        ObjectManager $manager,
        Devise $currency,
        AgencyPaymentMethod $paymentMethod,
        string $paymentReference,
        string $referenceKey,
    ): void {
        $payment = $this->getReference($paymentReference, Payment::class);

        $attempt = (new PaymentAttempt())
            ->setPayment($payment)
            ->setPaymentMethod($paymentMethod)
            ->setAttemptNumber(1)
            ->setStatus(PaymentAttemptStatus::SUCCEEDED)
            ->setProviderPaymentIntentId($payment->getProviderPaymentIntentId())
            ->setProviderChargeId($payment->getProviderChargeId())
            ->setAmountMinor($payment->getAmountPaidMinor())
            ->setCurrency($currency)
            ->setCompletedAt($payment->getPaidAt());

        $manager->persist($attempt);
        $this->addReference(self::PAYMENT_ATTEMPT_REFERENCE_PREFIX.$referenceKey, $attempt);
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les tentatives de paiement.');
        }

        return $currency;
    }
}
