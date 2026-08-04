<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\BillingTaxIdentifier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class BillingTaxIdentifierFixtures extends Fixture implements DependentFixtureInterface
{
    public const BILLING_TAX_IDENTIFIER_REFERENCE_PREFIX = 'billing_tax_identifier_';

    public function load(ObjectManager $manager): void
    {
        foreach (array_keys(BillingFixtureData::agencyReferences()) as $position => $agencyKey) {
            $profile = $this->getReference(
                BillingFixtureData::profileReference($agencyKey),
                AgencyBillingProfile::class,
            );

            $taxIdentifier = (new BillingTaxIdentifier())
                ->setBillingProfile($profile)
                ->setType('eu_vat')
                ->setCountryCode('FR')
                ->setValue(sprintf('FR%011d', 100000000 + (int) $position))
                ->setStripeTaxId('txi_fixture_'.$agencyKey)
                ->setVerificationStatus('verified')
                ->setIsPrimary(true)
                ->setVerifiedAt(new \DateTimeImmutable('-20 days'));

            $manager->persist($taxIdentifier);
            $this->addReference(self::BILLING_TAX_IDENTIFIER_REFERENCE_PREFIX.$agencyKey, $taxIdentifier);
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
