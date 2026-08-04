<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Dashboard\Api\Billing;

use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\PaymentAttemptStatus;
use App\Entity\Billing\Enum\PaymentMethodSetupStatus;
use App\Entity\Billing\Enum\PaymentStatus;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentAttempt;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/agence/billing')]
#[IsGranted('ROLE_AGENCE')]
/**
 * HTTP controller for module Dashboard / Api / Billing / SubscriptionController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class SubscriptionController extends AbstractController
{
    /**
     * Handles the __construct controller action.
     */
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/subscription', name: 'api_agency_billing_subscription_create', methods: ['POST'])]
    /**
     * Handles the create controller action.
     */
    public function create(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('agency_subscription', (string) $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 403);
        }

        $agency = $this->getUser();

        if (!$agency instanceof User) {
            return $this->json(['success' => false, 'message' => 'Utilisateur non authentifié.'], 401);
        }

        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            return $this->json(['success' => false, 'message' => 'Requête JSON invalide.'], 400);
        }

        $planPriceId = filter_var($payload['planPriceId'] ?? null, \FILTER_VALIDATE_INT);
        $paymentMethodId = filter_var($payload['paymentMethodId'] ?? null, \FILTER_VALIDATE_INT);

        if (false === $planPriceId || null === $planPriceId || $planPriceId < 1) {
            return $this->json(['success' => false, 'message' => 'Forfait invalide.'], 400);
        }

        $planPrice = $this->entityManager->getRepository(SubscriptionPlanPrice::class)->find($planPriceId);

        if (!$planPrice instanceof SubscriptionPlanPrice || !$planPrice->isActive() || !$planPrice->getPlan()->isActive()) {
            return $this->json(['success' => false, 'message' => 'Forfait indisponible.'], 404);
        }

        if ($planPrice->getAmountMinor() <= 0) {
            return $this->json(['success' => false, 'message' => 'Ce forfait gratuit ne nécessite pas de paiement Stripe.'], 400);
        }

        $billingProfile = $agency->getBillingProfile();

        if (null === $billingProfile || !str_starts_with((string) $billingProfile->getStripeCustomerId(), 'cus_')) {
            return $this->json(['success' => false, 'message' => 'Profil de facturation ou client Stripe introuvable.'], 409);
        }

        $paymentMethod = false !== $paymentMethodId && null !== $paymentMethodId && $paymentMethodId > 0
            ? $this->entityManager->getRepository(AgencyPaymentMethod::class)->find($paymentMethodId)
            : $billingProfile->getDefaultPaymentMethod();

        if (!$paymentMethod instanceof AgencyPaymentMethod || $paymentMethod->getBillingProfile() !== $billingProfile || !$paymentMethod->isActive() || PaymentMethodSetupStatus::SUCCEEDED !== $paymentMethod->getSetupStatus()) {
            return $this->json(['success' => false, 'message' => 'Aucune carte bancaire valide n’est disponible pour cet achat.'], 409);
        }

        $existingSubscription = $this->entityManager->getRepository(AgencySubscription::class)
            ->createQueryBuilder('subscription')
            ->where('subscription.agency = :agency')
            ->andWhere('subscription.status IN (:statuses)')
            ->setParameter('agency', $agency)
            ->setParameter('statuses', [SubscriptionStatus::ACTIVE, SubscriptionStatus::INCOMPLETE, SubscriptionStatus::PAST_DUE])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingSubscription instanceof AgencySubscription) {
            return $this->json(['success' => false, 'message' => 'Un abonnement est déjà en cours.'], 409);
        }

        try {
            $stripePriceId = $this->getOrCreateStripePrice($planPrice);
            $stripeSubscription = $this->stripe->subscriptions->create([
                'customer' => $billingProfile->getStripeCustomerId(),
                'items' => [['price' => $stripePriceId]],
                'default_payment_method' => $paymentMethod->getStripePaymentMethodId(),
                'payment_behavior' => 'error_if_incomplete',
                'metadata' => [
                    'agency_id' => (string) $agency->getId(),
                    'subscription_plan_price_id' => (string) $planPrice->getId(),
                ],
            ]);

            $this->persistSubscription($agency, $planPrice, $paymentMethod, $stripeSubscription);

            return $this->json([
                'success' => true,
                'message' => 'Votre abonnement est actif.',
                'subscriptionId' => $stripeSubscription->id,
                'status' => $stripeSubscription->status,
            ], 201);
        } catch (ApiErrorException $exception) {
            $this->logger->error('Erreur Stripe pendant la création de l’abonnement.', [
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'agency_id' => $agency->getId(),
                'subscription_plan_price_id' => $planPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Stripe : '.$exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur interne pendant la création de l’abonnement.', [
                'message' => $exception->getMessage(),
                'agency_id' => $agency->getId(),
                'subscription_plan_price_id' => $planPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Impossible de créer l’abonnement.'], 500);
        }
    }

    private function getOrCreateStripePrice(SubscriptionPlanPrice $planPrice): string
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
            'recurring' => ['interval' => SubscriptionBillingPeriod::ANNUAL === $planPrice->getBillingPeriod() ? 'year' : 'month'],
            'metadata' => ['subscription_plan_price_id' => (string) $planPrice->getId()],
        ]);

        $planPrice->setPaymentProviderPriceId($price->id);
        $this->entityManager->flush();

        return $price->id;
    }

    private function persistSubscription(User $agency, SubscriptionPlanPrice $planPrice, AgencyPaymentMethod $paymentMethod, Subscription $stripeSubscription): void
    {
        $periodStart = (new \DateTimeImmutable())->setTimestamp((int) $stripeSubscription->current_period_start);
        $periodEnd = (new \DateTimeImmutable())->setTimestamp((int) $stripeSubscription->current_period_end);
        $invoiceId = \is_string($stripeSubscription->latest_invoice) ? $stripeSubscription->latest_invoice : null;

        $subscription = (new AgencySubscription())
            ->setAgency($agency)
            ->setPlan($planPrice->getPlan())
            ->setPlanPrice($planPrice)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartedAt($periodStart)
            ->setCurrentPeriodStart($periodStart)
            ->setCurrentPeriodEnd($periodEnd)
            ->setProviderCustomerId($stripeSubscription->customer)
            ->setProviderSubscriptionId($stripeSubscription->id)
            ->setProviderSubscriptionItemId($stripeSubscription->items->data[0]->id ?? null)
            ->setPropertyLimitSnapshot($planPrice->getPlan()->getPropertyLimit())
            ->setIncludedBoostsSnapshot($planPrice->getPlan()->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($planPrice->getPlan()->getBoostDurationDays())
            ->setAmountSnapshotMinor($planPrice->getAmountMinor())
            ->setCurrencySnapshot($planPrice->getCurrency());

        $payment = (new Payment())
            ->setReference('SUB-'.mb_strtoupper(bin2hex(random_bytes(8))))
            ->setAgency($agency)
            ->setBillingProfile($paymentMethod->getBillingProfile())
            ->setPaymentMethod($paymentMethod)
            ->setSubscription($subscription)
            ->setType(PaymentType::SUBSCRIPTION_INITIAL)
            ->setStatus(PaymentStatus::SUCCEEDED)
            ->setAmountSubtotalMinor($planPrice->getAmountMinor())
            ->setAmountTotalMinor($planPrice->getAmountMinor())
            ->setAmountPaidMinor($planPrice->getAmountMinor())
            ->setCurrency($planPrice->getCurrency())
            ->setProviderInvoiceId($invoiceId)
            ->setPaymentMethodSnapshot(['brand' => $paymentMethod->getBrand(), 'last4' => $paymentMethod->getLast4()])
            ->setMetadata(['stripe_subscription_id' => $stripeSubscription->id])
            ->setPaidAt(new \DateTimeImmutable());

        $period = (new AgencySubscriptionPeriod())
            ->setSubscription($subscription)
            ->setPeriodStart($periodStart)
            ->setPeriodEnd($periodEnd)
            ->setPropertyLimit($planPrice->getPlan()->getPropertyLimit())
            ->setIncludedBoosts($planPrice->getPlan()->getIncludedBoosts())
            ->setAmountMinor($planPrice->getAmountMinor())
            ->setCurrency($planPrice->getCurrency())
            ->setPayment($payment)
            ->setStatus(SubscriptionPeriodStatus::PAID)
            ->setProviderInvoiceId($invoiceId);

        $attempt = (new PaymentAttempt())
            ->setPayment($payment)
            ->setPaymentMethod($paymentMethod)
            ->setStatus(PaymentAttemptStatus::SUCCEEDED)
            ->setAmountMinor($planPrice->getAmountMinor())
            ->setCurrency($planPrice->getCurrency())
            ->setCompletedAt(new \DateTimeImmutable());

        $this->entityManager->persist($subscription);
        $this->entityManager->persist($payment);
        $this->entityManager->persist($period);
        $this->entityManager->persist($attempt);
        $this->entityManager->flush();
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
