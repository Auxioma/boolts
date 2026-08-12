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

namespace App\Repository\Document;

use App\Entity\Enum\DocumentSubmissionStatus;
use App\Entity\Document\UserDocumentSubmission;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserDocumentSubmission>
 */
final class UserDocumentSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDocumentSubmission::class);
    }

    /**
     * @param list<DocumentSubmissionStatus> $statuses
     */
    public function hasLatestSubmissionWithStatus(User $user, array $statuses): bool
    {
        if ([] === $statuses) {
            return false;
        }

        $statusValues = array_map(
            static fn (DocumentSubmissionStatus $status): string => $status->value,
            $statuses,
        );

        return 0 < (int) $this->createQueryBuilder('submission')
            ->select('COUNT(submission.id)')
            ->innerJoin('submission.documentRequest', 'documentRequest')
            ->andWhere('documentRequest.user = :user')
            ->andWhere('submission.status IN (:statuses)')
            ->andWhere(sprintf(
                'NOT EXISTS (
                    SELECT newerSubmission.id
                    FROM %s newerSubmission
                    WHERE newerSubmission.documentRequest = documentRequest
                    AND newerSubmission.attemptNumber > submission.attemptNumber
                )',
                UserDocumentSubmission::class,
            ))
            ->setParameter('user', $user)
            ->setParameter('statuses', $statusValues)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
