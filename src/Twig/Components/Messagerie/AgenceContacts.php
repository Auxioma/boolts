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
use App\Security\Voter\AgencyDocumentVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Messagerie agence, onglet « Contact » : particuliers ayant écrit à l'agence
 * depuis sa page (contact direct, sans annonce) + fil sélectionné.
 */
#[AsLiveComponent('Messagerie:AgenceContacts')]
final class AgenceContacts
{
    use DefaultActionTrait;

    public const string FILTER_ALL = 'all';
    public const string FILTER_UNREAD = 'unread';

    #[LiveProp(writable: true)]
    public ?int $conversation = null;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $filter = self::FILTER_ALL;

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
     * @return list<Conversation>
     */
    public function getAllConversations(): array
    {
        return $this->conversations ??= $this->conversationRepository->findDirectForAgence($this->getAgence());
    }

    /**
     * Contacts après recherche (nom / e-mail du particulier) et filtre « Non lues ».
     *
     * @return list<Conversation>
     */
    public function getConversations(): array
    {
        $query = mb_strtolower(mb_trim($this->query));
        $unread = $this->getUnreadByConversation();

        return array_values(array_filter(
            $this->getAllConversations(),
            function (Conversation $conversation) use ($query, $unread): bool {
                if (self::FILTER_UNREAD === $this->filter && 0 === ($unread[$conversation->getId()] ?? 0)) {
                    return false;
                }

                if ('' === $query) {
                    return true;
                }

                $visiteur = $conversation->getVisiteur();

                return str_contains(mb_strtolower(implode(' ', [
                    $visiteur?->getPrenom(),
                    $visiteur?->getNom(),
                    $visiteur?->getEmail(),
                ])), $query);
            },
        ));
    }

    /**
     * @return array<int, int>
     */
    public function getUnreadByConversation(): array
    {
        return $this->unreadByConversation ??= $this->messageRepository->countUnreadByConversation(
            $this->getAllConversations(),
            $this->getAgence(),
        );
    }

    /**
     * Conversation ouverte : celle demandée, sinon la plus récente.
     */
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

    private function getAgence(): User
    {
        $user = $this->security->getUser();

        if (
            !$user instanceof User
            || !$this->security->isGranted('ROLE_AGENCE')
            || !$this->security->isGranted(AgencyDocumentVoter::ACCESS_RESTRICTED_DASHBOARD)
        ) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
