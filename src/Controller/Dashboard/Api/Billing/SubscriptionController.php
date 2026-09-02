<?php

declare(strict_types=1);

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

use App\Entity\AgencyNotification;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\PaymentAttemptStatus;
use App\Entity\Billing\Enum\PaymentMethodSetupStatus;
use App\Entity\Billing\Enum\PaymentStatus;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentAttempt;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\User;
use App\Exception\PlanChangeException;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Service\Billing\InvoiceIssuer;
use App\Service\Stripe\StripeSubscriptionService;
use App\Service\Subscription\SubscriptionPlanChangeService;
use App\Service\Subscription\SubscriptionSynchronizationService;
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
        private readonly AgencySubscriptionRepository $agencySubscriptionRepository,
        private readonly StripeSubscriptionService $stripeSubscriptionService,
        private readonly SubscriptionSynchronizationService $subscriptionSynchronizationService,
        private readonly SubscriptionPlanChangeService $planChangeService,
        private readonly InvoiceIssuer $invoiceIssuer,
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

        $existingSubscription = $this->agencySubscriptionRepository->findOneActivePaidForAgency($agency);

        if ($existingSubscription instanceof AgencySubscription) {
            return $this->changePlan($existingSubscription, $planPrice, $paymentMethod);
        }

        try {
            $stripePriceId = $this->stripeSubscriptionService->getOrCreateStripePrice($planPrice);
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

    /**
     * Routes an already-subscribed agency to the right flow depending on how the
     * chosen plan price compares to the current one: higher or equal amount is an
     * immediate change (existing upgrade path), a strictly lower amount is deferred
     * to the end of the paid period via a Stripe subscription schedule.
     */
    private function changePlan(
        AgencySubscription $subscription,
        SubscriptionPlanPrice $planPrice,
        AgencyPaymentMethod $paymentMethod,
    ): JsonResponse {
        $currentPlanPrice = $subscription->getPlanPrice();

        if (!$currentPlanPrice instanceof SubscriptionPlanPrice) {
            return $this->json(['success' => false, 'message' => 'Le prix de l’abonnement actuel est introuvable.'], 409);
        }

        if ($planPrice->getId() === $currentPlanPrice->getId()) {
            return $this->json(['success' => false, 'message' => 'C’est déjà votre forfait actuel.'], 409);
        }

        if ($planPrice->getAmountMinor() < $currentPlanPrice->getAmountMinor()) {
            return $this->scheduleDowngrade($subscription, $planPrice, $paymentMethod);
        }

        return $this->upgrade($subscription, $planPrice, $paymentMethod);
    }

    private function scheduleDowngrade(
        AgencySubscription $subscription,
        SubscriptionPlanPrice $planPrice,
        AgencyPaymentMethod $paymentMethod,
    ): JsonResponse {
        try {
            $updated = $this->planChangeService->scheduleDowngrade($subscription, $planPrice, $paymentMethod);
        } catch (PlanChangeException $exception) {
            return $this->json(['success' => false, 'message' => $exception->getMessage()], 409);
        } catch (ApiErrorException $exception) {
            $this->logger->error('Erreur Stripe pendant la programmation du changement de forfait.', [
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'agency_id' => $subscription->getAgency()->getId(),
                'subscription_plan_price_id' => $planPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Stripe : '.$exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur interne pendant la programmation du changement de forfait.', [
                'message' => $exception->getMessage(),
                'agency_id' => $subscription->getAgency()->getId(),
                'subscription_plan_price_id' => $planPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Impossible de programmer le changement de forfait.'], 500);
        }

        $effectiveAt = $updated->getPendingPlanChangeEffectiveAt();

        return $this->json([
            'success' => true,
            'scheduled' => true,
            'effectiveAt' => $effectiveAt?->format(\DATE_ATOM),
            'message' => \sprintf(
                'Votre passage au forfait %s sera effectif le %s. Aucun paiement aujourd’hui : il sera prélevé à cette date lors du renouvellement.',
                $planPrice->getPlan()->getName(),
                $effectiveAt?->format('d/m/Y') ?? 'à la fin de votre période en cours',
            ),
        ], 200);
    }

    private function persistSubscription(User $agency, SubscriptionPlanPrice $planPrice, AgencyPaymentMethod $paymentMethod, Subscription $stripeSubscription): void
    {
        [$periodStart, $periodEnd] = $this->resolveSubscriptionPeriod($stripeSubscription);
        $subscriptionItem = $this->firstSubscriptionItem($stripeSubscription);
        $invoiceId = \is_string($stripeSubscription->latest_invoice) ? $stripeSubscription->latest_invoice : null;
        $now = new \DateTimeImmutable();
        $providerPriceId = $this->resolveStripePriceId($subscriptionItem) ?? $planPrice->getPaymentProviderPriceId();
        $providerProductId = $this->resolveStripeProductId($subscriptionItem);

        $this->closeOpenFreeSubscriptions($agency, $periodStart);

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
            ->setProviderSubscriptionItemId(null !== $subscriptionItem ? $this->readStripeString($subscriptionItem, 'id') : null)
            ->setProviderPriceId($providerPriceId)
            ->setProviderProductId($providerProductId)
            ->setProviderLatestInvoiceId($invoiceId)
            ->setPropertyLimitSnapshot($planPrice->getPlan()->getPropertyLimit())
            ->setIncludedBoostsSnapshot($planPrice->getPlan()->getIncludedBoosts())
            ->setBoostDurationDaysSnapshot($planPrice->getPlan()->getBoostDurationDays())
            ->setAmountSnapshotMinor($planPrice->getAmountMinor())
            ->setCurrencySnapshot($planPrice->getCurrency())
            ->setLastSuccessfulPaymentAt($now)
            ->setLastStripeSyncAt($now);

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
            ->setBillingPeriodStart($periodStart)
            ->setBillingPeriodEnd($periodEnd)
            ->setAttemptNumber(1)
            ->setPaidAt($now);

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
            ->setSubscription($subscription)
            ->setPayment($payment)
            ->setPaymentMethod($paymentMethod)
            ->setStatus(PaymentAttemptStatus::SUCCEEDED)
            ->setProviderInvoiceId($invoiceId)
            ->setAmountMinor($planPrice->getAmountMinor())
            ->setCurrency($planPrice->getCurrency())
            ->setAttemptedAt($now)
            ->setCompletedAt($now);
        $subscriptionCredit = $this->createSubscriptionCreditTransaction(
            $agency,
            $period,
            $payment,
            $stripeSubscription->id,
        );

        $this->entityManager->persist($subscription);
        $this->entityManager->persist($payment);
        $this->entityManager->persist($period);
        $this->entityManager->persist($attempt);
        if ($subscriptionCredit instanceof BoosterTransaction) {
            $this->entityManager->persist($subscriptionCredit);
        }
        $this->entityManager->persist($this->buildPlanActivatedNotification($agency, $planPrice));

        $this->invoiceIssuer->issueForInitialPurchase(
            $subscription,
            $period,
            $payment,
            $planPrice,
            $invoiceId,
            $now,
        );

        $this->entityManager->flush();
    }

    /**
     * Notifie l'agence que son abonnement (souscription initiale ou montée en gamme) est actif.
     */
    private function notifyPlanActivated(User $agency, SubscriptionPlanPrice $planPrice): void
    {
        $this->entityManager->persist($this->buildPlanActivatedNotification($agency, $planPrice));
        $this->entityManager->flush();
    }

    private function buildPlanActivatedNotification(User $agency, SubscriptionPlanPrice $planPrice): AgencyNotification
    {
        return (new AgencyNotification())
            ->setAgency($agency)
            ->setNom(\sprintf(
                'Votre abonnement %s a été activé.',
                $planPrice->getPlan()->getName(),
            ));
    }

    private function createSubscriptionCreditTransaction(
        User $agency,
        AgencySubscriptionPeriod $period,
        Payment $payment,
        string $stripeSubscriptionId,
    ): ?BoosterTransaction {
        if ($period->getIncludedBoosts() <= 0) {
            return null;
        }

        return (new BoosterTransaction())
            ->setAgency($agency)
            ->setQuantity($period->getIncludedBoosts())
            ->setType(BoosterTransactionType::SUBSCRIPTION_CREDIT)
            ->setSubscriptionPeriod($period)
            ->setPayment($payment)
            ->setExpiresAt($period->getPeriodEnd())
            ->setIdempotencyKey(\sprintf(
                'subscription-credit-%s-%d',
                $stripeSubscriptionId,
                $period->getPeriodStart()->getTimestamp(),
            ))
            ->setDescription('Boosts inclus dans l’abonnement.');
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable}
     */
    private function resolveSubscriptionPeriod(Subscription $stripeSubscription): array
    {
        $subscriptionItem = $this->firstSubscriptionItem($stripeSubscription);
        $periodStartTimestamp = null !== $subscriptionItem
            ? $this->readStripeTimestamp($subscriptionItem, 'current_period_start')
            : null;
        $periodEndTimestamp = null !== $subscriptionItem
            ? $this->readStripeTimestamp($subscriptionItem, 'current_period_end')
            : null;

        $periodStartTimestamp ??= $this->readStripeTimestamp($stripeSubscription, 'current_period_start');
        $periodEndTimestamp ??= $this->readStripeTimestamp($stripeSubscription, 'current_period_end');

        if (null === $periodStartTimestamp || null === $periodEndTimestamp) {
            throw new \LogicException('Stripe n’a pas retourné les dates de période de l’abonnement.');
        }

        return [
            (new \DateTimeImmutable())->setTimestamp($periodStartTimestamp),
            (new \DateTimeImmutable())->setTimestamp($periodEndTimestamp),
        ];
    }

    private function firstSubscriptionItem(Subscription $stripeSubscription): ?object
    {
        $subscriptionItem = $stripeSubscription->items->data[0] ?? null;

        return \is_object($subscriptionItem) ? $subscriptionItem : null;
    }

    private function readStripeTimestamp(object $stripeObject, string $property): ?int
    {
        if (!isset($stripeObject->{$property}) || !is_numeric($stripeObject->{$property})) {
            return null;
        }

        $timestamp = (int) $stripeObject->{$property};

        return $timestamp > 0 ? $timestamp : null;
    }

    private function readStripeString(object $stripeObject, string $property): ?string
    {
        if (!isset($stripeObject->{$property}) || !\is_string($stripeObject->{$property})) {
            return null;
        }

        return '' !== $stripeObject->{$property} ? $stripeObject->{$property} : null;
    }

    private function resolveStripePriceId(?object $subscriptionItem): ?string
    {
        if (null === $subscriptionItem || !isset($subscriptionItem->price) || !\is_object($subscriptionItem->price)) {
            return null;
        }

        return $this->readStripeString($subscriptionItem->price, 'id');
    }

    private function resolveStripeProductId(?object $subscriptionItem): ?string
    {
        if (null === $subscriptionItem || !isset($subscriptionItem->price) || !\is_object($subscriptionItem->price)) {
            return null;
        }

        $product = $subscriptionItem->price->product ?? null;

        if (\is_string($product) && '' !== $product) {
            return $product;
        }

        if (\is_object($product)) {
            return $this->readStripeString($product, 'id');
        }

        return null;
    }

    private function closeOpenFreeSubscriptions(User $agency, \DateTimeImmutable $endedAt): void
    {
        foreach ($this->agencySubscriptionRepository->findOpenFreeForAgency($agency) as $freeSubscription) {
            $freeSubscription
                ->setStatus(SubscriptionStatus::CANCELED)
                ->setCancelAtPeriodEnd(false)
                ->setCanceledAt($endedAt)
                ->setEndedAt($endedAt);

            if (null === $freeSubscription->getCurrentPeriodEnd() || $freeSubscription->getCurrentPeriodEnd() > $endedAt) {
                $freeSubscription->setCurrentPeriodEnd($endedAt);
            }

            $this->closeOpenFreePeriods($freeSubscription, $endedAt);
        }
    }

    private function closeOpenFreePeriods(AgencySubscription $freeSubscription, \DateTimeImmutable $endedAt): void
    {
        $periods = $this->entityManager->getRepository(AgencySubscriptionPeriod::class)
            ->createQueryBuilder('period')
            ->where('period.subscription = :subscription')
            ->andWhere('period.status = :status')
            ->andWhere('period.periodEnd >= :endedAt')
            ->setParameter('subscription', $freeSubscription)
            ->setParameter('status', SubscriptionPeriodStatus::FREE)
            ->setParameter('endedAt', $endedAt)
            ->getQuery()
            ->getResult();

        foreach ($periods as $period) {
            $period->setStatus(SubscriptionPeriodStatus::CANCELED);

            if ($period->getPeriodStart() <= $endedAt && $period->getPeriodEnd() > $endedAt) {
                $period->setPeriodEnd($endedAt);
            }
        }
    }

    private function upgrade(
        AgencySubscription $subscription,
        SubscriptionPlanPrice $planPrice,
        AgencyPaymentMethod $paymentMethod,
    ): JsonResponse {
        if (!\in_array($subscription->getStatus(), [SubscriptionStatus::ACTIVE, SubscriptionStatus::CANCEL_SCHEDULED], true)) {
            return $this->json([
                'success' => false,
                'message' => 'L’abonnement actuel doit être actif pour changer de forfait.',
            ], 409);
        }

        $currentPlanPrice = $subscription->getPlanPrice();

        if (!$currentPlanPrice instanceof SubscriptionPlanPrice) {
            return $this->json(['success' => false, 'message' => 'Le prix de l’abonnement actuel est introuvable.'], 409);
        }

        if ($currentPlanPrice->getCurrency() !== $planPrice->getCurrency()) {
            return $this->json(['success' => false, 'message' => 'La devise du nouveau forfait doit être identique.'], 409);
        }

        if ($planPrice->getAmountMinor() <= $currentPlanPrice->getAmountMinor()) {
            return $this->json([
                'success' => false,
                'message' => 'Vous pouvez uniquement choisir un forfait d’un montant supérieur.',
            ], 409);
        }

        try {
            if ($subscription->hasPendingPlanChange()) {
                $scheduleId = $subscription->getProviderScheduleId();

                if (\is_string($scheduleId) && '' !== $scheduleId) {
                    $this->stripeSubscriptionService->releaseSchedule($scheduleId);
                }

                $subscription->clearPendingPlanChange();
                $this->entityManager->flush();
            }

            $stripeSubscription = $this->stripeSubscriptionService->retrieve(
                (string) $subscription->getProviderSubscriptionId(),
            );
            $stripeSnapshot = $this->stripeSubscriptionService->snapshot($stripeSubscription);

            if ($stripeSnapshot->priceId === $planPrice->getPaymentProviderPriceId()) {
                $this->subscriptionSynchronizationService->synchronizeFromStripe(
                    $subscription,
                    $stripeSubscription,
                    paymentType: PaymentType::SUBSCRIPTION_UPGRADE,
                );

                $this->notifyPlanActivated($subscription->getAgency(), $planPrice);

                return $this->json([
                    'success' => true,
                    'message' => 'Votre nouveau forfait est actif.',
                    'subscriptionId' => $stripeSubscription->id,
                    'status' => $stripeSubscription->status,
                ]);
            }

            $stripeSubscription = $this->stripeSubscriptionService->upgradeNow(
                $subscription,
                $planPrice,
                $paymentMethod,
            );

            $this->subscriptionSynchronizationService->synchronizeFromStripe(
                $subscription,
                $stripeSubscription,
                paymentType: PaymentType::SUBSCRIPTION_UPGRADE,
            );

            $this->notifyPlanActivated($subscription->getAgency(), $planPrice);

            return $this->json([
                'success' => true,
                'message' => 'Votre nouveau forfait est actif.',
                'subscriptionId' => $stripeSubscription->id,
                'status' => $stripeSubscription->status,
            ]);
        } catch (ApiErrorException $exception) {
            $this->logger->error('Erreur Stripe pendant le changement de forfait.', [
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'agency_id' => $subscription->getAgency()->getId(),
                'subscription_id' => $subscription->getId(),
                'subscription_plan_price_id' => $planPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Stripe : '.$exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur interne pendant le changement de forfait.', [
                'message' => $exception->getMessage(),
                'agency_id' => $subscription->getAgency()->getId(),
                'subscription_id' => $subscription->getId(),
                'subscription_plan_price_id' => $planPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Impossible de changer de forfait.'], 500);
        }
    }
}
