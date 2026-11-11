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
use App\Entity\User;
use App\Repository\Messagerie\ConversationRepository;
use App\Repository\Messagerie\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Messagerie visiteur : historique des conversations avec les agences + fil sélectionné.
 */
#[AsLiveComponent('Messagerie:VisiteurInbox')]
final class VisiteurInbox
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?int $conversation = null;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $filter = 'all';

    /**
     * @var list<Conversation>|null
     */
    private ?array $conversations = null;

    /**
     * @var array<int, int>|null
     */
    private ?array $unreadByConversation = null;

    public function __construct(
        private readonly Security $security,
        private readonly ConversationRepository $conversationRepository,
        private readonly MessageRepository $messageRepository,
    ) {
    }

    #[LiveAction]
    public function openConversation(#[LiveArg] int $id): void
    {
        $this->conversation = $id;
    }

    /**
     * Toutes les conversations du visiteur.
     *
     * @return list<Conversation>
     */
    public function getAllConversations(): array
    {
        return $this->conversations ??= $this->conversationRepository->findForVisiteur($this->getVisiteur());
    }

    /**
     * Conversations après recherche (agence ou annonce) et filtre « Non lues ».
     *
     * @return list<Conversation>
     */
    public function getConversations(): array
    {
        $query = mb_strtolower(mb_trim($this->query));
        $unread = $this->getUnreadByConversation();

        return array_values(array_filter(
            $this->getAllConversations(),
            fn (Conversation $conversation): bool => ('unread' !== $this->filter || ($unread[$conversation->getId()] ?? 0) > 0)
                && ('' === $query || str_contains(mb_strtolower($this->searchText($conversation)), $query)),
        ));
    }

    /**
     * @return array<int, int>
     */
    public function getUnreadByConversation(): array
    {
        return $this->unreadByConversation ??= $this->messageRepository->countUnreadByConversation(
            $this->getAllConversations(),
            $this->getVisiteur(),
        );
    }

    public function getSelectedConversation(): ?Conversation
    {
        $conversations = $this->getAllConversations();

        foreach ($conversations as $conversation) {
            if ($conversation->getId() === $this->conversation) {
                return $conversation;
            }
        }

        return $conversations[0] ?? null;
    }

    private function searchText(Conversation $conversation): string
    {
        $agence = $conversation->getAgence();
        $property = $conversation->getProperty();

        return implode(' ', [
            $agence?->getEntreprise(),
            $agence?->getPrenom(),
            $agence?->getNom(),
            $property?->getTitreDuLogement(),
        ]);
    }

    private function getVisiteur(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
