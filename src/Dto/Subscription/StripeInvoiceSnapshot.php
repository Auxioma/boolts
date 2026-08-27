<?php

declare(strict_types=1);

namespace App\Dto\Subscription;

use Stripe\Invoice as StripeInvoice;

final readonly class StripeInvoiceSnapshot
{
    public function __construct(
        public string $id,
        public string $number,
        public string $status,
        public int $subtotalMinor,
        public int $amountDueMinor,
        public int $amountPaidMinor,
        public int $amountTotalMinor,
        public string $currency,
        public int $attemptCount,
        public ?\DateTimeImmutable $nextPaymentAttemptAt,
        public ?\DateTimeImmutable $paidAt,
        public ?\DateTimeImmutable $createdAt,
        public ?string $paymentIntentId,
        public ?string $chargeId,
        public ?string $subscriptionId,
        public ?string $hostedInvoiceUrl,
        public ?string $invoicePdfUrl,
        public ?\DateTimeImmutable $billingPeriodStart,
        public ?\DateTimeImmutable $billingPeriodEnd,
    ) {
    }

    public static function fromStripe(StripeInvoice $invoice): self
    {
        $line = \is_object($invoice->lines ?? null) ? ($invoice->lines->data[0] ?? null) : null;
        $period = \is_object($line) && isset($line->period) && \is_object($line->period)
            ? $line->period
            : null;
        $paymentIntent = \is_object($invoice->payment_intent ?? null)
            ? $invoice->payment_intent
            : null;
        $statusTransitions = \is_object($invoice->status_transitions ?? null)
            ? $invoice->status_transitions
            : null;

        return new self(
            id: (string) $invoice->id,
            number: self::readString($invoice, 'number') ?? (string) $invoice->id,
            status: (string) $invoice->status,
            subtotalMinor: (int) ($invoice->subtotal ?? $invoice->total ?? 0),
            amountDueMinor: (int) ($invoice->amount_due ?? 0),
            amountPaidMinor: (int) ($invoice->amount_paid ?? 0),
            amountTotalMinor: (int) ($invoice->total ?? 0),
            currency: mb_strtoupper((string) ($invoice->currency ?? 'EUR')),
            attemptCount: (int) ($invoice->attempt_count ?? 0),
            nextPaymentAttemptAt: self::timestampToDate(self::readTimestamp($invoice, 'next_payment_attempt')),
            paidAt: self::timestampToDate(self::readTimestamp($statusTransitions, 'paid_at')),
            createdAt: self::timestampToDate(self::readTimestamp($invoice, 'created')),
            paymentIntentId: self::readId($invoice->payment_intent ?? null),
            chargeId: self::readId($invoice->charge ?? null) ?? self::readId($paymentIntent->latest_charge ?? null),
            subscriptionId: self::readId($invoice->subscription ?? null),
            hostedInvoiceUrl: self::readString($invoice, 'hosted_invoice_url'),
            invoicePdfUrl: self::readString($invoice, 'invoice_pdf'),
            billingPeriodStart: self::timestampToDate(self::readTimestamp($period, 'start')),
            billingPeriodEnd: self::timestampToDate(self::readTimestamp($period, 'end')),
        );
    }

    public function isPaid(): bool
    {
        return 'paid' === $this->status || $this->amountPaidMinor >= $this->amountTotalMinor && $this->amountTotalMinor > 0;
    }

    public function isOpen(): bool
    {
        return 'open' === $this->status;
    }

    public function isUncollectibleOrVoid(): bool
    {
        return \in_array($this->status, ['uncollectible', 'void'], true);
    }

    private static function readId(mixed $value): ?string
    {
        if (\is_string($value) && '' !== $value) {
            return $value;
        }

        if (\is_object($value) && isset($value->id) && \is_string($value->id) && '' !== $value->id) {
            return $value->id;
        }

        return null;
    }

    private static function readString(object $object, string $property): ?string
    {
        if (!isset($object->{$property}) || !\is_string($object->{$property}) || '' === $object->{$property}) {
            return null;
        }

        return $object->{$property};
    }

    private static function readTimestamp(mixed $object, string $property): ?int
    {
        if (!\is_object($object) || !isset($object->{$property}) || !is_numeric($object->{$property})) {
            return null;
        }

        $timestamp = (int) $object->{$property};

        return $timestamp > 0 ? $timestamp : null;
    }

    private static function timestampToDate(?int $timestamp): ?\DateTimeImmutable
    {
        return null === $timestamp ? null : (new \DateTimeImmutable())->setTimestamp($timestamp);
    }
}
