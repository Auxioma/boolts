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

namespace App\Controller\Dashboard\AgenceImmobiliere;

use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Enum\PaymentAttemptStatus;
use App\Entity\Billing\Enum\PaymentMethodSetupStatus;
use App\Entity\Billing\Enum\PaymentStatus;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\Payment;
use App\Entity\Billing\PaymentAttempt;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Booster\BoosterPackPrice;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\User;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Billing\SubscriptionPlanPriceRepository;
use App\Repository\Booster\BoosterPackPriceRepository;
use App\Security\Voter\AgencyDocumentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/options', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
#[IsGranted(
    AgencyDocumentVoter::ACCESS_RESTRICTED_DASHBOARD,
    message: 'Vos documents doivent être validés pour accéder à cette page.',
)]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / AgenceImmobiliereOptionsController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereOptionsController extends AbstractController
{
    /**
     * Handles the __construct controller action.
     */
    public function __construct(
    ) {
    }

    #[Route('/', name: 'options')]
    /**
     * Handles the index controller action.
     */
    public function index(
        SubscriptionPlanPriceRepository $subscriptionPlanPriceRepository,
        BoosterPackPriceRepository $boosterPackPriceRepository,
        AgencySubscriptionRepository $agencySubscriptionRepository,
    ): Response {
        $agency = $this->getUser();

        if (!$agency instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $subscriptionPrices = $subscriptionPlanPriceRepository->findActiveWithPlanAndCurrency();

        $forfaits = [];

        foreach ($subscriptionPrices as $subscriptionPrice) {
            $plan = $subscriptionPrice->getPlan();
            $planId = $plan->getId();

            if (!isset($forfaits[$planId])) {
                $forfaits[$planId] = [
                    'plan' => $plan,
                    'monthly' => null,
                    'annual' => null,
                ];
            }

            $forfaits[$planId][$subscriptionPrice->getBillingPeriod()->value] = $subscriptionPrice;
        }

        $boosterPackPrices = $boosterPackPriceRepository->findActiveWithPackAndCurrency();

        $currentSubscription = $agencySubscriptionRepository->findLatestForAgency($agency);

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_options/index.html.twig',
            [
                'forfaits' => $forfaits,
                'packs_boost' => $boosterPackPrices,
                'abonnement_actuel' => $currentSubscription,
            ]
        );
    }

    #[Route('/achat/{id}', name: 'achat')]
    /**
     * Handles the achat controller action.
     */
    public function achat(
        int $id,
        Request $request,
        SubscriptionPlanPriceRepository $subscriptionPlanPriceRepository,
        AgencyPaymentMethodRepository $paymentMethodRepository,
        #[Autowire('%stripe.public_key%')]
        string $stripePublicKey,
    ): Response {
        $period = $request->query->get('period', 'monthly');

        if (!\in_array($period, ['monthly', 'annual'], true)) {
            throw $this->createNotFoundException('Période de facturation invalide.');
        }

        $planPrice = $subscriptionPlanPriceRepository->findActiveForPlanAndPeriod(
            $id,
            SubscriptionBillingPeriod::from($period),
        );

        if (!$planPrice instanceof SubscriptionPlanPrice) {
            throw $this->createNotFoundException('Forfait indisponible pour cette période de facturation.');
        }

        $agency = $this->getUser();
        $paymentMethods = [];
        $defaultPaymentMethod = null;

        if ($agency instanceof User && null !== $agency->getBillingProfile()) {
            $paymentMethods = $paymentMethodRepository->findActiveByBillingProfile(
                $agency->getBillingProfile()
            );

            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->isDefault()) {
                    $defaultPaymentMethod = $paymentMethod;
                    break;
                }
            }

            $defaultPaymentMethod ??= $paymentMethods[0] ?? null;
        }

        $otherPaymentMethods = array_values(array_filter(
            $paymentMethods,
            static fn (AgencyPaymentMethod $paymentMethod): bool => $paymentMethod !== $defaultPaymentMethod
        ));

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_options/achat.html.twig',
            [
                'plan' => $planPrice->getPlan(),
                'plan_price' => $planPrice,
                'period' => $period,
                'default_payment_method' => $defaultPaymentMethod,
                'other_payment_methods' => $otherPaymentMethods,
                'stripe_public_key' => $stripePublicKey,
            ]
        );
    }

    #[Route('/boost/{id}', name: 'boost')]
    /**
     * Handles the boost controller action.
     */
    public function boost(
        int $id,
        BoosterPackPriceRepository $boosterPackPriceRepository,
        AgencyPaymentMethodRepository $paymentMethodRepository,
        #[Autowire('%stripe.public_key%')]
        string $stripePublicKey,
    ): Response {
        $boostPrice = $boosterPackPriceRepository->find($id);

        if (null === $boostPrice || !$boostPrice->isIsActive() || !$boostPrice->getBoosterPack()->isIsActive()) {
            throw $this->createNotFoundException('Pack boost indisponible.');
        }

        $agency = $this->getUser();
        $paymentMethods = [];
        $defaultPaymentMethod = null;

        if ($agency instanceof User && null !== $agency->getBillingProfile()) {
            $paymentMethods = $paymentMethodRepository->findActiveByBillingProfile(
                $agency->getBillingProfile()
            );

            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod->isDefault()) {
                    $defaultPaymentMethod = $paymentMethod;
                    break;
                }
            }

            $defaultPaymentMethod ??= $paymentMethods[0] ?? null;
        }

        $otherPaymentMethods = array_values(array_filter(
            $paymentMethods,
            static fn (AgencyPaymentMethod $paymentMethod): bool => $paymentMethod !== $defaultPaymentMethod
        ));

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_options/boost.html.twig',
            [
                'boost_pack' => $boostPrice->getBoosterPack(),
                'boost_price' => $boostPrice,
                'default_payment_method' => $defaultPaymentMethod,
                'other_payment_methods' => $otherPaymentMethods,
                'stripe_public_key' => $stripePublicKey,
            ]
        );
    }

    #[Route('/boost/{id}/payment', name: 'boost_payment', methods: ['POST'])]
    /**
     * Charges an already saved Stripe card for a one-time booster pack purchase.
     */
    public function payBoost(
        int $id,
        Request $request,
        BoosterPackPriceRepository $boosterPackPriceRepository,
        AgencyPaymentMethodRepository $paymentMethodRepository,
        EntityManagerInterface $entityManager,
        StripeClient $stripe,
        LoggerInterface $logger,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('agency_boost_purchase', (string) $request->headers->get('X-CSRF-TOKEN'))) {
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

        $paymentMethodId = filter_var($payload['paymentMethodId'] ?? null, \FILTER_VALIDATE_INT);

        if (false === $paymentMethodId || null === $paymentMethodId || $paymentMethodId < 1) {
            return $this->json(['success' => false, 'message' => 'Carte bancaire invalide.'], 400);
        }

        $boostPrice = $boosterPackPriceRepository->find($id);

        if (!$boostPrice instanceof BoosterPackPrice || !$boostPrice->isIsActive() || !$boostPrice->getBoosterPack()->isIsActive()) {
            return $this->json(['success' => false, 'message' => 'Pack boost indisponible.'], 404);
        }

        if ($boostPrice->getAmountMinor() <= 0) {
            return $this->json(['success' => false, 'message' => 'Le prix du pack boost doit être supérieur à zéro.'], 400);
        }

        $billingProfile = $agency->getBillingProfile();

        if (null === $billingProfile || !str_starts_with((string) $billingProfile->getStripeCustomerId(), 'cus_')) {
            return $this->json(['success' => false, 'message' => 'Profil de facturation ou client Stripe introuvable.'], 409);
        }

        $paymentMethod = $paymentMethodRepository->find($paymentMethodId);

        if (!$paymentMethod instanceof AgencyPaymentMethod
            || $paymentMethod->getBillingProfile() !== $billingProfile
            || !$paymentMethod->isActive()
            || PaymentMethodSetupStatus::SUCCEEDED !== $paymentMethod->getSetupStatus()
        ) {
            return $this->json(['success' => false, 'message' => 'Aucune carte bancaire valide n’est disponible pour cet achat.'], 409);
        }

        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $boostPrice->getAmountMinor(),
                'currency' => $this->currencyCode($boostPrice),
                'customer' => $billingProfile->getStripeCustomerId(),
                'payment_method' => $paymentMethod->getStripePaymentMethodId(),
                'payment_method_types' => ['card'],
                'confirm' => true,
                'description' => \sprintf('Pack boost %s', $boostPrice->getBoosterPack()->getName()),
                'metadata' => [
                    'agency_id' => (string) $agency->getId(),
                    'booster_pack_price_id' => (string) $boostPrice->getId(),
                    'booster_pack_id' => (string) $boostPrice->getBoosterPack()->getId(),
                ],
            ]);

            if ('succeeded' !== $paymentIntent->status) {
                return $this->json(['success' => false, 'message' => 'Le paiement Stripe n’a pas été confirmé.'], 402);
            }

            $payment = (new Payment())
                ->setReference('BOOST-'.mb_strtoupper(bin2hex(random_bytes(8))))
                ->setAgency($agency)
                ->setBillingProfile($billingProfile)
                ->setPaymentMethod($paymentMethod)
                ->setBoosterPack($boostPrice->getBoosterPack())
                ->setType(PaymentType::BOOSTER_PACK)
                ->setStatus(PaymentStatus::SUCCEEDED)
                ->setAmountSubtotalMinor($boostPrice->getAmountMinor())
                ->setAmountTotalMinor($boostPrice->getAmountMinor())
                ->setAmountPaidMinor($boostPrice->getAmountMinor())
                ->setCurrency($boostPrice->getCurrency())
                ->setProviderPaymentIntentId($paymentIntent->id)
                ->setProviderChargeId(\is_string($paymentIntent->latest_charge) ? $paymentIntent->latest_charge : null)
                ->setPaymentMethodSnapshot([
                    'brand' => $paymentMethod->getBrand(),
                    'last4' => $paymentMethod->getLast4(),
                ])
                ->setMetadata(['booster_pack_price_id' => $boostPrice->getId()])
                ->setPaidAt(new \DateTimeImmutable());

            $boostTransaction = (new BoosterTransaction())
                ->setAgency($agency)
                ->setQuantity($boostPrice->getBoosterPack()->getBoostQuantity())
                ->setType(BoosterTransactionType::PACK_PURCHASE)
                ->setBoosterPack($boostPrice->getBoosterPack())
                ->setPayment($payment)
                ->setExpiresAt((new \DateTimeImmutable())->modify(\sprintf('+%d days', $boostPrice->getBoosterPack()->getBoostDurationDays())))
                ->setDescription(\sprintf('Achat du pack boost %s', $boostPrice->getBoosterPack()->getName()));

            $paymentAttempt = (new PaymentAttempt())
                ->setPayment($payment)
                ->setPaymentMethod($paymentMethod)
                ->setStatus(PaymentAttemptStatus::SUCCEEDED)
                ->setProviderPaymentIntentId($paymentIntent->id)
                ->setProviderChargeId(\is_string($paymentIntent->latest_charge) ? $paymentIntent->latest_charge : null)
                ->setAmountMinor($boostPrice->getAmountMinor())
                ->setCurrency($boostPrice->getCurrency())
                ->setCompletedAt(new \DateTimeImmutable());

            $entityManager->persist($payment);
            $entityManager->persist($boostTransaction);
            $entityManager->persist($paymentAttempt);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Votre pack boost a été ajouté.',
                'paymentIntentId' => $paymentIntent->id,
            ], 201);
        } catch (ApiErrorException $exception) {
            $logger->error('Erreur Stripe pendant le paiement du pack boost.', [
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'agency_id' => $agency->getId(),
                'booster_pack_price_id' => $boostPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Stripe : '.$exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            $logger->error('Erreur interne pendant le paiement du pack boost.', [
                'message' => $exception->getMessage(),
                'agency_id' => $agency->getId(),
                'booster_pack_price_id' => $boostPrice->getId(),
            ]);

            return $this->json(['success' => false, 'message' => 'Impossible de payer le pack boost.'], 500);
        }
    }

    private function currencyCode(BoosterPackPrice $boostPrice): string
    {
        preg_match('/\\(([A-Z]{3})\\)/', (string) $boostPrice->getCurrency()->getNom(), $matches);

        if (!isset($matches[1])) {
            throw new \LogicException('Le code ISO de la devise du pack boost est introuvable.');
        }

        return mb_strtolower($matches[1]);
    }
}
