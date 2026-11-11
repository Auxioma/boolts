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

namespace App\Controller\Messagerie;

use App\Entity\Messagerie\Message;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * Téléchargement d'un document joint à un message : réservé aux deux participants
 * de la conversation (les documents sont stockés hors du dossier public).
 */
final class MessagerieDocumentController extends AbstractController
{
    #[Route('/messagerie/document/{id}', name: 'app_messagerie_document', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function __invoke(Message $message, StorageInterface $storage): BinaryFileResponse
    {
        $user = $this->getUser();
        $conversation = $message->getConversation();

        if (!$user instanceof User || null === $conversation || !$conversation->isParticipant($user)) {
            throw $this->createAccessDeniedException();
        }

        $path = null !== $message->getDocumentName() ? $storage->resolvePath($message, 'documentFile') : null;

        if (null === $path || !is_file($path)) {
            throw $this->createNotFoundException('Document introuvable.');
        }

        $response = new BinaryFileResponse($path);
        $response->setPrivate();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $message->getDocumentOriginalName() ?? $message->getDocumentName(),
            (string) $message->getDocumentName(),
        );

        return $response;
    }
}
