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

namespace App\Repository;

use App\Entity\Billing\Invoice;
use App\Entity\Billing\Payment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private const ROLE_AGENCY = 'ROLE_AGENCE';
    private const ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return list<User>
     */
    public function findAgenciesForDocumentDeletionWarning(
        \DateTimeImmutable $createdAtOrBefore,
        \DateTimeImmutable $createdAtAfter,
        int $daysBeforeDeletion,
    ): array {
        $warningField = match ($daysBeforeDeletion) {
            30 => 'documentDeletionWarningThirtyDaysSentAt',
            15 => 'documentDeletionWarningFifteenDaysSentAt',
            5 => 'documentDeletionWarningFiveDaysSentAt',
            default => throw new \InvalidArgumentException(\sprintf('Unsupported warning delay "%d".', $daysBeforeDeletion)),
        };

        return $this->baseDocumentDeletionAgencyQueryBuilder()
            ->andWhere('u.createdAt <= :createdAtOrBefore')
            ->andWhere('u.createdAt > :createdAtAfter')
            ->andWhere(\sprintf('u.%s IS NULL', $warningField))
            ->setParameter('createdAtOrBefore', $createdAtOrBefore)
            ->setParameter('createdAtAfter', $createdAtAfter)
            ->orderBy('u.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<User>
     */
    public function findAgenciesExpiredForMissingDocuments(
        \DateTimeImmutable $createdAtOrBefore,
    ): array {
        return $this->baseDocumentDeletionAgencyQueryBuilder()
            ->andWhere('u.createdAt <= :createdAtOrBefore')
            ->setParameter('createdAtOrBefore', $createdAtOrBefore)
            ->orderBy('u.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function baseDocumentDeletionAgencyQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $paymentSubquery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('1')
            ->from(Payment::class, 'payment')
            ->innerJoin('payment.billingProfile', 'paymentBillingProfile')
            ->andWhere('payment.agency = u OR paymentBillingProfile.agency = u');
        $invoiceSubquery = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('1')
            ->from(Invoice::class, 'invoice')
            ->innerJoin('invoice.billingProfile', 'invoiceBillingProfile')
            ->andWhere('invoice.agency = u OR invoiceBillingProfile.agency = u');

        return $queryBuilder
            ->andWhere('u.deletedAt IS NULL')
            ->andWhere('u.createdAt IS NOT NULL')
            ->andWhere('u.roles LIKE :agencyRole')
            ->andWhere('u.roles NOT LIKE :adminRole')
            ->andWhere($queryBuilder->expr()->not(
                $queryBuilder->expr()->exists($paymentSubquery->getDQL()),
            ))
            ->andWhere($queryBuilder->expr()->not(
                $queryBuilder->expr()->exists($invoiceSubquery->getDQL()),
            ))
            ->setParameter('agencyRole', '%"'.self::ROLE_AGENCY.'"%')
            ->setParameter('adminRole', '%"'.self::ROLE_ADMIN.'"%');
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
