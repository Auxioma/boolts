<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Repository\Billing\AgencySubscriptionRepository;
use App\Entity\Billing\AgencySubscription;
use App\Service\Stripe\StripeSubscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SubscriptionProcessor
{
    public function __construct(
        private AgencySubscriptionRepository $subscriptionRepository,
        private FreeSubscriptionRenewalService $freeRenewalService,
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

    public function process(?\DateTimeImmutable $now = null): SubscriptionProcessingReport
    {
        $now ??= new \DateTimeImmutable();
        $report = new SubscriptionProcessingReport($now, $this->batchSize);

        $this->processFreeSubscriptions($now, $report);
        $this->processActiveSubscriptions($now, $report);
        $this->processPaymentFailures($now, $report);
        $this->processDefinitivePaymentFailures($now, $report);
        $this->processCanceledSubscriptions($now, $report);
        $this->processSubscriptionsToSynchronize($now, $report);

        return $report;
    }

    private function processFreeSubscriptions(
        \DateTimeImmutable $now,
        SubscriptionProcessingReport $report,
    ): void {
        $subscriptions = $this->subscriptionRepository->findFreeSubscriptionsToRenew($now, $this->batchSize);
        $report->startPhase('FREE_RENEWAL', \count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $this->guardedProcess(
                'FREE_RENEWAL',
                $subscription,
                fn () => $this->freeRenewalService->renew($subscription, $now),
                $report,
            );
        }

        $this->entityManager->clear();
    }

    private function processActiveSubscriptions(
        \DateTimeImmutable $now,
        SubscriptionProcessingReport $report,
    ): void
    {
        $subscriptions = $this->subscriptionRepository->findActiveSubscriptionsToProcess($now, $this->batchSize);
        $report->startPhase('ACTIVE_RENEWAL', \count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $this->guardedProcess(
                'ACTIVE_RENEWAL',
                $subscription,
                fn () => $this->renewalService->processActiveSubscription($subscription),
                $report,
            );
        }

        $this->entityManager->clear();
    }

    private function processPaymentFailures(
        \DateTimeImmutable $now,
        SubscriptionProcessingReport $report,
    ): void
    {
        $subscriptions = $this->subscriptionRepository->findFailedSubscriptionsToRetry($now, $this->batchSize);
        $report->startPhase('PAYMENT_RETRY', \count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $this->guardedProcess(
                'PAYMENT_RETRY',
                $subscription,
                fn () => $this->recoveryService->processRetry($subscription, $now),
                $report,
            );
        }

        $this->entityManager->clear();
    }

    private function processDefinitivePaymentFailures(
        \DateTimeImmutable $now,
        SubscriptionProcessingReport $report,
    ): void
    {
        $subscriptions = $this->subscriptionRepository->findFailedSubscriptionsToFinalize($now, $this->batchSize);
        $report->startPhase('PAYMENT_FAILURE_FINALIZATION', \count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $this->guardedProcess(
                'PAYMENT_FAILURE_FINALIZATION',
                $subscription,
                fn () => $this->recoveryService->finalizeDefinitiveFailure($subscription, $now),
                $report,
            );
        }

        $this->entityManager->clear();
    }

    private function processCanceledSubscriptions(
        \DateTimeImmutable $now,
        SubscriptionProcessingReport $report,
    ): void
    {
        $subscriptions = $this->subscriptionRepository->findCanceledSubscriptionsToFinalize($now, $this->batchSize);
        $report->startPhase('CANCELLATION_FINALIZATION', \count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $this->guardedProcess(
                'CANCELLATION_FINALIZATION',
                $subscription,
                fn () => $this->cancellationService->finalizeCancellation($subscription, $now),
                $report,
            );
        }

        $this->entityManager->clear();
    }

    private function processSubscriptionsToSynchronize(
        \DateTimeImmutable $now,
        SubscriptionProcessingReport $report,
    ): void
    {
        $staleBefore = $now->modify('-6 hours');
        $subscriptions = $this->subscriptionRepository->findSubscriptionsToSynchronize($staleBefore, $this->batchSize);
        $report->startPhase('STRIPE_SYNCHRONIZATION', \count($subscriptions));

        foreach ($subscriptions as $subscription) {
            $subscriptionId = $subscription->getProviderSubscriptionId();

            if (!\is_string($subscriptionId) || '' === $subscriptionId) {
                $report->skipped(
                    'STRIPE_SYNCHRONIZATION',
                    $subscription,
                    'Identifiant d’abonnement Stripe absent.',
                );

                continue;
            }

            $this->guardedProcess(
                'STRIPE_SYNCHRONIZATION',
                $subscription,
                fn () => $this->synchronizationService->synchronizeFromStripe(
                    $subscription,
                    $this->stripeSubscriptionService->retrieve($subscriptionId),
                ),
                $report,
            );
        }

        $this->entityManager->clear();
    }

    /**
     * @param callable(): void $operation
     */
    private function guardedProcess(
        string $action,
        AgencySubscription $subscription,
        callable $operation,
        SubscriptionProcessingReport $report,
    ): void {
        try {
            $operation();
            $report->succeeded($action, $subscription);
        } catch (\Throwable $exception) {
            $report->failed($action, $subscription, $exception);
            $this->logger->error('[SUBSCRIPTION CRON] Subscription processing failed.', [
                'action' => $action,
                'subscription' => $subscription->getId(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        }
    }
}
