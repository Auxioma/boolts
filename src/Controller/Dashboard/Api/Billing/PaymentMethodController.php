<?php

declare(strict_types=1);

namespace App\Controller\Api\Billing;

use App\Entity\Billing\AgencyPaymentMethod;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
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
final class PaymentMethodController extends AbstractController
{
    public function __construct(
        private readonly StripeClient $stripe,
        private readonly EntityManagerInterface $entityManager,
        private readonly AgencyPaymentMethodRepository $paymentMethodRepository,
    ) {
    }

    /**
     * Première étape :
     * création du SetupIntent Stripe.
     */
    #[Route('/setup-intent', name: 'api_agency_billing_setup_intent', methods: ['POST'])]
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

        /*
         * Adapte cette partie selon ton entité User.
         *
         * Exemple :
         * $agency = $this->getUser()->getAgency();
         */
        $user = $this->getUser();

        if ($user === null) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        /*
         * Adapte le getter à ton architecture.
         */
        $billingProfile = $user->getAgencyBillingProfile();

        if ($billingProfile === null) {
            return $this->json([
                'success' => false,
                'message' => 'Le profil de facturation est introuvable.',
            ], 404);
        }

        try {
            $stripeCustomerId = $billingProfile->getProviderCustomerId();

            /*
             * Création du Customer Stripe s'il n'existe pas encore.
             */
            if (!$stripeCustomerId) {
                $customer = $this->stripe->customers->create([
                    'email' => $user->getEmail(),
                    'name' => $billingProfile->getLegalName()
                        ?: $user->getUserIdentifier(),
                    'metadata' => [
                        'user_id' => (string) $user->getId(),
                        'billing_profile_id' => (string) $billingProfile->getId(),
                    ],
                ]);

                $stripeCustomerId = $customer->id;

                $billingProfile->setProviderCustomerId($stripeCustomerId);

                $this->entityManager->flush();
            }

            /*
             * off_session signifie que cette carte pourra être utilisée
             * plus tard, même lorsque le client n'est pas présent.
             */
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
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Deuxième étape :
     * vérification du SetupIntent et récupération du fingerprint.
     */
    #[Route(
        '/payment-method/complete',
        name: 'api_agency_billing_payment_method_complete',
        methods: ['POST']
    )]
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

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json([
                'success' => false,
                'message' => 'Requête JSON invalide.',
            ], 400);
        }

        $setupIntentId = $payload['setupIntentId'] ?? null;
        $setAsDefault = filter_var(
            $payload['setAsDefault'] ?? false,
            FILTER_VALIDATE_BOOL
        );

        if (!is_string($setupIntentId) || !str_starts_with($setupIntentId, 'seti_')) {
            return $this->json([
                'success' => false,
                'message' => 'SetupIntent invalide.',
            ], 400);
        }

        $user = $this->getUser();

        if ($user === null) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $billingProfile = $user->getAgencyBillingProfile();

        if ($billingProfile === null) {
            return $this->json([
                'success' => false,
                'message' => 'Profil de facturation introuvable.',
            ], 404);
        }

        try {
            /*
             * On récupère tout depuis Stripe côté serveur.
             * On ne fait pas confiance aux informations de carte envoyées
             * par le navigateur.
             */
            $setupIntent = $this->stripe->setupIntents->retrieve(
                $setupIntentId,
                [
                    'expand' => ['payment_method'],
                ]
            );

            if ($setupIntent->status !== SetupIntent::STATUS_SUCCEEDED) {
                return $this->json([
                    'success' => false,
                    'message' => sprintf(
                        'La carte n’a pas été validée. Statut Stripe : %s.',
                        $setupIntent->status
                    ),
                ], 400);
            }

            /*
             * Vérification indispensable :
             * le SetupIntent doit appartenir au Customer Stripe connecté.
             */
            $setupIntentCustomerId = is_string($setupIntent->customer)
                ? $setupIntent->customer
                : $setupIntent->customer?->id;

            if ($setupIntentCustomerId !== $billingProfile->getProviderCustomerId()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ce SetupIntent ne vous appartient pas.',
                ], 403);
            }

            $paymentMethod = $setupIntent->payment_method;

            if (is_string($paymentMethod)) {
                $paymentMethod = $this->stripe->paymentMethods->retrieve(
                    $paymentMethod
                );
            }

            if (!$paymentMethod instanceof PaymentMethod) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le moyen de paiement est introuvable.',
                ], 404);
            }

            if ($paymentMethod->type !== 'card' || $paymentMethod->card === null) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le moyen de paiement obtenu n’est pas une carte.',
                ], 400);
            }

            /*
             * Voici l'empreinte recherchée.
             */
            $fingerprint = $paymentMethod->card->fingerprint;

            if (!$fingerprint) {
                return $this->json([
                    'success' => false,
                    'message' => 'Stripe n’a retourné aucune empreinte.',
                ], 400);
            }

            /*
             * Évite d'enregistrer deux fois exactement le même
             * PaymentMethod Stripe.
             */
            $existingPaymentMethod = $this->paymentMethodRepository
                ->findOneBy([
                    'providerPaymentMethodId' => $paymentMethod->id,
                ]);

            if ($existingPaymentMethod instanceof AgencyPaymentMethod) {
                return $this->json([
                    'success' => true,
                    'duplicate' => true,
                    'message' => 'Ce moyen de paiement est déjà enregistré.',
                    'paymentMethod' => [
                        'id' => $existingPaymentMethod->getId(),
                        'brand' => $paymentMethod->card->brand,
                        'last4' => $paymentMethod->card->last4,
                        'expMonth' => $paymentMethod->card->exp_month,
                        'expYear' => $paymentMethod->card->exp_year,
                        'fingerprint' => $fingerprint,
                    ],
                ]);
            }

            /*
             * Adapte les setters aux véritables noms présents
             * dans ton entité AgencyPaymentMethod.
             */
            $agencyPaymentMethod = new AgencyPaymentMethod();

            $agencyPaymentMethod
                ->setBillingProfile($billingProfile)
                ->setProvider('stripe')
                ->setProviderPaymentMethodId($paymentMethod->id)
                ->setType($paymentMethod->type)
                ->setCardBrand($paymentMethod->card->brand)
                ->setCardLast4($paymentMethod->card->last4)
                ->setCardExpMonth($paymentMethod->card->exp_month)
                ->setCardExpYear($paymentMethod->card->exp_year)
                ->setCardFingerprint($fingerprint)
                ->setFunding($paymentMethod->card->funding)
                ->setCountry($paymentMethod->card->country)
                ->setIsDefault($setAsDefault);

            /*
             * Si cette carte devient la carte par défaut,
             * les autres ne doivent plus l'être.
             */
            if ($setAsDefault) {
                foreach ($billingProfile->getPaymentMethods() as $otherMethod) {
                    $otherMethod->setIsDefault(false);
                }

                $agencyPaymentMethod->setIsDefault(true);

                $this->stripe->customers->update(
                    $billingProfile->getProviderCustomerId(),
                    [
                        'invoice_settings' => [
                            'default_payment_method' => $paymentMethod->id,
                        ],
                    ]
                );
            }

            $this->entityManager->persist($agencyPaymentMethod);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Le moyen de paiement a été enregistré.',
                'paymentMethod' => [
                    'id' => $agencyPaymentMethod->getId(),
                    'providerId' => $paymentMethod->id,
                    'brand' => $paymentMethod->card->brand,
                    'last4' => $paymentMethod->card->last4,
                    'expMonth' => $paymentMethod->card->exp_month,
                    'expYear' => $paymentMethod->card->exp_year,
                    'funding' => $paymentMethod->card->funding,
                    'country' => $paymentMethod->card->country,
                    'fingerprint' => $fingerprint,
                    'default' => $agencyPaymentMethod->isDefault(),
                ],
            ], 201);
        } catch (ApiErrorException $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
