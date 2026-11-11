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

namespace App\Service\Messagerie;

use App\Entity\Messagerie\Conversation;
use App\Entity\Messagerie\Message;
use App\Entity\Property;
use App\Entity\User;
use App\Repository\Messagerie\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Création des conversations et envoi des messages de la messagerie instantanée.
 */
final class MessagerieManager
{
    public const int MAX_CONTENT_LENGTH = 5000;

    public const array IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** PDF, Word, Excel, OpenDocument et texte. */
    public const array DOCUMENT_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'text/plain',
    ];

    /** Valeur de l'attribut accept du champ fichier du fil de discussion. */
    public const string ACCEPT = 'image/jpeg,image/png,image/webp,image/gif,.pdf,.doc,.docx,.xls,.xlsx,.odt,.ods,.txt';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConversationRepository $conversationRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Retourne la conversation (bien, visiteur), en la créant si besoin.
     */
    public function getOrCreateConversation(Property $property, User $visiteur): Conversation
    {
        $agence = $property->getUser();

        if (null === $agence) {
            throw new \LogicException('Aucune agence n’est associée à ce bien.');
        }

        $conversation = $this->conversationRepository->findOneBy([
            'property' => $property,
            'visiteur' => $visiteur,
        ]);

        if (null !== $conversation) {
            return $conversation;
        }

        $conversation = (new Conversation())
            ->setProperty($property)
            ->setAgence($agence)
            ->setVisiteur($visiteur);

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }

    /**
     * Retourne le contact direct (agence, visiteur) sans annonce, en le créant si besoin.
     */
    public function getOrCreateDirectConversation(User $agence, User $visiteur): Conversation
    {
        $conversation = $this->conversationRepository->findOneBy([
            'property' => null,
            'agence' => $agence,
            'visiteur' => $visiteur,
        ]);

        if (null !== $conversation) {
            return $conversation;
        }

        $conversation = (new Conversation())
            ->setAgence($agence)
            ->setVisiteur($visiteur);

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

        return $conversation;
    }

    /**
     * Enregistre un message (texte et/ou pièce jointe).
     *
     * La pièce jointe est enregistrée comme image si c'en est une (affichée dans le fil),
     * sinon comme document (PDF, Word, Excel…, téléchargeable par les participants).
     *
     * @return list<string> erreurs de validation (vide si le message est envoyé)
     */
    public function send(Conversation $conversation, User $sender, ?string $content, ?UploadedFile $attachment = null): array
    {
        if (!$conversation->isParticipant($sender)) {
            throw new \LogicException('L’expéditeur ne participe pas à cette conversation.');
        }

        $content = null !== $content ? trim($content) : null;
        $content = '' === $content ? null : $content;

        if (null === $content && null === $attachment) {
            return ['Le message est vide.'];
        }

        if (null !== $content && mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            return [\sprintf('Le message ne doit pas dépasser %d caractères.', self::MAX_CONTENT_LENGTH)];
        }

        $isImage = null !== $attachment && \in_array($attachment->getMimeType(), self::IMAGE_MIME_TYPES, true);

        if (null !== $attachment) {
            $violations = $this->validator->validate($attachment, $isImage
                ? new Image(
                    maxSize: '5M',
                    mimeTypes: self::IMAGE_MIME_TYPES,
                    mimeTypesMessage: 'Formats acceptés : JPG, PNG, WEBP ou GIF.',
                )
                : new FileConstraint(
                    maxSize: '10M',
                    mimeTypes: self::DOCUMENT_MIME_TYPES,
                    mimeTypesMessage: 'Formats acceptés : images (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument ou texte.',
                ));

            if (\count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = (string) $violation->getMessage();
                }

                return $errors;
            }
        }

        $message = (new Message())
            ->setSender($sender)
            ->setContent($content);

        if (null !== $attachment && $isImage) {
            $message->setImageFile($attachment);
        } elseif (null !== $attachment) {
            $message->setDocumentFile($attachment);
        }

        $preview = match (true) {
            null !== $content => mb_substr($content, 0, 255),
            $isImage => '📷 Image',
            default => mb_substr('📎 '.$attachment?->getClientOriginalName(), 0, 255),
        };

        // setConversation plutôt que addMessage : évite de charger tout l'historique.
        $message->setConversation($conversation);
        $conversation
            ->setLastMessageAt(new \DateTimeImmutable())
            ->setLastMessagePreview($preview);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return [];
    }
}
