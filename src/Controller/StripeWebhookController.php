<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Billing\Enum\DowngradeReason;
use App\Entity\Billing\Enum\WebhookEventStatus;
use App\Entity\Billing\PaymentWebhookEvent;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Billing\PaymentWebhookEventRepository;
use App\Service\Stripe\StripeInvoiceService;
use App\Service\Stripe\StripeSubscriptionService;
use App\Service\Subscription\SubscriptionDowngradeService;
use App\Service\Subscription\SubscriptionPaymentRecoveryService;
use App\Service\Subscription\SubscriptionSynchronizationService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Invoice as StripeInvoice;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentWebhookEventRepository $webhookEventRepository,
        private readonly AgencySubscriptionRepository $subscriptionRepository,
        private readonly StripeSubscriptionService $stripeSubscriptionService,
        private readonly StripeInvoiceService $stripeInvoiceService,
        private readonly SubscriptionPaymentRecoveryService $recoveryService,
        private readonly SubscriptionSynchronizationService $synchronizationService,
        private readonly SubscriptionDowngradeService $downgradeService,
        private readonly LoggerInterface $logger,
        #[Autowire('%stripe.webhook_secret%')]
        private readonly string $webhookSecret,
    ) {
    }

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->headers->get('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException|\UnexpectedValueException $exception) {
            $this->logger->warning('[STRIPE WEBHOOK] Invalid Stripe webhook received.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->json(['success' => false], 400);
        }

        $webhookEvent = $this->webhookEventRepository->findOneBy([
            'providerEventId' => $event->id,
        ]);

        if (
            $webhookEvent instanceof PaymentWebhookEvent
            && WebhookEventStatus::PROCESSED === $webhookEvent->getStatus()
        ) {
            return $this->json(['success' => true, 'duplicate' => true]);
        }

        if (!$webhookEvent instanceof PaymentWebhookEvent) {
            $webhookEvent = (new PaymentWebhookEvent())
                ->setProvider('stripe')
                ->setProviderEventId((string) $event->id)
                ->setEventType((string) $event->type)
                ->setApiVersion(\is_string($event->api_version ?? null) ? $event->api_version : null)
                ->setLivemode((bool) ($event->livemode ?? false))
                ->setPayload($this->decodePayload($payload))
                ->setStatus(WebhookEventStatus::RECEIVED);

            $this->entityManager->persist($webhookEvent);

            try {
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                return $this->json(['success' => true, 'duplicate' => true]);
            }
        }

        $webhookEvent
            ->setStatus(WebhookEventStatus::PROCESSING)
            ->setAttemptCount($webhookEvent->getAttemptCount() + 1);
        $this->entityManager->flush();

        try {
            $this->processStripeEvent($event);

            $webhookEvent
                ->setStatus(WebhookEventStatus::PROCESSED)
                ->setProcessedAt(new \DateTimeImmutable())
                ->setErrorMessage(null);

            $this->entityManager->flush();

            return $this->json(['success' => true]);
        } catch (\Throwable $exception) {
            $webhookEvent
                ->setStatus(WebhookEventStatus::FAILED)
                ->setFailedAt(new \DateTimeImmutable())
                ->setErrorMessage($exception->getMessage());

            $this->entityManager->flush();

            $this->logger->error('[STRIPE WEBHOOK] Stripe webhook processing failed.', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->json(['success' => false], 500);
        }
    }

    private function processStripeEvent(Event $event): void
    {
        match ((string) $event->type) {
            'invoice.paid' => $this->handleInvoicePaid($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            default => null,
        };
    }

    private function handleInvoicePaid(Event $event): void
    {
        $stripeInvoice = $this->resolveInvoice($event->data->object ?? null);
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

        if (null === $invoice->subscriptionId) {
            return;
        }

        $subscription = $this->subscriptionRepository->findOneByProviderSubscriptionId($invoice->subscriptionId);

        if (null === $subscription) {
            $this->logger->warning('[STRIPE WEBHOOK] Local subscription not found for paid invoice.', [
                'stripe_subscription' => $invoice->subscriptionId,
                'invoice' => $invoice->id,
            ]);

            return;
        }

        $stripeSubscription = $this->stripeSubscriptionService->retrieve($invoice->subscriptionId);
        $this->synchronizationService->synchronizeFromStripe($subscription, $stripeSubscription, $stripeInvoice);
    }

    private function handleInvoicePaymentFailed(Event $event): void
    {
        $stripeInvoice = $this->resolveInvoice($event->data->object ?? null);
        $invoice = $this->stripeInvoiceService->snapshot($stripeInvoice);

        if (null === $invoice->subscriptionId) {
            return;
        }

        $subscription = $this->subscriptionRepository->findOneByProviderSubscriptionId($invoice->subscriptionId);

        if (null === $subscription) {
            return;
        }

        $stripeSubscription = $this->stripeSubscriptionService->retrieve($invoice->subscriptionId);
        $this->recoveryService->handleInvoicePaymentFailed($subscription, $stripeSubscription, $stripeInvoice);
    }

    private function handleSubscriptionUpdated(Event $event): void
    {
        $stripeSubscription = $this->resolveSubscription($event->data->object ?? null);
        $subscription = $this->subscriptionRepository->findOneByProviderSubscriptionId((string) $stripeSubscription->id);

        if (null === $subscription) {
            return;
        }

        $this->synchronizationService->synchronizeFromStripe($subscription, $stripeSubscription);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeSubscription = $this->resolveSubscription($event->data->object ?? null);
        $subscription = $this->subscriptionRepository->findOneByProviderSubscriptionId((string) $stripeSubscription->id);

        if (null === $subscription) {
            return;
        }

        $this->synchronizationService->synchronizeSubscriptionFields($subscription, $stripeSubscription);
        $this->entityManager->flush();
        $this->downgradeService->downgradeToFree($subscription, DowngradeReason::STRIPE_SUBSCRIPTION_DELETED);
    }

    private function resolveInvoice(mixed $value): StripeInvoice
    {
        if ($value instanceof StripeInvoice) {
            return $value;
        }

        if (\is_object($value) && isset($value->id) && \is_string($value->id)) {
            return $this->stripeInvoiceService->retrieve($value->id);
        }

        throw new \RuntimeException('Payload Stripe invoice inexploitable.');
    }

    private function resolveSubscription(mixed $value): StripeSubscription
    {
        if ($value instanceof StripeSubscription) {
            return $value;
        }

        if (\is_object($value) && isset($value->id) && \is_string($value->id)) {
            return $this->stripeSubscriptionService->retrieve($value->id);
        }

        throw new \RuntimeException('Payload Stripe subscription inexploitable.');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(string $payload): array
    {
        $decoded = json_decode($payload, true);

        return \is_array($decoded) ? $decoded : [];
    }
}
