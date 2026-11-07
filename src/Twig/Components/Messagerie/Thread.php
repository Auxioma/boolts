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

namespace App\Twig\Components\Messagerie;

use App\Entity\Messagerie\Conversation;
use App\Entity\Messagerie\Message;
use App\Entity\User;
use App\Repository\Messagerie\MessageRepository;
use App\Service\Messagerie\MessagerieManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

/**
 * Fil de discussion en direct (rafraîchi par polling) d'une conversation.
 *
 * Utilisé côté agence et côté visiteur. Les messages reçus sont marqués
 * comme lus à l'ouverture puis à chaque rafraîchissement.
 */
#[AsLiveComponent('Messagerie:Thread')]
final class Thread
{
    use DefaultActionTrait;

    #[LiveProp]
    public Conversation $conversation;

    #[LiveProp(writable: true)]
    public string $content = '';

    /**
     * @var list<string>
     */
    public array $errors = [];

    /**
     * @var list<Message>|null
     */
    private ?array $messages = null;

    public function __construct(
        private readonly Security $security,
        private readonly MessageRepository $messageRepository,
        private readonly MessagerieManager $messagerieManager,
    ) {
    }

    #[PostMount]
    public function onMount(): void
    {
        $this->denyUnlessParticipant();
        $this->markAsRead();
    }

    #[PostHydrate]
    public function onHydrate(): void
    {
        $this->denyUnlessParticipant();
    }

    /**
     * Appelée par le polling : marque les nouveaux messages reçus comme lus.
     */
    #[LiveAction]
    public function refresh(): void
    {
        $this->markAsRead();
    }

    #[LiveAction]
    public function send(Request $request): void
    {
        $image = $request->files->get('image');

        $this->errors = $this->messagerieManager->send(
            conversation: $this->conversation,
            sender: $this->getUser(),
            content: $this->content,
            image: $image instanceof UploadedFile ? $image : null,
        );

        if ([] === $this->errors) {
            $this->content = '';
        }

        $this->messages = null;
    }

    /**
     * @return list<Message>
     */
    public function getMessages(): array
    {
        return $this->messages ??= $this->messageRepository->findForConversation($this->conversation);
    }

    public function getUser(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    public function getInterlocutor(): ?User
    {
        return $this->conversation->getOtherParticipant($this->getUser());
    }

    public function isAgenceView(): bool
    {
        return $this->getUser() === $this->conversation->getAgence();
    }

    private function markAsRead(): void
    {
        $this->messageRepository->markAsRead($this->conversation, $this->getUser());
    }

    private function denyUnlessParticipant(): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User || !$this->conversation->isParticipant($user)) {
            throw new AccessDeniedException('Vous ne participez pas à cette conversation.');
        }
    }
}
