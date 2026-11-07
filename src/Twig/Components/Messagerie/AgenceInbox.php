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
use App\Entity\Property;
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
 * Messagerie agence, onglet « Annonce ».
 *
 * Étape 1 : biens ayant reçu des messages (nb de conversations + non lus).
 * Étape 2 : visiteurs ayant écrit sur le bien (pastille de non lus) + fil sélectionné.
 */
#[AsLiveComponent('Messagerie:AgenceInbox')]
final class AgenceInbox
{
    use DefaultActionTrait;

    public const string FILTER_ALL = 'all';
    public const string FILTER_UNREAD = 'unread';

    #[LiveProp(writable: true, url: true)]
    public ?int $bien = null;

    #[LiveProp(writable: true, url: true)]
    public ?int $conversation = null;

    #[LiveProp(writable: true)]
    public string $filter = self::FILTER_ALL;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $conversationFilter = self::FILTER_ALL;

    #[LiveProp(writable: true)]
    public string $conversationQuery = '';

    /**
     * @var list<array{property: Property, conversations: int, unread: int, lastMessageAt: \DateTimeImmutable}>|null
     */
    private ?array $annonces = null;

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
    public function openBien(#[LiveArg] int $id): void
    {
        $this->bien = $id;
        $this->conversation = null;
        $this->conversationFilter = self::FILTER_ALL;
        $this->conversationQuery = '';
    }

    #[LiveAction]
    public function back(): void
    {
        $this->bien = null;
        $this->conversation = null;
    }

    #[LiveAction]
    public function openConversation(#[LiveArg] int $id): void
    {
        $this->conversation = $id;
    }

    /**
     * @return list<array{property: Property, conversations: int, unread: int, lastMessageAt: \DateTimeImmutable}>
     */
    public function getAllAnnonces(): array
    {
        return $this->annonces ??= $this->conversationRepository->findAnnoncesForAgence($this->getAgence());
    }

    /**
     * @return list<array{property: Property, conversations: int, unread: int, lastMessageAt: \DateTimeImmutable}>
     */
    public function getAnnonces(): array
    {
        $query = mb_strtolower(trim($this->query));

        return array_values(array_filter(
            $this->getAllAnnonces(),
            function (array $annonce) use ($query): bool {
                if (self::FILTER_UNREAD === $this->filter && 0 === $annonce['unread']) {
                    return false;
                }

                if ('' === $query) {
                    return true;
                }

                $property = $annonce['property'];
                $haystack = mb_strtolower(implode(' ', [
                    $property->getTitreDuLogement(),
                    $property->getVille(),
                    $property->getReferenceInterne(),
                ]));

                return str_contains($haystack, $query);
            },
        ));
    }

    /**
     * @return array{property: Property, conversations: int, unread: int, lastMessageAt: \DateTimeImmutable}|null
     */
    public function getSelectedAnnonce(): ?array
    {
        if (null === $this->bien) {
            return null;
        }

        foreach ($this->getAllAnnonces() as $annonce) {
            if ($annonce['property']->getId() === $this->bien) {
                return $annonce;
            }
        }

        return null;
    }

    /**
     * @return list<Conversation>
     */
    public function getAllConversations(): array
    {
        $annonce = $this->getSelectedAnnonce();

        if (null === $annonce) {
            return [];
        }

        return $this->conversations ??= $this->conversationRepository->findForAgenceAndProperty(
            $this->getAgence(),
            $annonce['property'],
        );
    }

    /**
     * @return list<Conversation>
     */
    public function getConversations(): array
    {
        $query = mb_strtolower(trim($this->conversationQuery));
        $unread = $this->getUnreadByConversation();

        return array_values(array_filter(
            $this->getAllConversations(),
            function (Conversation $conversation) use ($query, $unread): bool {
                if (self::FILTER_UNREAD === $this->conversationFilter && 0 === ($unread[$conversation->getId()] ?? 0)) {
                    return false;
                }

                if ('' === $query) {
                    return true;
                }

                $visiteur = $conversation->getVisiteur();
                $haystack = mb_strtolower(implode(' ', [
                    $visiteur?->getPrenom(),
                    $visiteur?->getNom(),
                    $visiteur?->getEmail(),
                ]));

                return str_contains($haystack, $query);
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
     * Conversation ouverte : celle demandée si elle appartient au bien, sinon la plus récente.
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
