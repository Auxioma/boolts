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

namespace App\Controller\Account;

use App\Entity\Billing\AgencySubscription;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Service\Stripe\StripeCustomerService;
use App\Service\Subscription\SubscriptionCancellationService;
use App\Service\Subscription\SubscriptionPlanChangeService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(
    path: [
        'en' => '/account/subscription',
        'fr' => '/fr/account/subscription',
    ],
    name: 'account_subscription_'
)]
#[IsGranted('ROLE_AGENCE')]
final class SubscriptionLifecycleController extends AbstractController
{
    public function __construct(
        private readonly SubscriptionCancellationService $cancellationService,
        private readonly SubscriptionPlanChangeService $planChangeService,
        private readonly AgencySubscriptionRepository $subscriptionRepository,
        private readonly StripeCustomerService $stripeCustomerService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('account_subscription_cancel', $this->readCsrfToken($request))) {
            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.invalid_csrf'));

            return $this->redirectAfterLifecycleAction($request);
        }

        try {
            $this->cancellationService->requestCancellation($this->agency());
            $this->addFlash('subscription_success', $this->translator->trans('agency_options.flash.cancel_success'));
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION] Unable to schedule cancellation from customer area.', [
                'agency' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                'message' => $exception->getMessage(),
            ]);

            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.cancel_error'));
        }

        return $this->redirectAfterLifecycleAction($request);
    }

    #[Route('/reactivate', name: 'reactivate', methods: ['POST'])]
    public function reactivate(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('account_subscription_reactivate', $this->readCsrfToken($request))) {
            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.invalid_csrf'));

            return $this->redirectAfterLifecycleAction($request);
        }

        try {
            $this->cancellationService->revokeCancellation($this->agency());
            $this->addFlash('subscription_success', $this->translator->trans('agency_options.flash.reactivate_success'));
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION] Unable to reactivate subscription from customer area.', [
                'agency' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                'message' => $exception->getMessage(),
            ]);

            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.reactivate_error'));
        }

        return $this->redirectAfterLifecycleAction($request);
    }

    #[Route('/cancel-plan-change', name: 'cancel_plan_change', methods: ['POST'])]
    public function cancelPlanChange(Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('account_subscription_cancel_plan_change', $this->readCsrfToken($request))) {
            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.invalid_csrf'));

            return $this->redirectAfterLifecycleAction($request);
        }

        $subscription = $this->subscriptionRepository->findOneActivePaidForAgency($this->agency());

        if (!$subscription instanceof AgencySubscription || !$subscription->hasPendingPlanChange()) {
            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.no_plan_change'));

            return $this->redirectAfterLifecycleAction($request);
        }

        try {
            $this->planChangeService->cancelScheduledDowngrade($subscription);
            $this->addFlash('subscription_success', $this->translator->trans('agency_options.flash.cancel_plan_change_success'));
        } catch (\Throwable $exception) {
            $this->logger->error('[SUBSCRIPTION] Unable to cancel scheduled plan change from customer area.', [
                'agency' => $this->getUser() instanceof User ? $this->getUser()->getId() : null,
                'message' => $exception->getMessage(),
            ]);

            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.cancel_plan_change_error'));
        }

        return $this->redirectAfterLifecycleAction($request);
    }

    #[Route('/customer-portal', name: 'customer_portal', methods: ['GET', 'POST'])]
    public function customerPortal(): RedirectResponse
    {
        $agency = $this->agency();
        $billingProfile = $agency->getBillingProfile();
        $stripeCustomerId = $billingProfile?->getStripeCustomerId();

        if (!\is_string($stripeCustomerId) || !str_starts_with($stripeCustomerId, 'cus_')) {
            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.no_billing_profile'));

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

            $this->addFlash('subscription_error', $this->translator->trans('agency_options.flash.customer_portal_error'));

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

    private function redirectAfterLifecycleAction(Request $request): RedirectResponse
    {
        return match ((string) $request->request->get('_redirect_route')) {
            'agence_immobiliere_options' => $this->redirectToRoute('agence_immobiliere_options'),
            default => $this->redirectToBillingSettings(),
        };
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
