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

use App\Entity\Document\RequiredDocument;
use App\Entity\Document\UserDocumentRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserDocumentRequest>
 */
final class UserDocumentRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDocumentRequest::class);
    }

    public function findForUserAndRequiredDocument(User $user, RequiredDocument $document): ?UserDocumentRequest
    {
        return $this->findOneBy([
            'user' => $user,
            'requiredDocument' => $document,
        ]);
    }

    public function countSubmittedRequiredDocuments(User $user): int
    {
        return (int) $this->createQueryBuilder('documentRequest')
            ->select('COUNT(DISTINCT documentRequest.id)')
            ->innerJoin('documentRequest.requiredDocument', 'requiredDocument')
            ->innerJoin('documentRequest.submissions', 'submission')
            ->andWhere('documentRequest.user = :user')
            ->andWhere('requiredDocument.enabled = :enabled')
            ->andWhere('requiredDocument.required = :required')
            ->setParameter('user', $user)
            ->setParameter('enabled', true)
            ->setParameter('required', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
