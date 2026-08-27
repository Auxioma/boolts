<?php

declare(strict_types=1);

namespace App\Dto\Subscription;

use Stripe\Subscription as StripeSubscription;

final readonly class StripeSubscriptionSnapshot
{
    public function __construct(
        public string $id,
        public ?string $customerId,
        public string $status,
        public ?\DateTimeImmutable $currentPeriodStart,
        public ?\DateTimeImmutable $currentPeriodEnd,
        public bool $cancelAtPeriodEnd,
        public ?\DateTimeImmutable $canceledAt,
        public ?\DateTimeImmutable $endedAt,
        public ?string $latestInvoiceId,
        public ?string $subscriptionItemId,
        public ?string $priceId,
        public ?string $productId,
    ) {
    }

    public static function fromStripe(StripeSubscription $subscription): self
    {
        $item = \is_object($subscription->items ?? null) ? ($subscription->items->data[0] ?? null) : null;
        $price = \is_object($item) && isset($item->price) && \is_object($item->price)
            ? $item->price
            : null;

        return new self(
            id: (string) $subscription->id,
            customerId: self::readId($subscription->customer ?? null),
            status: (string) $subscription->status,
            currentPeriodStart: self::timestampToDate(
                self::readTimestamp($item, 'current_period_start')
                ?? self::readTimestamp($subscription, 'current_period_start')
            ),
            currentPeriodEnd: self::timestampToDate(
                self::readTimestamp($item, 'current_period_end')
                ?? self::readTimestamp($subscription, 'current_period_end')
            ),
            cancelAtPeriodEnd: (bool) ($subscription->cancel_at_period_end ?? false),
            canceledAt: self::timestampToDate(self::readTimestamp($subscription, 'canceled_at')),
            endedAt: self::timestampToDate(self::readTimestamp($subscription, 'ended_at')),
            latestInvoiceId: self::readId($subscription->latest_invoice ?? null),
            subscriptionItemId: self::readId($item),
            priceId: self::readId($price),
            productId: null !== $price ? self::readId($price->product ?? null) : null,
        );
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
