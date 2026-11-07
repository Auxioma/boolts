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

namespace App\Repository\Messagerie;

use App\Entity\Messagerie\Conversation;
use App\Entity\Messagerie\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Messages d'une conversation, du plus ancien au plus récent.
     *
     * @return list<Message>
     */
    public function findForConversation(Conversation $conversation): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('s')
            ->join('m.sender', 's')
            ->andWhere('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Marque comme lus les messages reçus par $reader dans la conversation.
     */
    public function markAsRead(Conversation $conversation, User $reader): int
    {
        return $this->createQueryBuilder('m')
            ->update()
            ->set('m.readAt', ':now')
            ->andWhere('m.conversation = :conversation')
            ->andWhere('m.sender <> :reader')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('conversation', $conversation)
            ->setParameter('reader', $reader)
            ->getQuery()
            ->execute();
    }

    /**
     * Nombre de messages non lus par $reader, indexé par id de conversation.
     *
     * @param list<Conversation> $conversations
     *
     * @return array<int, int>
     */
    public function countUnreadByConversation(array $conversations, User $reader): array
    {
        if ([] === $conversations) {
            return [];
        }

        $rows = $this->createQueryBuilder('m')
            ->select('IDENTITY(m.conversation) AS conversationId', 'COUNT(m.id) AS unread')
            ->andWhere('m.conversation IN (:conversations)')
            ->andWhere('m.sender <> :reader')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('conversations', $conversations)
            ->setParameter('reader', $reader)
            ->groupBy('m.conversation')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['conversationId']] = (int) $row['unread'];
        }

        return $counts;
    }
}
