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

use App\Entity\MaintenanceAllowedIp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MaintenanceAllowedIp>
 */
class MaintenanceAllowedIpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceAllowedIp::class);
    }

    public function isAllowed(?string $ipAddress): bool
    {
        if (null === $ipAddress || '' === $ipAddress) {
            return false;
        }

        $normalizedIp = $this->normalizeIpAddress($ipAddress);

        if (null === $normalizedIp) {
            return false;
        }

        return null !== $this->findOneBy([
            'ipAddress' => $normalizedIp,
            'enabled' => true,
        ]);
    }

    private function normalizeIpAddress(string $ipAddress): ?string
    {
        $binary = @inet_pton(mb_trim($ipAddress));

        if (false === $binary) {
            return null;
        }

        $normalized = inet_ntop($binary);

        return false !== $normalized ? $normalized : null;
    }
}
