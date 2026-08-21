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

namespace App\Service;

use App\Entity\MaintenanceSetting;
use App\Repository\MaintenanceAllowedIpRepository;
use App\Repository\MaintenanceSettingRepository;
use Symfony\Component\HttpFoundation\Request;

class MaintenanceManager
{
    public function __construct(
        private readonly MaintenanceSettingRepository $maintenanceSettingRepository,
        private readonly MaintenanceAllowedIpRepository $maintenanceAllowedIpRepository,
    ) {
    }

    public function getSettings(): ?MaintenanceSetting
    {
        return $this->maintenanceSettingRepository->getSettings();
    }

    public function getActiveSettings(): ?MaintenanceSetting
    {
        $settings = $this->getSettings();

        if (null === $settings) {
            return null;
        }

        if (!$settings->isActiveAt()) {
            return null;
        }

        return $settings;
    }

    public function isClientAllowed(Request $request): bool
    {
        return $this->maintenanceAllowedIpRepository->isAllowed(
            $request->getClientIp()
        );
    }
}
