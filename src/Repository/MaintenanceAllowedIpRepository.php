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
        if ($ipAddress === null || $ipAddress === '') {
            return false;
        }

        $normalizedIp = $this->normalizeIpAddress($ipAddress);

        if ($normalizedIp === null) {
            return false;
        }

        return $this->findOneBy([
            'ipAddress' => $normalizedIp,
            'enabled' => true,
        ]) !== null;
    }

    private function normalizeIpAddress(string $ipAddress): ?string
    {
        $binary = @inet_pton(trim($ipAddress));

        if ($binary === false) {
            return null;
        }

        $normalized = inet_ntop($binary);

        return $normalized !== false ? $normalized : null;
    }
}