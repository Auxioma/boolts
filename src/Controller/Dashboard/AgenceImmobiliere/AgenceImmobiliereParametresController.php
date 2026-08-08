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
use App\Form\Dashboard\AgenceImmobiliere\ProfileAgenceType;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/parametres', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / AgenceImmobiliereParametresController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereParametresController extends AbstractController
{
    #[Route('/', name: 'parametres', methods: ['GET', 'POST'])]
    /**
     * Handles the index controller action.
     */
    public function index(
        AgencyPaymentMethodRepository $paymentMethodRepository,
        #[Autowire('%stripe.public_key%')]
        string $stripePublicKey,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $form = $this->createForm(ProfileAgenceType::class, $user);

        $billingProfile = $user->getBillingProfile();

        $paymentMethods = null !== $billingProfile
            ? $paymentMethodRepository->findActiveByBillingProfile($billingProfile)
            : [];

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_parametres/index.html.twig',
            [
                'form' => $form->createView(),
                'stripe_public_key' => $stripePublicKey,
                'payment_methods' => $paymentMethods,
            ]
        );
    }
}
