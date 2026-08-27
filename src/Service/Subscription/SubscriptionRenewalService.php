<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Service\Stripe\StripeInvoiceService;
use App\Service\Stripe\StripePaymentService;
use App\Service\Stripe\StripeSubscriptionService;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice as StripeInvoice;
use Stripe\PaymentIntent;

final readonly class SubscriptionRenewalService
{
    public function __construct(
        private StripeSubscriptionService $stripeSubscriptionService,
        private StripeInvoiceService $stripeInvoiceService,
        private StripePaymentService $stripePaymentService,
        private SubscriptionPaymentService $paymentService,
        private SubscriptionPaymentRecoveryService $recoveryService,
        private SubscriptionSynchronizationService $synchronizationService,
        private LoggerInterface $logger,
    ) {
    }

    public function processActiveSubscription(
        AgencySubscription $subscription,
    ): void {
        $subscriptionId = $subscription->getProviderSubscriptionId();

        if (!\is_string($subscriptionId) || !str_starts_with($subscriptionId, 'sub_')) {
            $this->logger->warning('[SUBSCRIPTION CRON] Active subscription has no Stripe subscription id.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
            ]);

            return;
        }

        try {
            $stripeSubscription = $this->stripeSubscriptionService->retrieve($subscriptionId);
            $stripeInvoice = $this->stripeInvoiceService->latestInvoiceFromSubscription($stripeSubscription);

            $this->logger->info('[SUBSCRIPTION CRON] Checking active subscription renewal.', [
                'subscription' => $subscription->getId(),
                'agency' => $subscription->getAgency()->getId(),
                'stripe_subscription' => $subscriptionId,
                'action' => 'CHECK_RENEWAL',
            ]);

            if (!$stripeInvoice instanceof StripeInvoice) {
                $this->synchronizationService->synchronizeSubscriptionFields($subscription, $stripeSubscription);

                return;
            }

            $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

            if ($invoice->isPaid()) {
                $this->paymentService->recordPaidInvoice($subscription, $stripeInvoice, $stripeSubscription);

                return;
            }

            if ($this->invoiceRepresentsPaymentFailure($stripeInvoice)) {
                $this->recoveryService->handleInvoicePaymentFailed(
                    $subscription,
                    $stripeSubscription,
                    $stripeInvoice,
                );

                return;
            }

            $this->synchronizationService->synchronizeSubscriptionFields($subscription, $stripeSubscription);
        } catch (ApiErrorException $exception) {
            if ($this->stripePaymentService->isTemporaryStripeFailure($exception)) {
                $this->logger->error('[SUBSCRIPTION CRON] Temporary Stripe error while checking renewal.', [
                    'subscription' => $subscription->getId(),
                    'agency' => $subscription->getAgency()->getId(),
                    'stripe_subscription' => $subscriptionId,
                    'stripe_code' => $exception->getStripeCode(),
                    'http_status' => $exception->getHttpStatus(),
                    'message' => $exception->getMessage(),
                ]);

                return;
            }

            throw $exception;
        }
    }

    private function invoiceRepresentsPaymentFailure(StripeInvoice $stripeInvoice): bool
    {
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

        if ($invoice->isUncollectibleOrVoid()) {
            return true;
        }

        if (!$invoice->isOpen() || $invoice->attemptCount <= 0) {
            return false;
        }

        $paymentIntent = $this->stripePaymentService->retrievePaymentIntent($invoice->paymentIntentId);

        return $paymentIntent instanceof PaymentIntent
            && \in_array((string) $paymentIntent->status, ['requires_payment_method', 'requires_action', 'canceled'], true);
    }
}
