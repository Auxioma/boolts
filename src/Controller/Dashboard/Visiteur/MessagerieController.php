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

namespace App\Controller\Dashboard\Visiteur;

use App\Repository\Messagerie\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class MessagerieController extends AbstractController
{
    #[Route('/dashboard/visiteur/messagerie', name: 'app_dashboard_visiteur_messagerie')]
    public function index(Request $request, ConversationRepository $conversationRepository): Response
    {
        // Les agences ont leur propre messagerie.
        if ($this->isGranted('ROLE_AGENCE')) {
            return $this->redirectToRoute('agence_immobiliere_messagerie');
        }

        /*
         * ?conversation=ID d'un contact direct (sans annonce) : on ouvre l'onglet « Agences ».
         */
        $conversation = $conversationRepository->find($request->query->getInt('conversation'));
        $openAgences = null !== $conversation
            && $conversation->isDirect()
            && $conversation->getVisiteur() === $this->getUser();

        return $this->render('dashboard/visiteur/messagerie/index.html.twig', [
            'active_tab' => $openAgences ? 'agences' : 'annonce',
        ]);
    }
}
