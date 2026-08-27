<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\User;
use App\Repository\PropertyRepository;
use Psr\Log\LoggerInterface;

final readonly class SubscriptionEntitlementService
{
    public function __construct(
        private PropertyRepository $propertyRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function applyFreePlanLimits(AgencySubscription $freeSubscription): int
    {
        return $this->applyPlanLimits(
            $freeSubscription->getAgency(),
            $freeSubscription->getPropertyLimitSnapshot(),
        );
    }

    public function applyPlanLimits(User $agency, ?int $propertyLimit): int
    {
        if (null === $propertyLimit) {
            return 0;
        }

        $suspended = 0;

        foreach ($this->propertyRepository->findPublishedPropertiesExceedingQuota($agency, $propertyLimit) as $property) {
            $property->setStatut(StatutAnnonceImmobiliere::SUSPENDED_BY_PLAN);
            ++$suspended;
        }

        if ($suspended > 0) {
            $this->logger->info('[SUBSCRIPTION ENTITLEMENTS] Properties suspended by free plan quota.', [
                'agency' => $agency->getId(),
                'property_limit' => $propertyLimit,
                'suspended_count' => $suspended,
            ]);
        }

        return $suspended;
    }
}
