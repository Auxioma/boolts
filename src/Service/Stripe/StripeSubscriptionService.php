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
use Stripe\SubscriptionSchedule as StripeSubscriptionSchedule;

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

    /**
     * Schedules a switch to a cheaper plan that only takes effect (and is billed)
     * at the end of the current paid period, using a Stripe subscription schedule:
     * phase 1 keeps the current price until the period end, phase 2 (open-ended)
     * carries the new price. Stripe bills the new price at the phase boundary; the
     * schedule is released once we observe the switch (see the synchronisation
     * service), handing normal management back to the subscription.
     *
     * Safe to call again to replace a change that was already scheduled: the
     * existing schedule is reused and its second phase overwritten.
     */
    public function scheduleDowngradeAtPeriodEnd(
        AgencySubscription $subscription,
        SubscriptionPlanPrice $targetPlanPrice,
        AgencyPaymentMethod $paymentMethod,
    ): StripeSubscriptionSchedule {
        $subscriptionId = $this->requireProviderSubscriptionId($subscription);
        $currentPriceId = $this->requireCurrentStripePriceId($subscription);
        $targetPriceId = $this->getOrCreateStripePrice($targetPlanPrice);

        $schedule = $this->resolveDowngradeSchedule($subscriptionId, $subscription->getProviderScheduleId());

        $currentPhase = $schedule->phases[0] ?? null;

        if (!\is_object($currentPhase) || !isset($currentPhase->start_date, $currentPhase->end_date)) {
            throw new \LogicException('Le planning Stripe ne contient pas de phase courante exploitable.');
        }

        return $this->stripe->subscriptionSchedules->update(
            (string) $schedule->id,
            [
                'end_behavior' => 'release',
                'proration_behavior' => 'none',
                'default_settings' => [
                    'collection_method' => 'charge_automatically',
                    'default_payment_method' => $paymentMethod->getStripePaymentMethodId(),
                ],
                'phases' => [
                    [
                        'items' => [['price' => $currentPriceId, 'quantity' => 1]],
                        'start_date' => $currentPhase->start_date,
                        'end_date' => $currentPhase->end_date,
                    ],
                    [
                        'items' => [['price' => $targetPriceId, 'quantity' => 1]],
                    ],
                ],
            ],
            [
                'idempotency_key' => \sprintf(
                    'subscription-downgrade-%s-%s-%s',
                    $subscriptionId,
                    $targetPriceId,
                    bin2hex(random_bytes(8)),
                ),
            ],
        );
    }

    /**
     * Returns the subscription schedule to drive the downgrade with: the one we
     * already track, a freshly created one, or — when Stripe refuses to create one
     * because the subscription is already attached to a schedule (e.g. a previous
     * attempt created it but never persisted its id) — the schedule Stripe reports
     * on the subscription.
     */
    private function resolveDowngradeSchedule(string $subscriptionId, ?string $knownScheduleId): StripeSubscriptionSchedule
    {
        if (\is_string($knownScheduleId) && str_starts_with($knownScheduleId, 'sub_sched_')) {
            return $this->stripe->subscriptionSchedules->retrieve($knownScheduleId);
        }

        try {
            return $this->stripe->subscriptionSchedules->create(['from_subscription' => $subscriptionId]);
        } catch (\Stripe\Exception\InvalidRequestException $exception) {
            $attachedScheduleId = $this->attachedScheduleId($subscriptionId);

            if (null === $attachedScheduleId) {
                throw $exception;
            }

            return $this->stripe->subscriptionSchedules->retrieve($attachedScheduleId);
        }
    }

    private function attachedScheduleId(string $subscriptionId): ?string
    {
        $schedule = $this->stripe->subscriptions->retrieve($subscriptionId)->schedule ?? null;

        if (\is_string($schedule) && str_starts_with($schedule, 'sub_sched_')) {
            return $schedule;
        }

        if (\is_object($schedule) && isset($schedule->id) && \is_string($schedule->id)) {
            return $schedule->id;
        }

        return null;
    }

    /**
     * Releases a subscription schedule while leaving the underlying subscription
     * untouched. Tolerates a schedule that Stripe already released or completed.
     */
    public function releaseSchedule(string $scheduleId): void
    {
        try {
            $this->stripe->subscriptionSchedules->release($scheduleId);
        } catch (\Stripe\Exception\InvalidRequestException) {
            // Release requires a not_started/active schedule; any other state means
            // it is already gone, which is exactly the target state here.
        }
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

    private function requireCurrentStripePriceId(AgencySubscription $subscription): string
    {
        $priceId = $subscription->getProviderPriceId()
            ?? $subscription->getPlanPrice()?->getPaymentProviderPriceId();

        if (!\is_string($priceId) || !str_starts_with($priceId, 'price_')) {
            throw new \LogicException('Le tarif Stripe courant de l’abonnement est introuvable.');
        }

        return $priceId;
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
