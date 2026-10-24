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

use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Devise;
use App\Service\Billing\CurrencyMinorUnit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class SubscriptionPlanPriceFixtures extends Fixture implements DependentFixtureInterface
{
    public const SUBSCRIPTION_PLAN_PRICE_REFERENCE_PREFIX = 'subscription_plan_price_';

    /** Prix du forfait gratuit par défaut (mensuel, 0), utilisé par les autres fixtures. */
    public const FREE_PLAN_PRICE_REFERENCE = self::SUBSCRIPTION_PLAN_PRICE_REFERENCE_PREFIX.'free_monthly';

    public function __construct(
        private readonly CurrencyMinorUnit $currencyMinorUnit,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (BillingFixtureData::SUBSCRIPTION_CURRENCIES as $code => [$name, $rate]) {
            $currency = $manager->getRepository(Devise::class)->findOneBy(['nom' => $name]);

            if (!$currency instanceof Devise) {
                throw new \RuntimeException(\sprintf('La devise %s doit être chargée avant les prix des abonnements.', $code));
            }

            foreach (BillingFixtureData::SUBSCRIPTION_PLANS as $data) {
                $plan = $this->getReference(
                    SubscriptionPlanFixtures::SUBSCRIPTION_PLAN_REFERENCE_PREFIX.$data['code'],
                    SubscriptionPlan::class,
                );

                $price = FixtureEntityHelper::findOrCreate($manager, SubscriptionPlanPrice::class, [
                    'plan' => $plan,
                    'currency' => $currency,
                    'billingPeriod' => SubscriptionBillingPeriod::from($data['billingPeriod']),
                ]);
                $price
                    ->setPlan($plan)
                    ->setCurrency($currency)
                    ->setAmountMinor($this->convertFromEur($data['amountMinorEur'], $rate, $currency))
                    ->setBillingPeriod(SubscriptionBillingPeriod::from($data['billingPeriod']))
                    ->setIsActive(true);

                $manager->persist($price);

                // Les références existantes (utilisées par les autres fixtures) restent sur l'EUR.
                $reference = self::SUBSCRIPTION_PLAN_PRICE_REFERENCE_PREFIX.str_replace('-', '_', $data['code']);
                $this->addReference('EUR' === $code ? $reference : $reference.'_'.strtolower($code), $price);
            }
        }

        $manager->flush();
    }

    /**
     * Prix EUR converti puis arrondi au prix « psychologique » immédiatement
     * supérieur : ,99 avec centimes (22,99 $), …99 sans (3 599 ¥).
     * Résultat en unités mineures Stripe de la devise (voir CurrencyMinorUnit).
     */
    private function convertFromEur(int $amountMinorEur, float $rate, Devise $currency): int
    {
        if (0 === $amountMinorEur) {
            return 0;
        }

        // round() évite qu'une imprécision flottante (2237,4800001) ne fasse sauter un palier.
        $major = round($amountMinorEur / 100 * $rate, 6);

        if (0 === $this->currencyMinorUnit->decimals($currency)) {
            $rounded = (int) (ceil(($major + 1) / 100) * 100 - 1);

            return $rounded * $this->currencyMinorUnit->divisor($currency);
        }

        return (int) (ceil(($major * 100 + 1) / 100) * 100 - 1);
    }

    public function getDependencies(): array
    {
        return [
            PaysFixtures::class,
            SubscriptionPlanFixtures::class,
        ];
    }
}
