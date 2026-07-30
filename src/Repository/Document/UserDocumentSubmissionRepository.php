<?php

namespace App\Repository\Document;

use App\Entity\Document\UserDocumentSubmission;
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
}
