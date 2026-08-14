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

use App\Entity\Billing\AgencySubscription;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Booster\BoosterTransactionRepository;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_AGENCE')]
final class BoostBalanceController extends AbstractController
{
    #[Route('/pro/boost-balance', name: 'agence_immobiliere_boost_balance', methods: ['GET'])]
    public function button(
        BoosterTransactionRepository $boosterTransactionRepository,
        string $variant = 'dashboard',
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        return $this->render('dashboard/agence_immobiliere/_partials/boost_balance_button.html.twig', [
            'boosts_restants' => $boosterTransactionRepository->countAvailableForAgency($user),
            'variant' => $variant,
        ]);
    }

    #[Route('/pro/boost-balance/annonce', name: 'agence_immobiliere_boost_balance_annonce', methods: ['GET'])]
    public function annonce(
        AgencySubscriptionRepository $agencySubscriptionRepository,
        PropertyRepository $propertyRepository,
        string $variant = 'dashboard',
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $subscription = $agencySubscriptionRepository->findLatestQuotaForAgency($user);
        $propertyLimit = $this->resolvePropertyLimit($subscription);
        $usedProperties = $propertyRepository->countUsedForAgencyQuota($user);

        return $this->render('dashboard/agence_immobiliere/_partials/boost_balance_annonce.html.twig', [
            'annonces_restantes' => null === $propertyLimit ? null : max(0, $propertyLimit - $usedProperties),
            'annonces_utilisees' => $usedProperties,
            'limite_annonces' => $propertyLimit,
            'variant' => $variant,
        ]);
    }

    private function resolvePropertyLimit(?AgencySubscription $subscription): ?int
    {
        if (!$subscription instanceof AgencySubscription) {
            return 0;
        }

        return $subscription->getPropertyLimitSnapshot()
            ?? $subscription->getPlan()->getPropertyLimit();
    }
}
