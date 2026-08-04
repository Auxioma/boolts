<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Billing\Enum\SubscriptionBillingPeriod;

final class BillingFixtureData
{
    public const SUBSCRIPTION_PLANS = [
        [
            'code' => 'free',
            'name' => 'Gratuit',
            'description' => 'Publiez gratuitement vos premières annonces.',
            'propertyLimit' => 3,
            'includedBoosts' => 0,
            'boostDurationDays' => 7,
            'isFree' => true,
            'isDefault' => true,
            'position' => 1,
        ],
        [
            'code' => 'starter',
            'name' => 'Starter',
            'description' => 'Une offre adaptée aux professionnels qui démarrent.',
            'propertyLimit' => 10,
            'includedBoosts' => 3,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 2,
        ],
        [
            'code' => 'pro',
            'name' => 'Pro',
            'description' => 'Gérez davantage de biens et améliorez leur visibilité.',
            'propertyLimit' => 50,
            'includedBoosts' => 5,
            'boostDurationDays' => 15,
            'isFree' => false,
            'isDefault' => false,
            'position' => 3,
        ],
    ];

    public const SUBSCRIPTION_PRICES = [
        SubscriptionBillingPeriod::MONTHLY->value => [
            'free' => 0,
            'starter' => 1990,
            'pro' => 4990,
        ],
        SubscriptionBillingPeriod::ANNUAL->value => [
            'free' => 0,
            'starter' => 19900,
            'pro' => 49900,
        ],
    ];

    public const BOOSTER_PACKS = [
        [
            'code' => 'boost-1',
            'name' => '1 boost',
            'description' => '1 boost s’applique sur 1 annonce et dure 15 jours.',
            'boostQuantity' => 1,
            'boostDurationDays' => 15,
            'position' => 1,
        ],
        [
            'code' => 'boost-5',
            'name' => '5 boosts',
            'description' => '5 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
            'boostQuantity' => 5,
            'boostDurationDays' => 15,
            'position' => 2,
        ],
        [
            'code' => 'boost-20',
            'name' => '20 boosts',
            'description' => '20 boosts à utiliser sur vos annonces pendant 15 jours chacun.',
            'boostQuantity' => 20,
            'boostDurationDays' => 15,
            'position' => 3,
        ],
    ];

    public const BOOSTER_PRICES = [
        'boost-1' => 2499,
        'boost-5' => 9999,
        'boost-20' => 29999,
    ];

    public const PROPERTY_BOOST_STATUS_COUNTS = [
        'active' => 24,
        'scheduled' => 6,
        'expired' => 8,
        'canceled' => 4,
    ];

    public static function agencyReferences(): array
    {
        $references = [
            'main' => UserFixtures::USER_AGENCE_REFERENCE,
        ];

        for ($index = 1; $index <= 50; ++$index) {
            $references['agency_'.$index] = UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$index;
        }

        return $references;
    }

    public static function agencyPlanCode(int $position): string
    {
        return match ($position % 3) {
            0 => 'free',
            1 => 'starter',
            default => 'pro',
        };
    }

    public static function profileReference(string $agencyKey): string
    {
        return 'agency_billing_profile_'.$agencyKey;
    }

    public static function paymentMethodReference(string $agencyKey): string
    {
        return 'agency_payment_method_'.$agencyKey;
    }

    public static function subscriptionReference(string $agencyKey): string
    {
        return 'agency_subscription_'.$agencyKey;
    }

    public static function subscriptionPeriodReference(string $agencyKey): string
    {
        return 'agency_subscription_period_'.$agencyKey;
    }

    public static function subscriptionPaymentReference(string $agencyKey): string
    {
        return 'payment_subscription_'.$agencyKey;
    }

    public static function boosterPaymentReference(string $agencyKey): string
    {
        return 'payment_booster_pack_'.$agencyKey;
    }

    public static function subscriptionInvoiceReference(string $agencyKey): string
    {
        return 'invoice_subscription_'.$agencyKey;
    }

    public static function boosterInvoiceReference(string $agencyKey): string
    {
        return 'invoice_booster_pack_'.$agencyKey;
    }

    public static function firstAgencyKeyForPlan(string $planCode): string
    {
        foreach (array_keys(self::agencyReferences()) as $position => $agencyKey) {
            if (self::agencyPlanCode($position) === $planCode) {
                return $agencyKey;
            }
        }

        throw new \LogicException(sprintf('Aucune agence de fixture pour le forfait "%s".', $planCode));
    }

    public static function paymentSnapshot(string $last4 = '4242'): array
    {
        return [
            'type' => 'card',
            'brand' => 'visa',
            'last4' => $last4,
            'exp_month' => 12,
            'exp_year' => 2030,
            'funding' => 'credit',
            'country' => 'FR',
        ];
    }

    public static function sellerSnapshot(): array
    {
        return [
            'name' => 'Boolts',
            'legal_name' => 'Boolts SAS',
            'address' => '10 rue de la Paix',
            'postal_code' => '75002',
            'city' => 'Paris',
            'country_code' => 'FR',
            'vat_number' => 'FR00123456789',
        ];
    }

    public static function customerSnapshot(string $agencyKey): array
    {
        return [
            'name' => 'Agence '.$agencyKey,
            'email' => 'facturation+'.$agencyKey.'@boolts.test',
            'address' => '12 rue des Agences',
            'postal_code' => '75015',
            'city' => 'Paris',
            'country_code' => 'FR',
        ];
    }

    public static function taxSnapshot(): array
    {
        return [
            'name' => 'TVA non applicable',
            'rate' => '0.00000',
            'country_code' => 'FR',
            'tax_behavior' => 'exclusive',
        ];
    }

    public static function propertyBoostSchedule(int $index, string $status): array
    {
        $today = (new \DateTimeImmutable('today'))->setTime(9, 0);

        return match ($status) {
            'active' => [
                $start = $today->modify(sprintf('-%d days', ($index % 7) + 1)),
                $start->modify('+15 days'),
                null,
            ],
            'scheduled' => [
                $start = $today->modify(sprintf('+%d days', ($index % 6) + 1)),
                $start->modify('+15 days'),
                null,
            ],
            'expired' => [
                $start = $today->modify(sprintf('-%d days', 24 + ($index % 8))),
                $start->modify('+15 days'),
                null,
            ],
            'canceled' => [
                $start = $today->modify(sprintf('-%d days', 12 + ($index % 4))),
                $start->modify('+15 days'),
                $today->modify(sprintf('-%d days', ($index % 3) + 1)),
            ],
            default => throw new \InvalidArgumentException(sprintf('Statut de boost fixture invalide : %s', $status)),
        };
    }
}
