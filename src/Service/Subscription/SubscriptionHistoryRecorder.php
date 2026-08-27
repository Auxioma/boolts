<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionHistory;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SubscriptionHistoryRecorder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function record(
        AgencySubscription $subscription,
        SubscriptionHistoryEventType $eventType,
        ?SubscriptionStatus $oldStatus = null,
        ?SubscriptionStatus $newStatus = null,
        ?string $oldPlan = null,
        ?string $newPlan = null,
        ?string $providerInvoiceId = null,
        ?string $providerPaymentIntentId = null,
        array $metadata = [],
    ): void {
        $history = (new SubscriptionHistory())
            ->setSubscription($subscription)
            ->setAgency($subscription->getAgency())
            ->setEventType($eventType)
            ->setOldStatus($oldStatus)
            ->setNewStatus($newStatus)
            ->setOldPlan($oldPlan)
            ->setNewPlan($newPlan)
            ->setProviderInvoiceId($providerInvoiceId)
            ->setProviderPaymentIntentId($providerPaymentIntentId)
            ->setMetadata($metadata);

        $this->entityManager->persist($history);
    }
}
