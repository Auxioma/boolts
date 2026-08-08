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
use App\Entity\Devise;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class AgencyBillingProfileFixtures extends Fixture implements DependentFixtureInterface
{
    public const AGENCY_BILLING_PROFILE_REFERENCE_PREFIX = 'agency_billing_profile_';

    public function load(ObjectManager $manager): void
    {
        $currency = $this->currency($manager);

        foreach (BillingFixtureData::agencyReferences() as $agencyKey => $agencyReference) {
            $agency = $this->getReference($agencyReference, User::class);

            $profile = (new AgencyBillingProfile())
                ->setAgency($agency)
                ->setStripeCustomerId('cus_fixture_'.$agencyKey)
                ->setPreferredCurrency($currency)
                ->setBillingEmail('facturation+'.$agencyKey.'@boolts.test')
                ->setLegalName('Agence '.$agencyKey.' SAS')
                ->setCommercialName('Agence '.$agencyKey)
                ->setAddressLine1('12 rue des Agences')
                ->setAddressLine2('Service facturation')
                ->setPostalCode('75015')
                ->setCity('Paris')
                ->setRegion('Ile-de-France')
                ->setCountryCode('FR')
                ->setLocale('fr')
                ->setTaxExemptStatus('none');

            $manager->persist($profile);
            $this->addReference(BillingFixtureData::profileReference($agencyKey), $profile);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PaysFixtures::class,
        ];
    }

    private function currency(ObjectManager $manager): Devise
    {
        $currency = $manager->getRepository(Devise::class)->findOneBy([
            'nom' => 'euro (EUR)',
        ]);

        if (!$currency instanceof Devise) {
            throw new \RuntimeException('La devise EUR doit être chargée avant les profils de facturation.');
        }

        return $currency;
    }
}
