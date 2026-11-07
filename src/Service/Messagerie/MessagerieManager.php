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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Création des conversations et envoi des messages de la messagerie instantanée.
 */
final class MessagerieManager
{
    public const int MAX_CONTENT_LENGTH = 5000;

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
     * Enregistre un message (texte et/ou image).
     *
     * @return list<string> erreurs de validation (vide si le message est envoyé)
     */
    public function send(Conversation $conversation, User $sender, ?string $content, ?UploadedFile $image = null): array
    {
        if (!$conversation->isParticipant($sender)) {
            throw new \LogicException('L’expéditeur ne participe pas à cette conversation.');
        }

        $content = null !== $content ? trim($content) : null;
        $content = '' === $content ? null : $content;

        if (null === $content && null === $image) {
            return ['Le message est vide.'];
        }

        if (null !== $content && mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            return [\sprintf('Le message ne doit pas dépasser %d caractères.', self::MAX_CONTENT_LENGTH)];
        }

        if (null !== $image) {
            $violations = $this->validator->validate($image, new Image(
                maxSize: '5M',
                mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                mimeTypesMessage: 'Formats acceptés : JPG, PNG, WEBP ou GIF.',
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

        if (null !== $image) {
            $message->setImageFile($image);
        }

        // setConversation plutôt que addMessage : évite de charger tout l'historique.
        $message->setConversation($conversation);
        $conversation
            ->setLastMessageAt(new \DateTimeImmutable())
            ->setLastMessagePreview(null !== $content ? mb_substr($content, 0, 255) : '📷 Image');

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return [];
    }
}
