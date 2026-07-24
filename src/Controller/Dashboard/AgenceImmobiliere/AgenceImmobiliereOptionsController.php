<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Dashboard\AgenceImmobiliere;

use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\User;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Billing\SubscriptionPlanPriceRepository;
use App\Repository\Booster\BoosterPackPriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/options', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
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
       
    ){}

    #[Route('/', name: 'options')]
    /**
     * Handles the index controller action.
     */
    public function index(
        SubscriptionPlanPriceRepository $subscriptionPlanPriceRepository,
        BoosterPackPriceRepository $boosterPackPriceRepository,
        AgencySubscriptionRepository $agencySubscriptionRepository,
    ): Response
    {
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
    ): Response
    {
        $period = $request->query->get('period', 'monthly');

        if (!in_array($period, ['monthly', 'annual'], true)) {
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

        if ($agency instanceof User && $agency->getBillingProfile() !== null) {
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
            static fn (AgencyPaymentMethod $paymentMethod): bool =>
                $paymentMethod !== $defaultPaymentMethod
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
}
