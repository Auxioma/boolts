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

use App\Entity\Billing\Enum\WebhookEventStatus;
use App\Entity\Billing\PaymentWebhookEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class PaymentWebhookEventFixtures extends Fixture
{
    public const PAYMENT_WEBHOOK_EVENT_REFERENCE_PREFIX = 'payment_webhook_event_';

    private const EVENTS = [
        [
            'id' => 'evt_fixture_payment_succeeded',
            'type' => 'payment_intent.succeeded',
            'status' => WebhookEventStatus::PROCESSED,
            'attemptCount' => 1,
        ],
        [
            'id' => 'evt_fixture_invoice_paid',
            'type' => 'invoice.paid',
            'status' => WebhookEventStatus::PROCESSED,
            'attemptCount' => 1,
        ],
        [
            'id' => 'evt_fixture_refund_created',
            'type' => 'charge.refunded',
            'status' => WebhookEventStatus::RECEIVED,
            'attemptCount' => 0,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::EVENTS as $position => $data) {
            $event = (new PaymentWebhookEvent())
                ->setProvider('stripe')
                ->setProviderEventId($data['id'])
                ->setEventType($data['type'])
                ->setApiVersion('2024-06-20')
                ->setLivemode(false)
                ->setPayload([
                    'id' => $data['id'],
                    'type' => $data['type'],
                    'livemode' => false,
                    'object' => 'event',
                ])
                ->setStatus($data['status'])
                ->setAttemptCount($data['attemptCount'])
                ->setReceivedAt(new \DateTimeImmutable(\sprintf('-%d days', 3 - $position)))
                ->setProcessedAt(WebhookEventStatus::PROCESSED === $data['status'] ? new \DateTimeImmutable('-1 day') : null);

            $manager->persist($event);
            $this->addReference(self::PAYMENT_WEBHOOK_EVENT_REFERENCE_PREFIX.($position + 1), $event);
        }

        $manager->flush();
    }
}
