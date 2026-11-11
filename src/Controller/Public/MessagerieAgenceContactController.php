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

namespace App\Controller\Public;

use App\Entity\User;
use App\Service\Messagerie\MessagerieManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Bouton « Envoie message agence » de la page agence : ouvre (ou reprend) le contact
 * direct entre le visiteur connecté et l'agence, puis redirige vers l'onglet « Agences »
 * de la messagerie du visiteur.
 */
final class MessagerieAgenceContactController extends AbstractController
{
    #[Route(
        path: [
            'en' => '/agency/{slug}/messagerie',
            'fr' => '/fr/agency/{slug}/messagerie',
        ],
        name: 'app_public_detail_agence_messagerie',
        methods: ['GET'],
    )]
    #[IsGranted('ROLE_USER')]
    public function __invoke(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        User $agence,
        MessagerieManager $messagerieManager,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || !\in_array('ROLE_AGENCE', $agence->getRoles(), true)
            || $this->isGranted('ROLE_AGENCE')
            || $this->isGranted('ROLE_ADMIN')
            || $agence === $user
        ) {
            $this->addFlash('warning', 'La messagerie avec les agences est réservée aux comptes particuliers.');

            return $this->redirectToRoute('app_public_detail_agence', ['slug' => $agence->getSlug()]);
        }

        $conversation = $messagerieManager->getOrCreateDirectConversation($agence, $user);

        return $this->redirectToRoute('app_dashboard_visiteur_messagerie', [
            'conversation' => $conversation->getId(),
        ]);
    }
}
