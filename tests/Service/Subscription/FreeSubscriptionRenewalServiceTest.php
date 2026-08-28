<?php

declare(strict_types=1);

namespace App\Tests\Service\Subscription;

use App\Service\Subscription\FreeSubscriptionRenewalService;
use PHPUnit\Framework\TestCase;

final class FreeSubscriptionRenewalServiceTest extends TestCase
{
    public function testExpiredFreePeriodIsRenewedForOneMonth(): void
    {
        $periods = FreeSubscriptionRenewalService::resolveRenewalPeriods(
            new \DateTimeImmutable('2026-08-28 10:04:58'),
            new \DateTimeImmutable('2026-08-28 12:00:00'),
        );

        self::assertCount(1, $periods);
        self::assertSame('2026-08-28 10:04:59', $periods[0]['start']->format('Y-m-d H:i:s'));
        self::assertSame('2026-09-28 10:04:58', $periods[0]['end']->format('Y-m-d H:i:s'));
    }

    public function testEveryMissingFreePeriodIsCreated(): void
    {
        $periods = FreeSubscriptionRenewalService::resolveRenewalPeriods(
            new \DateTimeImmutable('2026-06-28 10:04:58'),
            new \DateTimeImmutable('2026-08-28 12:00:00'),
        );

        self::assertCount(3, $periods);
        self::assertSame('2026-06-28 10:04:59', $periods[0]['start']->format('Y-m-d H:i:s'));
        self::assertSame('2026-09-28 10:04:58', $periods[2]['end']->format('Y-m-d H:i:s'));
    }

    public function testCurrentFreePeriodIsNotRenewedEarly(): void
    {
        self::assertSame([], FreeSubscriptionRenewalService::resolveRenewalPeriods(
            new \DateTimeImmutable('2026-09-28 10:04:58'),
            new \DateTimeImmutable('2026-08-28 12:00:00'),
        ));
    }
}
