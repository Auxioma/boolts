<?php

declare(strict_types=1);

namespace App\Service\Stripe;

use App\Dto\Subscription\StripeSubscriptionSnapshot;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\SubscriptionPlanPrice;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;

final readonly class StripeSubscriptionService
{
    public function __construct(
        private StripeClient $stripe,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function retrieve(string $subscriptionId): StripeSubscription
    {
        return $this->stripe->subscriptions->retrieve(
            $subscriptionId,
            [
                'expand' => [
                    'latest_invoice.payment_intent',
                    'items.data.price.product',
                ],
            ],
        );
    }

    public function snapshot(StripeSubscription $subscription): StripeSubscriptionSnapshot
    {
        return StripeSubscriptionSnapshot::fromStripe($subscription);
    }

    public function scheduleCancellationAtPeriodEnd(
        AgencySubscription $subscription,
    ): StripeSubscription {
        $subscriptionId = $this->requireProviderSubscriptionId($subscription);

        return $this->stripe->subscriptions->update(
            $subscriptionId,
            [
                'cancel_at_period_end' => true,
            ],
            [
                'idempotency_key' => 'subscription-cancel-at-period-end-'.$subscriptionId,
            ],
        );
    }

    public function reactivateBeforePeriodEnd(
        AgencySubscription $subscription,
    ): StripeSubscription {
        $subscriptionId = $this->requireProviderSubscriptionId($subscription);

        return $this->stripe->subscriptions->update(
            $subscriptionId,
            [
                'cancel_at_period_end' => false,
            ],
            [
                'idempotency_key' => 'subscription-reactivate-'.$subscriptionId,
            ],
        );
    }

    public function cancelNow(AgencySubscription $subscription): StripeSubscription
    {
        $subscriptionId = $this->requireProviderSubscriptionId($subscription);

        return $this->stripe->subscriptions->cancel(
            $subscriptionId,
            [
                'invoice_now' => false,
                'prorate' => false,
            ],
            [
                'idempotency_key' => 'subscription-final-cancel-'.$subscriptionId,
            ],
        );
    }

    public function upgradeNow(
        AgencySubscription $subscription,
        SubscriptionPlanPrice $planPrice,
        AgencyPaymentMethod $paymentMethod,
    ): StripeSubscription {
        $subscriptionId = $this->requireProviderSubscriptionId($subscription);
        $subscriptionItemId = $subscription->getProviderSubscriptionItemId();

        if (!\is_string($subscriptionItemId) || '' === $subscriptionItemId) {
            throw new \LogicException('L’élément de l’abonnement Stripe est introuvable.');
        }

        $stripePriceId = $this->getOrCreateStripePrice($planPrice);

        return $this->stripe->subscriptions->update(
            $subscriptionId,
            [
                'items' => [[
                    'id' => $subscriptionItemId,
                    'price' => $stripePriceId,
                ]],
                'default_payment_method' => $paymentMethod->getStripePaymentMethodId(),
                'billing_cycle_anchor' => 'now',
                'proration_behavior' => 'none',
                'payment_behavior' => 'error_if_incomplete',
                'cancel_at_period_end' => false,
                'metadata' => [
                    'agency_id' => (string) $subscription->getAgency()->getId(),
                    'subscription_plan_price_id' => (string) $planPrice->getId(),
                ],
                'expand' => [
                    'latest_invoice.payment_intent',
                    'items.data.price.product',
                ],
            ],
            [
                'idempotency_key' => \sprintf(
                    'subscription-upgrade-%s-%s-%s',
                    $subscriptionId,
                    $stripePriceId,
                    bin2hex(random_bytes(8)),
                ),
            ],
        );
    }

    public function getOrCreateStripePrice(SubscriptionPlanPrice $planPrice): string
    {
        $stripePriceId = $planPrice->getPaymentProviderPriceId();

        if (\is_string($stripePriceId) && str_starts_with($stripePriceId, 'price_')) {
            return $stripePriceId;
        }

        $product = $this->stripe->products->create([
            'name' => \sprintf('Abonnement %s', $planPrice->getPlan()->getName()),
            'metadata' => ['subscription_plan_price_id' => (string) $planPrice->getId()],
        ]);
        $price = $this->stripe->prices->create([
            'product' => $product->id,
            'currency' => $this->currencyCode($planPrice),
            'unit_amount' => $planPrice->getAmountMinor(),
            'recurring' => ['interval' => $planPrice->getBillingPeriod()->stripeInterval()],
            'metadata' => ['subscription_plan_price_id' => (string) $planPrice->getId()],
        ]);

        $planPrice->setPaymentProviderPriceId($price->id);
        $this->entityManager->flush();

        return $price->id;
    }

    private function requireProviderSubscriptionId(AgencySubscription $subscription): string
    {
        $subscriptionId = $subscription->getProviderSubscriptionId();

        if (!\is_string($subscriptionId) || !str_starts_with($subscriptionId, 'sub_')) {
            throw new \LogicException('Abonnement Stripe introuvable pour cette souscription.');
        }

        return $subscriptionId;
    }

    private function currencyCode(SubscriptionPlanPrice $planPrice): string
    {
        preg_match('/\\(([A-Z]{3})\\)/', (string) $planPrice->getCurrency()->getNom(), $matches);

        if (!isset($matches[1])) {
            throw new \LogicException('Le code ISO de la devise du forfait est introuvable.');
        }

        return mb_strtolower($matches[1]);
    }
}
