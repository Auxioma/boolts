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

use App\Entity\AgencyNotification;
use App\Entity\User;
use App\Repository\AgencyNotificationRepository;
use App\Security\Voter\AgencyDocumentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/notifications', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
#[IsGranted(
    AgencyDocumentVoter::ACCESS_RESTRICTED_DASHBOARD,
    message: 'Vos documents doivent être validés pour accéder à cette page.',
)]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / AgenceImmobiliereNotificationsController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereNotificationsController extends AbstractController
{
    /**
     * Nombre maximum de notifications affichées sur la page.
     */
    private const FEED_LIMIT = 100;

    #[Route('/dashboard/agence/immobiliere/agence/immobiliere/notifications', name: 'notifications', methods: ['GET'])]
    /**
     * Handles the index controller action.
     */
    public function index(AgencyNotificationRepository $notificationRepository): Response
    {
        $agency = $this->getUser();

        if (!$agency instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('dashboard/agence_immobiliere/agence_immobiliere_notifications/index.html.twig', [
            'controller_name' => 'AgenceImmobiliereNotificationsController',
            'notifications' => $notificationRepository->findLatestForAgency($agency, self::FEED_LIMIT),
            'unreadCount' => $notificationRepository->countUnreadForAgency($agency),
        ]);
    }

    #[Route('/{id<\d+>}/lue', name: 'notifications_mark_read', methods: ['POST'])]
    /**
     * Passe une notification de l'agence courante en « lue » (appel AJAX
     * déclenché au survol de la carte).
     */
    public function markAsRead(
        AgencyNotification $notification,
        Request $request,
        AgencyNotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $agency = $this->getUser();

        if (!$agency instanceof User || $notification->getAgency()->getId() !== $agency->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('agency_notification_read', (string) $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 403);
        }

        if (!$notification->isRead()) {
            $notification->markAsRead();
            $entityManager->flush();
        }

        return $this->json([
            'success' => true,
            'unreadCount' => $notificationRepository->countUnreadForAgency($agency),
        ]);
    }
}
