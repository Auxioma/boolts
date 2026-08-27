<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Repository\Billing\AgencySubscriptionRepository;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SubscriptionProcessor
{
    public function __construct(
        private AgencySubscriptionRepository $subscriptionRepository,
        private SubscriptionRenewalService $renewalService,
        private SubscriptionPaymentRecoveryService $recoveryService,
        private SubscriptionCancellationService $cancellationService,
        private SubscriptionSynchronizationService $synchronizationService,
        private StripeSubscriptionService $stripeSubscriptionService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        #[Autowire('%app.subscription_batch_size%')]
        private int $batchSize,
    ) {
    }

    public function process(?\DateTimeImmutable $now = null): void
    {
        $now ??= new \DateTimeImmutable();

        $this->processActiveSubscriptions($now);
        $this->processPaymentFailures($now);
        $this->processDefinitivePaymentFailures($now);
        $this->processCanceledSubscriptions($now);
        $this->processSubscriptionsToSynchronize($now);
    }

    private function processActiveSubscriptions(\DateTimeImmutable $now): void
    {
        foreach ($this->subscriptionRepository->findActiveSubscriptionsToProcess($now, $this->batchSize) as $subscription) {
            $this->guardedProcess(
                'ACTIVE_RENEWAL',
                (int) $subscription->getId(),
                fn () => $this->renewalService->processActiveSubscription($subscription),
            );
        }

        $this->entityManager->clear();
    }

    private function processPaymentFailures(\DateTimeImmutable $now): void
    {
        foreach ($this->subscriptionRepository->findFailedSubscriptionsToRetry($now, $this->batchSize) as $subscription) {
            $this->guardedProcess(
                'PAYMENT_RETRY',
                (int) $subscription->getId(),
                fn () => $this->recoveryService->processRetry($subscription, $now),
            );
        }

        $this->entityManager->clear();
    }

    private function processDefinitivePaymentFailures(\DateTimeImmutable $now): void
    {
        foreach ($this->subscriptionRepository->findFailedSubscriptionsToFinalize($now, $this->batchSize) as $subscription) {
            $this->guardedProcess(
                'PAYMENT_FAILURE_FINALIZATION',
                (int) $subscription->getId(),
                fn () => $this->recoveryService->finalizeDefinitiveFailure($subscription, $now),
            );
        }

        $this->entityManager->clear();
    }

    private function processCanceledSubscriptions(\DateTimeImmutable $now): void
    {
        foreach ($this->subscriptionRepository->findCanceledSubscriptionsToFinalize($now, $this->batchSize) as $subscription) {
            $this->guardedProcess(
                'CANCELLATION_FINALIZATION',
                (int) $subscription->getId(),
                fn () => $this->cancellationService->finalizeCancellation($subscription, $now),
            );
        }

        $this->entityManager->clear();
    }

    private function processSubscriptionsToSynchronize(\DateTimeImmutable $now): void
    {
        $staleBefore = $now->modify('-6 hours');

        foreach ($this->subscriptionRepository->findSubscriptionsToSynchronize($staleBefore, $this->batchSize) as $subscription) {
            $subscriptionId = $subscription->getProviderSubscriptionId();

            if (!\is_string($subscriptionId) || '' === $subscriptionId) {
                continue;
            }

            $this->guardedProcess(
                'STRIPE_SYNCHRONIZATION',
                (int) $subscription->getId(),
                fn () => $this->synchronizationService->synchronizeFromStripe(
                    $subscription,
                    $this->stripeSubscriptionService->retrieve($subscriptionId),
                ),
            );
        }

        $this->entityManager->clear();
    }

    /**
     * @param callable(): void $operation
     */
    private function guardedProcess(
        string $action,
        int $subscriptionId,
        callable $operation,
    ): void {
        try {
            $operation();
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION CRON] Subscription processing failed.', [
                'action' => $action,
                'subscription' => $subscriptionId,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }
    }
}
