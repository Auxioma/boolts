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
