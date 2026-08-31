<?php

declare(strict_types=1);

namespace App\Tests\Service\Subscription;

use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\SubscriptionPlan;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Devise;
use App\Entity\User;
use App\Exception\PlanChangeException;
use App\Service\Stripe\StripeSubscriptionService;
use App\Service\Subscription\SubscriptionHistoryRecorder;
use App\Service\Subscription\SubscriptionPlanChangeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Stripe\StripeClient;

/**
 * Covers the business rules that gate a deferred (period-end) downgrade. Every
 * case here is rejected before any Stripe call or transaction, so the collaborators
 * only need to be constructible.
 */
final class SubscriptionPlanChangeServiceTest extends TestCase
{
    public function testRejectsWhenTargetAmountIsNotStrictlyLower(): void
    {
        $eur = $this->devise('euro (EUR)');
        $subscription = $this->subscription($this->planPrice(1, 4900, $eur, false));

        $this->expectException(PlanChangeException::class);

        $this->service()->scheduleDowngrade(
            $subscription,
            $this->planPrice(2, 4900, $eur, false),
            $this->paymentMethod(),
        );
    }

    public function testRejectsWhenCurrencyDiffers(): void
    {
        $subscription = $this->subscription($this->planPrice(1, 4900, $this->devise('euro (EUR)'), false));

        $this->expectException(PlanChangeException::class);

        $this->service()->scheduleDowngrade(
            $subscription,
            $this->planPrice(2, 1900, $this->devise('dollar (USD)'), false),
            $this->paymentMethod(),
        );
    }

    public function testRejectsFreeTarget(): void
    {
        $eur = $this->devise('euro (EUR)');
        $subscription = $this->subscription($this->planPrice(1, 4900, $eur, false));

        $this->expectException(PlanChangeException::class);

        $this->service()->scheduleDowngrade(
            $subscription,
            $this->planPrice(2, 0, $eur, true),
            $this->paymentMethod(),
        );
    }

    public function testRejectsWhenSubscriptionIsNotActive(): void
    {
        $eur = $this->devise('euro (EUR)');
        $subscription = $this->subscription($this->planPrice(1, 4900, $eur, false));
        $subscription->setStatus(SubscriptionStatus::PAST_DUE);

        $this->expectException(PlanChangeException::class);

        $this->service()->scheduleDowngrade(
            $subscription,
            $this->planPrice(2, 1900, $eur, false),
            $this->paymentMethod(),
        );
    }

    public function testRejectsWhenCancellationAlreadyScheduled(): void
    {
        $eur = $this->devise('euro (EUR)');
        $subscription = $this->subscription($this->planPrice(1, 4900, $eur, false));
        $subscription->setCancelAtPeriodEnd(true);

        $this->expectException(PlanChangeException::class);

        $this->service()->scheduleDowngrade(
            $subscription,
            $this->planPrice(2, 1900, $eur, false),
            $this->paymentMethod(),
        );
    }

    public function testRejectsWhenCurrentPeriodEndIsUnknown(): void
    {
        $eur = $this->devise('euro (EUR)');
        $subscription = $this->subscription($this->planPrice(1, 4900, $eur, false));
        $subscription->setCurrentPeriodEnd(null);

        $this->expectException(PlanChangeException::class);

        $this->service()->scheduleDowngrade(
            $subscription,
            $this->planPrice(2, 1900, $eur, false),
            $this->paymentMethod(),
        );
    }

    public function testNoOpWhenTheSameDowngradeIsAlreadyScheduled(): void
    {
        $eur = $this->devise('euro (EUR)');
        $target = $this->planPrice(2, 1900, $eur, false);

        $subscription = $this->subscription($this->planPrice(1, 4900, $eur, false));
        $subscription
            ->setPendingPlanPrice($target)
            ->setPendingPlanChangeEffectiveAt(new \DateTimeImmutable('2026-09-30 00:00:00'))
            ->setPendingPlanChangeRequestedAt(new \DateTimeImmutable('2026-09-01 00:00:00'));

        $result = $this->service()->scheduleDowngrade($subscription, $target, $this->paymentMethod());

        self::assertSame($subscription, $result);
        self::assertSame($target, $subscription->getPendingPlanPrice());
        self::assertSame(
            '2026-09-01 00:00:00',
            $subscription->getPendingPlanChangeRequestedAt()?->format('Y-m-d H:i:s'),
        );
    }

    private function service(): SubscriptionPlanChangeService
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        return new SubscriptionPlanChangeService(
            $entityManager,
            new StripeSubscriptionService(new StripeClient('sk_test_dummy'), $entityManager),
            new SubscriptionHistoryRecorder($entityManager),
            new NullLogger(),
        );
    }

    private function subscription(SubscriptionPlanPrice $currentPlanPrice): AgencySubscription
    {
        $subscription = new AgencySubscription();
        $subscription
            ->setAgency(new User())
            ->setPlan($currentPlanPrice->getPlan())
            ->setPlanPrice($currentPlanPrice)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setCancelAtPeriodEnd(false)
            ->setCurrentPeriodEnd(new \DateTimeImmutable('2026-09-30 00:00:00'));

        return $subscription;
    }

    private function planPrice(int $id, int $amountMinor, Devise $currency, bool $free): SubscriptionPlanPrice
    {
        $plan = new SubscriptionPlan();
        $plan->setCode('plan-'.$id)->setName('Plan '.$id)->setIsFree($free);

        $planPrice = new SubscriptionPlanPrice();
        $planPrice->setPlan($plan)->setCurrency($currency)->setAmountMinor($amountMinor);

        $this->forceId($planPrice, $id);

        return $planPrice;
    }

    private function devise(string $nom): Devise
    {
        $devise = new Devise();
        $devise->setNom($nom)->setSigne('€');

        return $devise;
    }

    private function paymentMethod(): AgencyPaymentMethod
    {
        $paymentMethod = new AgencyPaymentMethod();
        $paymentMethod->setStripePaymentMethodId('pm_test_dummy');

        return $paymentMethod;
    }

    private function forceId(object $entity, int $id): void
    {
        $property = new \ReflectionProperty($entity, 'id');
        $property->setValue($entity, $id);
    }
}
