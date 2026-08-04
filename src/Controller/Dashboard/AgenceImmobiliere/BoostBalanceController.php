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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_AGENCE')]
final class BoostBalanceController extends AbstractController
{
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
}
