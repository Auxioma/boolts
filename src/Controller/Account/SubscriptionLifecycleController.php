<?php

declare(strict_types=1);

namespace App\Controller\Account;

use App\Entity\User;
use App\Service\Stripe\StripeCustomerService;
use App\Service\Subscription\SubscriptionCancellationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/account/subscription', name: 'account_subscription_')]
#[IsGranted('ROLE_AGENCE')]
final class SubscriptionLifecycleController extends AbstractController
{
    public function __construct(
        private readonly SubscriptionCancellationService $cancellationService,
        private readonly StripeCustomerService $stripeCustomerService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('account_subscription_cancel', $this->readCsrfToken($request))) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');

            return $this->redirectToBillingSettings();
        }

        try {
            $this->cancellationService->requestCancellation($this->agency());
            $this->addFlash('success', 'Votre résiliation est programmée à la fin de la période déjà payée.');
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION] Unable to schedule cancellation from customer area.', [
                'agency' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                'message' => $exception->getMessage(),
            ]);

            $this->addFlash('error', 'Impossible de programmer la résiliation de votre abonnement.');
        }

        return $this->redirectToBillingSettings();
    }

    #[Route('/reactivate', name: 'reactivate', methods: ['POST'])]
    public function reactivate(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('account_subscription_reactivate', $this->readCsrfToken($request))) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');

            return $this->redirectToBillingSettings();
        }

        try {
            $this->cancellationService->revokeCancellation($this->agency());
            $this->addFlash('success', 'Votre abonnement restera actif après la période en cours.');
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION] Unable to reactivate subscription from customer area.', [
                'agency' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                'message' => $exception->getMessage(),
            ]);

            $this->addFlash('error', 'Impossible de réactiver votre abonnement.');
        }

        return $this->redirectToBillingSettings();
    }

    #[Route('/customer-portal', name: 'customer_portal', methods: ['GET', 'POST'])]
    public function customerPortal(): RedirectResponse
    {
        $agency = $this->agency();
        $billingProfile = $agency->getBillingProfile();
        $stripeCustomerId = $billingProfile?->getStripeCustomerId();

        if (!\is_string($stripeCustomerId) || !str_starts_with($stripeCustomerId, 'cus_')) {
            $this->addFlash('error', 'Aucun profil de facturation Stripe actif n’est disponible.');

            return $this->redirectToBillingSettings();
        }

        try {
            return new RedirectResponse($this->stripeCustomerService->createBillingPortalSession(
                $stripeCustomerId,
                $this->billingSettingsUrl(),
            ));
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION] Unable to create Stripe Customer Portal session.', [
                'agency' => $agency->getId(),
                'stripe_customer' => $stripeCustomerId,
                'message' => $exception->getMessage(),
            ]);

            $this->addFlash('error', 'Impossible d’ouvrir le portail de paiement pour le moment.');

            return $this->redirectToBillingSettings();
        }
    }

    private function agency(): User
    {
        $agency = $this->getUser();

        if (!$agency instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return $agency;
    }

    private function readCsrfToken(Request $request): string
    {
        return (string) ($request->request->get('_token') ?? $request->headers->get('X-CSRF-TOKEN'));
    }

    private function redirectToBillingSettings(): RedirectResponse
    {
        return new RedirectResponse($this->billingSettingsUrl());
    }

    private function billingSettingsUrl(): string
    {
        return $this->urlGenerator->generate(
            'agence_immobiliere_parametres',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL,
        ).'#cat4';
    }
}
