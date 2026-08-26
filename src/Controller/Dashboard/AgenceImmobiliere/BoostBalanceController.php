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

use App\Entity\User;
use App\Repository\Booster\BoosterTransactionRepository;
use App\Service\Billing\AgencyPropertyQuotaCalculator;
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

        $boostBalance = $boosterTransactionRepository->countAvailableBySourceForAgency($user);

        return $this->render('dashboard/agence_immobiliere/_partials/boost_balance_button.html.twig', [
            'boosts_restants' => $boostBalance['total'],
            'boosts_forfait' => $boostBalance['subscription'],
            'boosts_independants' => $boostBalance['independent'],
            'variant' => $variant,
        ]);
    }

    #[Route('/pro/boost-balance/annonce', name: 'agence_immobiliere_boost_balance_annonce', methods: ['GET'])]
    public function annonce(
        AgencyPropertyQuotaCalculator $agencyPropertyQuotaCalculator,
        string $variant = 'dashboard',
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $quota = $agencyPropertyQuotaCalculator->calculate($user);

        return $this->render('dashboard/agence_immobiliere/_partials/boost_balance_annonce.html.twig', [
            'annonces_restantes' => $quota['remaining'],
            'annonces_utilisees' => $quota['used'],
            'limite_annonces' => $quota['limit'],
            'quota_annonces_atteint' => $quota['reached'],
            'variant' => $variant,
        ]);
    }
}
