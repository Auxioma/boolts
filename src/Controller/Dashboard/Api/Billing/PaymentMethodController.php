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

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\Enum\PaymentMethodSetupStatus;
use App\Entity\User;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/agence/billing')]
#[IsGranted('ROLE_AGENCE')]
/**
 * HTTP controller for module Dashboard / Api / Billing / PaymentMethodController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class PaymentMethodController extends AbstractController
{
    /**
     * Handles the __construct controller action.
     */
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly EntityManagerInterface $entityManager,
        private readonly AgencyPaymentMethodRepository $paymentMethodRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(
        '/setup-intent',
        name: 'api_agency_billing_setup_intent',
        methods: ['POST']
    )]
    /**
     * Handles the createSetupIntent controller action.
     */
    public function createSetupIntent(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid(
            'agency_payment_method',
            (string) $request->headers->get('X-CSRF-TOKEN')
        )) {
            return $this->json([
                'success' => false,
                'message' => 'Jeton CSRF invalide.',
            ], 403);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        try {
            $billingProfile = $this->getOrCreateBillingProfile($user);
            $stripeCustomerId = $this->getOrCreateStripeCustomer(
                $user,
                $billingProfile
            );

            $setupIntent = $this->stripe->setupIntents->create([
                'customer' => $stripeCustomerId,
                'usage' => 'off_session',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'user_id' => (string) $user->getId(),
                    'billing_profile_id' => (string) $billingProfile->getId(),
                ],
            ]);

            return $this->json([
                'success' => true,
                'setupIntentId' => $setupIntent->id,
                'clientSecret' => $setupIntent->client_secret,
            ]);
        } catch (ApiErrorException $exception) {
            $this->logger->error('Erreur Stripe pendant la création du SetupIntent.', [
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'user_id' => $user->getId(),
            ]);

            return $this->json([
                'success' => false,
                'message' => 'Stripe : '.$exception->getMessage(),
            ], 400);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur interne pendant la création du SetupIntent.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'user_id' => $user->getId(),
            ]);

            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    #[Route(
        '/payment-method/complete',
        name: 'api_agency_billing_payment_method_complete',
        methods: ['POST']
    )]
    /**
     * Handles the completePaymentMethod controller action.
     */
    public function completePaymentMethod(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid(
            'agency_payment_method',
            (string) $request->headers->get('X-CSRF-TOKEN')
        )) {
            return $this->json([
                'success' => false,
                'message' => 'Jeton CSRF invalide.',
            ], 403);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            return $this->json([
                'success' => false,
                'message' => 'Requête JSON invalide.',
            ], 400);
        }

        $setupIntentId = $payload['setupIntentId'] ?? null;
        $setAsDefault = filter_var(
            $payload['setAsDefault'] ?? false,
            \FILTER_VALIDATE_BOOL
        );

        if (
            !\is_string($setupIntentId)
            || !str_starts_with($setupIntentId, 'seti_')
        ) {
            return $this->json([
                'success' => false,
                'message' => 'SetupIntent invalide.',
            ], 400);
        }

        $billingProfile = $user->getBillingProfile();

        if (!$billingProfile instanceof AgencyBillingProfile) {
            return $this->json([
                'success' => false,
                'message' => 'Profil de facturation introuvable.',
            ], 404);
        }

        try {
            $setupIntent = $this->stripe->setupIntents->retrieve(
                $setupIntentId,
                ['expand' => ['payment_method']]
            );

            if (SetupIntent::STATUS_SUCCEEDED !== $setupIntent->status) {
                return $this->json([
                    'success' => false,
                    'message' => \sprintf(
                        'La carte n’est pas validée. Statut Stripe : %s.',
                        $setupIntent->status
                    ),
                ], 400);
            }

            $setupIntentCustomerId = $this->extractStripeId(
                $setupIntent->customer
            );

            if (
                null === $setupIntentCustomerId
                || $setupIntentCustomerId !== $billingProfile->getStripeCustomerId()
            ) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce SetupIntent ne vous appartient pas.',
                ], 403);
            }

            $paymentMethod = $setupIntent->payment_method;

            if (\is_string($paymentMethod)) {
                $paymentMethod = $this->stripe->paymentMethods->retrieve(
                    $paymentMethod
                );
            }

            if (!$paymentMethod instanceof PaymentMethod) {
                return $this->json([
                    'success' => false,
                    'message' => 'Moyen de paiement Stripe introuvable.',
                ], 404);
            }

            if (
                'card' !== $paymentMethod->type
                || null === $paymentMethod->card
            ) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le moyen de paiement obtenu n’est pas une carte.',
                ], 400);
            }

            $fingerprint = $paymentMethod->card->fingerprint;

            if (!\is_string($fingerprint) || '' === $fingerprint) {
                return $this->json([
                    'success' => false,
                    'message' => 'Stripe n’a retourné aucune empreinte.',
                ], 400);
            }

            $existing = $this->paymentMethodRepository
                ->findOneByStripePaymentMethodId($paymentMethod->id);

            if ($existing instanceof AgencyPaymentMethod) {
                if ($setAsDefault) {
                    $this->setDefaultPaymentMethod(
                        $billingProfile,
                        $existing
                    );
                }

                return $this->json([
                    'success' => true,
                    'duplicate' => true,
                    'message' => 'Cette carte est déjà enregistrée.',
                    'paymentMethod' => $this->serializePaymentMethod($existing),
                ]);
            }

            $sameCard = $this->paymentMethodRepository->findOneByFingerprint(
                $billingProfile,
                $fingerprint
            );

            if ($sameCard instanceof AgencyPaymentMethod) {
                return $this->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => \sprintf(
                        'Cette carte est déjà enregistrée et se termine par %s.',
                        $sameCard->getLast4()
                    ),
                ], 409);
            }

            $cardholderName = null;

            if (
                null !== $paymentMethod->billing_details
                && \is_string($paymentMethod->billing_details->name)
            ) {
                $cardholderName = mb_trim($paymentMethod->billing_details->name);
            }

            $agencyPaymentMethod = (new AgencyPaymentMethod())
                ->setBillingProfile($billingProfile)
                ->setStripePaymentMethodId($paymentMethod->id)
                ->setStripeSetupIntentId($setupIntent->id)
                ->setStripeMandateId(
                    $this->extractStripeId($setupIntent->mandate)
                )
                ->setType($paymentMethod->type)
                ->setBrand($paymentMethod->card->brand)
                ->setLast4($paymentMethod->card->last4)
                ->setExpMonth($paymentMethod->card->exp_month)
                ->setExpYear($paymentMethod->card->exp_year)
                ->setCardholderName($cardholderName)
                ->setCountryCode($paymentMethod->card->country)
                ->setFunding($paymentMethod->card->funding)
                ->setFingerprint($fingerprint)
                ->setIsDefault(false)
                ->setIsActive(true)
                ->setSetupStatus(PaymentMethodSetupStatus::SUCCEEDED);

            $this->entityManager->persist($agencyPaymentMethod);
            $this->entityManager->flush();

            if (
                $setAsDefault
                || null === $billingProfile->getDefaultPaymentMethod()
            ) {
                $this->setDefaultPaymentMethod(
                    $billingProfile,
                    $agencyPaymentMethod
                );
            }

            return $this->json([
                'success' => true,
                'message' => 'Le moyen de paiement a bien été enregistré.',
                'paymentMethod' => $this->serializePaymentMethod(
                    $agencyPaymentMethod
                ),
            ], 201);
        } catch (ApiErrorException $exception) {
            $this->logger->error('Erreur Stripe pendant la finalisation.', [
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'setup_intent_id' => $setupIntentId,
                'user_id' => $user->getId(),
            ]);

            return $this->json([
                'success' => false,
                'message' => 'Stripe : '.$exception->getMessage(),
            ], 400);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur interne pendant la finalisation.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'setup_intent_id' => $setupIntentId,
                'user_id' => $user->getId(),
            ]);

            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function getOrCreateBillingProfile(
        User $user,
    ): AgencyBillingProfile {
        $billingProfile = $user->getBillingProfile();

        if ($billingProfile instanceof AgencyBillingProfile) {
            return $billingProfile;
        }

        $billingProfile = (new AgencyBillingProfile())
            ->setAgency($user)
            ->setBillingEmail($user->getEmail())
            ->setLegalName(
                mb_trim(($user->getPrenom() ?? '').' '.($user->getNom() ?? ''))
                ?: $user->getUserIdentifier()
            );

        $this->entityManager->persist($billingProfile);
        $this->entityManager->flush();

        return $billingProfile;
    }

    private function getOrCreateStripeCustomer(
        User $user,
        AgencyBillingProfile $billingProfile,
    ): string {
        $stripeCustomerId = $billingProfile->getStripeCustomerId();

        if (
            \is_string($stripeCustomerId)
            && str_starts_with($stripeCustomerId, 'cus_')
        ) {
            return $stripeCustomerId;
        }

        $customer = $this->stripe->customers->create([
            'email' => $billingProfile->getBillingEmail()
                ?: $user->getEmail(),
            'name' => $billingProfile->getLegalName()
                ?: $user->getUserIdentifier(),
            'metadata' => [
                'user_id' => (string) $user->getId(),
                'billing_profile_id' => (string) $billingProfile->getId(),
            ],
        ]);

        $billingProfile->setStripeCustomerId($customer->id);
        $this->entityManager->flush();

        return $customer->id;
    }

    private function setDefaultPaymentMethod(
        AgencyBillingProfile $billingProfile,
        AgencyPaymentMethod $paymentMethod,
    ): void {
        $this->paymentMethodRepository
            ->unsetDefaultForBillingProfile($billingProfile);

        $paymentMethod->setIsDefault(true);
        $billingProfile->setDefaultPaymentMethod($paymentMethod);

        $this->stripe->customers->update(
            $billingProfile->getStripeCustomerId(),
            [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->getStripePaymentMethodId(),
                ],
            ]
        );

        $this->entityManager->flush();
    }

    private function extractStripeId(mixed $value): ?string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (
            \is_object($value)
            && isset($value->id)
            && \is_string($value->id)
        ) {
            return $value->id;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePaymentMethod(
        AgencyPaymentMethod $paymentMethod,
    ): array {
        return [
            'id' => $paymentMethod->getId(),
            'brand' => $paymentMethod->getBrand(),
            'last4' => $paymentMethod->getLast4(),
            'expMonth' => $paymentMethod->getExpMonth(),
            'expYear' => $paymentMethod->getExpYear(),
            'cardholderName' => $paymentMethod->getCardholderName(),
            'countryCode' => $paymentMethod->getCountryCode(),
            'funding' => $paymentMethod->getFunding(),
            'default' => $paymentMethod->isIsDefault(),
            'active' => $paymentMethod->isIsActive(),
            'status' => $paymentMethod->getSetupStatus()->value,
        ];
    }
}
