<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Tests\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Service\Subscription\SubscriptionPaymentRecoveryService;
use PHPUnit\Framework\TestCase;

final class SubscriptionPaymentRecoveryPolicyTest extends TestCase
{
    public function testRetryPolicySchedulesOnlyOneNextAttemptPerDay(): void
    {
        $now = new \DateTimeImmutable('2026-09-01 10:00:00');
        $deadline = new \DateTimeImmutable('2026-09-06 10:00:00');

        self::assertSame(
            '2026-09-02 10:00:00',
            SubscriptionPaymentRecoveryService::resolveNextPaymentRetryAt($now, $deadline, 1)?->format('Y-m-d H:i:s'),
        );
        self::assertSame(
            '2026-09-02 10:00:00',
            SubscriptionPaymentRecoveryService::resolveNextPaymentRetryAt($now, $deadline, 4)?->format('Y-m-d H:i:s'),
        );
    }

    public function testFifthAttemptDoesNotScheduleASixthAttempt(): void
    {
        self::assertNull(SubscriptionPaymentRecoveryService::resolveNextPaymentRetryAt(
            new \DateTimeImmutable('2026-09-05 10:00:00'),
            new \DateTimeImmutable('2026-09-06 10:00:00'),
            SubscriptionPaymentRecoveryService::MAX_ATTEMPTS,
        ));
    }

    public function testNextRetryNeverGoesAfterRecoveryDeadline(): void
    {
        self::assertSame(
            '2026-09-06 08:00:00',
            SubscriptionPaymentRecoveryService::resolveNextPaymentRetryAt(
                new \DateTimeImmutable('2026-09-05 12:00:00'),
                new \DateTimeImmutable('2026-09-06 08:00:00'),
                3,
            )?->format('Y-m-d H:i:s'),
        );
    }

    public function testRecoveryMustFinalizeAfterFifthFailure(): void
    {
        $subscription = (new AgencySubscription())
            ->setPaymentFailureCount(5)
            ->setPaymentRecoveryDeadline(new \DateTimeImmutable('2026-09-06 10:00:00'));

        self::assertTrue(SubscriptionPaymentRecoveryService::shouldFinalizeRecovery(
            $subscription,
            new \DateTimeImmutable('2026-09-05 10:00:00'),
        ));
    }

    public function testRecoveryMustFinalizeAfterFiveDaysDeadline(): void
    {
        $subscription = (new AgencySubscription())
            ->setPaymentFailureCount(3)
            ->setPaymentRecoveryDeadline(new \DateTimeImmutable('2026-09-06 10:00:00'));

        self::assertTrue(SubscriptionPaymentRecoveryService::shouldFinalizeRecovery(
            $subscription,
            new \DateTimeImmutable('2026-09-06 10:00:01'),
        ));
    }

    public function testRecoveryDoesNotFinalizeWhileAttemptsAndDeadlineRemain(): void
    {
        $subscription = (new AgencySubscription())
            ->setPaymentFailureCount(3)
            ->setPaymentRecoveryDeadline(new \DateTimeImmutable('2026-09-06 10:00:00'));

        self::assertFalse(SubscriptionPaymentRecoveryService::shouldFinalizeRecovery(
            $subscription,
            new \DateTimeImmutable('2026-09-04 10:00:00'),
        ));
    }
}
