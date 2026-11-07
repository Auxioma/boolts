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

use App\Entity\Property;
use App\Entity\User;
use App\Service\Messagerie\MessagerieManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ouvre (ou reprend) la conversation entre le visiteur connecté et l'agence d'un bien,
 * puis redirige vers la messagerie du visiteur.
 */
final class MessagerieContactController extends AbstractController
{
    #[Route(
        path: [
            'en' => '/public/detail/bien/{slug}/messagerie',
            'fr' => '/fr/public/detail/bien/{slug}/messagerie',
        ],
        name: 'app_public_detail_bien_messagerie',
        methods: ['GET'],
    )]
    #[IsGranted('ROLE_USER')]
    public function __invoke(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Property $property,
        MessagerieManager $messagerieManager,
    ): Response {
        $user = $this->getUser();

        if (
            !$user instanceof User
            || $this->isGranted('ROLE_AGENCE')
            || $this->isGranted('ROLE_ADMIN')
            || null === $property->getUser()
            || $property->getUser() === $user
        ) {
            $this->addFlash('warning', 'La messagerie avec les agences est réservée aux comptes particuliers.');

            return $this->redirectToRoute('app_public_detail_bien', ['slug' => $property->getSlug()]);
        }

        $conversation = $messagerieManager->getOrCreateConversation($property, $user);

        return $this->redirectToRoute('app_dashboard_visiteur_messagerie', [
            'conversation' => $conversation->getId(),
        ]);
    }
}
