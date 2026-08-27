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

namespace App\Tests\Dto\Subscription;

use App\Dto\Subscription\StripeSubscriptionSnapshot;
use PHPUnit\Framework\TestCase;
use Stripe\Subscription;

final class StripeSubscriptionSnapshotTest extends TestCase
{
    public function testSnapshotExtractsSubscriptionPeriodAndPrice(): void
    {
        $snapshot = StripeSubscriptionSnapshot::fromStripe(Subscription::constructFrom([
            'id' => 'sub_123',
            'customer' => 'cus_123',
            'status' => 'active',
            'cancel_at_period_end' => true,
            'latest_invoice' => [
                'id' => 'in_123',
            ],
            'items' => [
                'data' => [
                    [
                        'id' => 'si_123',
                        'current_period_start' => 1788230400,
                        'current_period_end' => 1790908800,
                        'price' => [
                            'id' => 'price_123',
                            'product' => 'prod_123',
                        ],
                    ],
                ],
            ],
        ]));

        self::assertSame('sub_123', $snapshot->id);
        self::assertSame('cus_123', $snapshot->customerId);
        self::assertSame('active', $snapshot->status);
        self::assertTrue($snapshot->cancelAtPeriodEnd);
        self::assertSame('in_123', $snapshot->latestInvoiceId);
        self::assertSame('si_123', $snapshot->subscriptionItemId);
        self::assertSame('price_123', $snapshot->priceId);
        self::assertSame('prod_123', $snapshot->productId);
        self::assertSame(1788230400, $snapshot->currentPeriodStart?->getTimestamp());
        self::assertSame(1790908800, $snapshot->currentPeriodEnd?->getTimestamp());
    }
}
