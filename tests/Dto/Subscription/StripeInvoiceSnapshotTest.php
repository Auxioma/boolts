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

use App\Dto\Subscription\StripeInvoiceSnapshot;
use PHPUnit\Framework\TestCase;
use Stripe\Invoice;

final class StripeInvoiceSnapshotTest extends TestCase
{
    public function testSnapshotExtractsPaidInvoiceStateAndBillingPeriod(): void
    {
        $snapshot = StripeInvoiceSnapshot::fromStripe(Invoice::constructFrom([
            'id' => 'in_renewal_123',
            'status' => 'paid',
            'currency' => 'eur',
            'subtotal' => 4900,
            'amount_due' => 4900,
            'amount_paid' => 4900,
            'amount_remaining' => 0,
            'total' => 4900,
            'attempt_count' => 3,
            'paid_at' => 1788230400,
            'created' => 1788230000,
            'payment_intent' => [
                'id' => 'pi_renewal_123',
                'latest_charge' => [
                    'id' => 'ch_renewal_123',
                ],
            ],
            'subscription' => 'sub_renewal_123',
            'hosted_invoice_url' => 'https://billing.stripe.test/invoice',
            'invoice_pdf' => 'https://billing.stripe.test/invoice.pdf',
            'lines' => [
                'data' => [
                    [
                        'period' => [
                            'start' => 1788230400,
                            'end' => 1790908800,
                        ],
                    ],
                ],
            ],
        ]));

        self::assertTrue($snapshot->isPaid());
        self::assertSame('in_renewal_123', $snapshot->id);
        self::assertSame('pi_renewal_123', $snapshot->paymentIntentId);
        self::assertSame('ch_renewal_123', $snapshot->chargeId);
        self::assertSame('sub_renewal_123', $snapshot->subscriptionId);
        self::assertSame(4900, $snapshot->amountTotalMinor);
        self::assertSame(3, $snapshot->attemptCount);
        self::assertSame(1788230400, $snapshot->billingPeriodStart?->getTimestamp());
        self::assertSame(1790908800, $snapshot->billingPeriodEnd?->getTimestamp());
    }

    public function testOpenInvoiceWithFailedPaymentIntentIsNotPaid(): void
    {
        $snapshot = StripeInvoiceSnapshot::fromStripe(Invoice::constructFrom([
            'id' => 'in_failed_123',
            'status' => 'open',
            'currency' => 'eur',
            'amount_due' => 4900,
            'amount_paid' => 0,
            'amount_remaining' => 4900,
            'total' => 4900,
            'attempt_count' => 1,
            'payment_intent' => 'pi_failed_123',
            'subscription' => 'sub_failed_123',
        ]));

        self::assertFalse($snapshot->isPaid());
        self::assertTrue($snapshot->isOpen());
        self::assertSame('pi_failed_123', $snapshot->paymentIntentId);
    }
}
