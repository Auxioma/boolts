<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\PaymentStatus;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\Payment;
use App\Entity\Booster\BoosterPack;
use App\Entity\Devise;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class PaymentFixtures extends Fixture implements DependentFixtureInterface
{
    public const PAYMENT_SUBSCRIPTION_REFERENCE_PREFIX = 'payment_subscription_';
    public const PAYMENT_BOOSTER_PACK_REFERENCE_PREFIX = 'payment_booster_pack_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);
        $paidAt = new \DateTimeImmutable('-5 days');
        $packCodes = array_keys(BillingFixtureData::BOOSTER_PRICES);

        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $planCode = BillingFixtureData::agencyPlanCode($position);
            $agency = $this->getReference(BillingFixtureData::agencyReferences()[$agencyKey], User::class);
            $billingProfile = $this->getReference(
                BillingFixtureData::profileReference($agencyKey),
                AgencyBillingProfile::class,
            );
            $paymentMethod = $this->getReference(
                BillingFixtureData::paymentMethodReference($agencyKey),
                AgencyPaymentMethod::class,
            );
            $subscription = $this->getReference(
                BillingFixtureData::subscriptionReference($agencyKey),
                AgencySubscription::class,
            );
            $last4 = $paymentMethod->getLast4() ?? '4242';

            if ('free' !== $planCode) {
                $subscriptionAmountMinor = BillingFixtureData::SUBSCRIPTION_PRICES[SubscriptionBillingPeriod::MONTHLY->value][$planCode];
                $subscriptionPayment = (new Payment())
                    ->setReference('FIXTURE-SUB-'.strtoupper($agencyKey))
                    ->setAgency($agency)
                    ->setBillingProfile($billingProfile)
                    ->setPaymentMethod($paymentMethod)
                    ->setSubscription($subscription)
                    ->setType(PaymentType::SUBSCRIPTION_RENEWAL)
                    ->setStatus(PaymentStatus::SUCCEEDED)
                    ->setAmountSubtotalMinor($subscriptionAmountMinor)
                    ->setAmountTotalMinor($subscriptionAmountMinor)
                    ->setAmountPaidMinor($subscriptionAmountMinor)
                    ->setCurrency($currency)
                    ->setSettlementCurrency($currency)
                    ->setGrossSettlementAmountMinor($subscriptionAmountMinor)
                    ->setFeeSettlementAmountMinor($this->providerFee($subscriptionAmountMinor))
                    ->setNetSettlementAmountMinor($subscriptionAmountMinor - $this->providerFee($subscriptionAmountMinor))
                    ->setProviderPaymentIntentId('pi_fixture_sub_'.$agencyKey)
                    ->setProviderChargeId('ch_fixture_sub_'.$agencyKey)
                    ->setProviderInvoiceId('in_fixture_sub_'.$agencyKey)
                    ->setProviderBalanceTransactionId('txn_fixture_sub_'.$agencyKey)
                    ->setPaymentMethodSnapshot(BillingFixtureData::paymentSnapshot($last4))
                    ->setMetadata(['fixture' => true, 'kind' => 'subscription', 'plan' => $planCode])
                    ->setPaidAt($paidAt);

                $manager->persist($subscriptionPayment);
                $this->addReference(BillingFixtureData::subscriptionPaymentReference($agencyKey), $subscriptionPayment);
            }

            $packCode = $packCodes[$position % count($packCodes)];
            $boosterPack = $this->getReference(
                BoosterPackFixtures::BOOSTER_PACK_REFERENCE_PREFIX.$packCode,
                BoosterPack::class,
            );
            $boosterAmountMinor = BillingFixtureData::BOOSTER_PRICES[$packCode];
            $refundedMinor = 'main' === $agencyKey ? 500 : 0;

            $boosterPayment = (new Payment())
                ->setReference('FIXTURE-BOOST-'.strtoupper($agencyKey))
                ->setAgency($agency)
                ->setBillingProfile($billingProfile)
                ->setPaymentMethod($paymentMethod)
                ->setBoosterPack($boosterPack)
                ->setType(PaymentType::BOOSTER_PACK)
                ->setStatus($refundedMinor > 0 ? PaymentStatus::PARTIALLY_REFUNDED : PaymentStatus::SUCCEEDED)
                ->setAmountSubtotalMinor($boosterAmountMinor)
                ->setAmountTotalMinor($boosterAmountMinor)
                ->setAmountPaidMinor($boosterAmountMinor)
                ->setAmountRefundedMinor($refundedMinor)
                ->setCurrency($currency)
                ->setSettlementCurrency($currency)
                ->setGrossSettlementAmountMinor($boosterAmountMinor)
                ->setFeeSettlementAmountMinor($this->providerFee($boosterAmountMinor))
                ->setNetSettlementAmountMinor($boosterAmountMinor - $this->providerFee($boosterAmountMinor))
                ->setProviderPaymentIntentId('pi_fixture_boost_'.$agencyKey)
                ->setProviderChargeId('ch_fixture_boost_'.$agencyKey)
                ->setProviderBalanceTransactionId('txn_fixture_boost_'.$agencyKey)
                ->setPaymentMethodSnapshot(BillingFixtureData::paymentSnapshot($last4))
                ->setMetadata(['fixture' => true, 'kind' => 'booster_pack', 'pack' => $packCode])
                ->setPaidAt($paidAt->modify('-1 day'));

            $manager->persist($boosterPayment);
            $this->addReference(BillingFixtureData::boosterPaymentReference($agencyKey), $boosterPayment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AgencyBillingProfileFixtures::class,
            AgencyPaymentMethodFixtures::class,
            AgencySubscriptionFixtures::class,
            BoosterPackFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les paiements.');
        }

        return $currency;
    }

    private function providerFee(int $amountMinor): int
    {
        return (int) round($amountMinor * 0.014 + 25);
    }
}
