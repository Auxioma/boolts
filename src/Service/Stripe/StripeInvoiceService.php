<?php

declare(strict_types=1);

namespace App\Service\Stripe;

use App\Dto\Subscription\StripeInvoiceSnapshot;
use Stripe\Invoice as StripeInvoice;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

final readonly class StripeInvoiceService
{
    public function __construct(
        private StripeClient $stripe,
    ) {
    }

    public function retrieve(string $invoiceId): StripeInvoice
    {
        return $this->stripe->invoices->retrieve(
            $invoiceId,
            [
                'expand' => [
                    'payment_intent',
                    'lines.data.price.product',
                ],
            ],
        );
    }

    public function latestInvoiceFromSubscription(StripeSubscription $subscription): ?StripeInvoice
    {
        $latestInvoice = $subscription->latest_invoice ?? null;

        if ($latestInvoice instanceof StripeInvoice) {
            return $latestInvoice;
        }

        if (\is_string($latestInvoice) && str_starts_with($latestInvoice, 'in_')) {
            return $this->retrieve($latestInvoice);
        }

        if (\is_object($latestInvoice) && isset($latestInvoice->id) && \is_string($latestInvoice->id)) {
            return $this->retrieve($latestInvoice->id);
        }

        return null;
    }

    public function payOpenInvoice(
        string $invoiceId,
        int $attemptNumber,
    ): StripeInvoice {
        return $this->stripe->invoices->pay(
            $invoiceId,
            [
                'expand' => [
                    'payment_intent',
                    'lines.data.price.product',
                ],
            ],
            [
                'idempotency_key' => \sprintf('subscription-invoice-pay-%s-%d', $invoiceId, $attemptNumber),
            ],
        );
    }

    public function snapshot(StripeInvoice $invoice): StripeInvoiceSnapshot
    {
        return StripeInvoiceSnapshot::fromStripe($invoice);
    }
}
