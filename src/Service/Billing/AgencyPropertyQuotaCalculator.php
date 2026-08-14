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

namespace App\Service\Billing;

use App\Entity\Billing\AgencySubscription;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\PropertyRepository;

final readonly class AgencyPropertyQuotaCalculator
{
    public function __construct(
        private AgencySubscriptionRepository $agencySubscriptionRepository,
        private PropertyRepository $propertyRepository,
    ) {
    }

    /**
     * @return array{limit: ?int, used: int, remaining: ?int, reached: bool}
     */
    public function calculate(User $agency): array
    {
        $subscription = $this->agencySubscriptionRepository
            ->findLatestQuotaForAgency($agency);

        $limit = $this->resolvePropertyLimit($subscription);
        $used = $this->propertyRepository->countUsedForAgencyQuota($agency);
        $remaining = null === $limit ? null : max(0, $limit - $used);

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'reached' => null !== $limit && $used >= $limit,
        ];
    }

    private function resolvePropertyLimit(?AgencySubscription $subscription): ?int
    {
        if (!$subscription instanceof AgencySubscription) {
            return 0;
        }

        return $subscription->getPropertyLimitSnapshot()
            ?? $subscription->getPlan()->getPropertyLimit();
    }
}
