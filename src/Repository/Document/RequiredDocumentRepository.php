<?php

namespace App\Repository\Document;

use App\Entity\Document\RequiredDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequiredDocument>
 */
final class RequiredDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequiredDocument::class);
    }
}
