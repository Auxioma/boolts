<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Repository;

use App\Entity\AgencyNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgencyNotification>
 */
final class AgencyNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgencyNotification::class);
    }

    public function save(AgencyNotification $notification, bool $flush = false): void
    {
        $this->getEntityManager()->persist($notification);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AgencyNotification $notification, bool $flush = false): void
    {
        $this->getEntityManager()->remove($notification);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return list<AgencyNotification>
     */
    public function findLatestForAgency(User $agency, int $limit = 20): array
    {
        return $this->createQueryBuilder('notification')
            ->where('notification.agency = :agency')
            ->setParameter('agency', $agency)
            ->orderBy('notification.date', 'DESC')
            ->addOrderBy('notification.id', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AgencyNotification>
     */
    public function findUnreadForAgency(User $agency, int $limit = 50): array
    {
        return $this->createQueryBuilder('notification')
            ->where('notification.agency = :agency')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('agency', $agency)
            ->orderBy('notification.date', 'DESC')
            ->addOrderBy('notification.id', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();
    }

    public function countUnreadForAgency(User $agency): int
    {
        return (int) $this->createQueryBuilder('notification')
            ->select('COUNT(notification.id)')
            ->where('notification.agency = :agency')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('agency', $agency)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Marque toutes les notifications non lues d'une agence comme lues.
     *
     * @return int Nombre de notifications mises à jour
     */
    public function markAllAsReadForAgency(User $agency, ?\DateTimeImmutable $readAt = null): int
    {
        return (int) $this->createQueryBuilder('notification')
            ->update()
            ->set('notification.readAt', ':readAt')
            ->where('notification.agency = :agency')
            ->andWhere('notification.readAt IS NULL')
            ->setParameter('readAt', $readAt ?? new \DateTimeImmutable())
            ->setParameter('agency', $agency)
            ->getQuery()
            ->execute();
    }
}
