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

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Suppression définitive du compte visiteur (onglet « Connexion et sécurité »).
 * Réservé aux visiteurs : les agences et les admins ne passent pas par ici.
 */
#[IsGranted('ROLE_USER')]
final class SupprimerCompteController extends AbstractController
{
    #[Route('/dashboard/visiteur/compte/supprimer', name: 'app_dashboard_visiteur_compte_supprimer', methods: ['POST'])]
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User || $this->isGranted('ROLE_AGENCE') || $this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('visiteur_delete_account', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Un problème est survenu. Réessayez.');

            return $this->redirectToRoute('app_dashboard_visiteur_parametres', ['_fragment' => 'cat2']);
        }

        // Les demandes de réinitialisation de mot de passe n'ont pas de ON DELETE CASCADE.
        $entityManager->createQueryBuilder()
            ->delete(ResetPasswordRequest::class, 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // Déconnexion avant la suppression (favoris, conversations et messages partent en cascade).
        $security->logout(false);

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_home');
    }
}
