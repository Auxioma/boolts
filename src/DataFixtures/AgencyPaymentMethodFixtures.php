<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\Enum\PaymentMethodSetupStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class AgencyPaymentMethodFixtures extends Fixture implements DependentFixtureInterface
{
    public const AGENCY_PAYMENT_METHOD_REFERENCE_PREFIX = 'agency_payment_method_';

    public function load(ObjectManager $manager): void
    {
        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $profile = $this->getReference(
                BillingFixtureData::profileReference($agencyKey),
                AgencyBillingProfile::class,
            );
            $last4 = str_pad((string) (4242 + (int) $position), 4, '0', STR_PAD_LEFT);

            $paymentMethod = (new AgencyPaymentMethod())
                ->setBillingProfile($profile)
                ->setStripePaymentMethodId('pm_fixture_'.$agencyKey)
                ->setStripeSetupIntentId('seti_fixture_'.$agencyKey)
                ->setType('card')
                ->setBrand('visa')
                ->setLast4(substr($last4, -4))
                ->setExpMonth(12)
                ->setExpYear(2030)
                ->setCardholderName('Agence '.$agencyKey)
                ->setCountryCode('FR')
                ->setFunding('credit')
                ->setFingerprint('fixture-fingerprint-'.$agencyKey)
                ->setIsDefault(true)
                ->setIsActive(true)
                ->setSetupStatus(PaymentMethodSetupStatus::SUCCEEDED);

            $manager->persist($paymentMethod);
            $this->addReference(BillingFixtureData::paymentMethodReference($agencyKey), $paymentMethod);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AgencyBillingProfileFixtures::class,
        ];
    }
}
