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
use App\Entity\Property;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Biens de l'agence ayant au moins une conversation avec message,
     * avec le nombre de conversations, de messages non lus et la date du dernier message.
     *
     * @return list<array{property: Property, conversations: int, unread: int, lastMessageAt: \DateTimeImmutable}>
     */
    public function findAnnoncesForAgence(User $agence): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select(
                'IDENTITY(c.property) AS propertyId',
                'COUNT(c.id) AS conversations',
                'MAX(c.lastMessageAt) AS lastMessageAt',
            )
            ->andWhere('c.agence = :agence')
            ->andWhere('c.lastMessageAt IS NOT NULL')
            ->setParameter('agence', $agence)
            ->groupBy('c.property')
            ->orderBy('lastMessageAt', 'DESC')
            ->getQuery()
            ->getArrayResult();

        if ([] === $rows) {
            return [];
        }

        $propertyIds = array_map(static fn (array $row): int => (int) $row['propertyId'], $rows);

        /*
         * Non lus par bien : messages envoyés par les visiteurs et pas encore lus.
         */
        $unreadRows = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(c.property) AS propertyId', 'COUNT(m.id) AS unread')
            ->from(Message::class, 'm')
            ->join('m.conversation', 'c')
            ->andWhere('c.agence = :agence')
            ->andWhere('m.sender <> :agence')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('agence', $agence)
            ->groupBy('c.property')
            ->getQuery()
            ->getArrayResult();

        $unreadByProperty = [];
        foreach ($unreadRows as $row) {
            $unreadByProperty[(int) $row['propertyId']] = (int) $row['unread'];
        }

        /** @var list<Property> $properties */
        $properties = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Property::class, 'p')
            ->andWhere('p.id IN (:ids)')
            ->setParameter('ids', $propertyIds)
            ->getQuery()
            ->getResult();

        $propertiesById = [];
        foreach ($properties as $property) {
            $propertiesById[$property->getId()] = $property;
        }

        $annonces = [];
        foreach ($rows as $row) {
            $propertyId = (int) $row['propertyId'];

            if (!isset($propertiesById[$propertyId])) {
                continue;
            }

            $annonces[] = [
                'property' => $propertiesById[$propertyId],
                'conversations' => (int) $row['conversations'],
                'unread' => $unreadByProperty[$propertyId] ?? 0,
                'lastMessageAt' => new \DateTimeImmutable((string) $row['lastMessageAt']),
            ];
        }

        return $annonces;
    }

    /**
     * Conversations d'un bien côté agence (uniquement celles contenant un message).
     *
     * @return list<Conversation>
     */
    public function findForAgenceAndProperty(User $agence, Property $property): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('v')
            ->join('c.visiteur', 'v')
            ->andWhere('c.agence = :agence')
            ->andWhere('c.property = :property')
            ->andWhere('c.lastMessageAt IS NOT NULL')
            ->setParameter('agence', $agence)
            ->setParameter('property', $property)
            ->orderBy('c.lastMessageAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Historique des conversations d'un visiteur, la plus récente en premier.
     *
     * @return list<Conversation>
     */
    public function findForVisiteur(User $visiteur): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('p', 'a')
            ->addSelect('COALESCE(c.lastMessageAt, c.createdAt) AS HIDDEN sortAt')
            ->join('c.property', 'p')
            ->join('c.agence', 'a')
            ->andWhere('c.visiteur = :visiteur')
            ->setParameter('visiteur', $visiteur)
            ->orderBy('sortAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
