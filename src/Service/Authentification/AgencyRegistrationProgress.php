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

namespace App\Service\Authentification;

use App\Entity\User;

final readonly class AgencyRegistrationProgress
{
    public const STEP_CODE = 'code';
    public const STEP_PROFILE = 'step3';
    public const STEP_ADDRESS = 'step4';
    public const STEP_PRESENTATION = 'step5';
    public const STEP_OPENING_HOURS = 'step6';

    private const ROLE_AGENCY = 'ROLE_AGENCE';
    private const ROLE_ADMIN = 'ROLE_ADMIN';

    private const ROUTES = [
        self::STEP_CODE => 'app_professionnelle_otp',
        self::STEP_PROFILE => 'app_professionnelle_step_trois',
        self::STEP_ADDRESS => 'app_professionnelle_step_quatre',
        self::STEP_PRESENTATION => 'app_professionnelle_step_cinq',
        self::STEP_OPENING_HOURS => 'app_professionnelle_step_six',
    ];

    public function isIncompleteAgencyRegistration(User $user): bool
    {
        return $this->isAgency($user)
            && !$this->isAdmin($user)
            && !$user->isDeleted()
            && !$this->isAgencyRegistrationComplete($user);
    }

    public function isAgencyRegistrationComplete(User $user): bool
    {
        if (!$this->isAgency($user) || !$user->isVerified()) {
            return false;
        }

        $storedStep = $user->getAgencyRegistrationStep();

        if (null !== $storedStep && isset(self::ROUTES[$storedStep])) {
            return false;
        }

        if (self::hasText($user->getPassword())) {
            return true;
        }

        return $this->hasRequiredAddressData($user) && !$user->getHoraireOuvertures()->isEmpty();
    }

    public function currentStep(User $user): string
    {
        $storedStep = $user->getAgencyRegistrationStep();

        if (null !== $storedStep && isset(self::ROUTES[$storedStep])) {
            return $storedStep;
        }

        return $this->inferCurrentStep($user);
    }

    public function routeForCurrentStep(User $user): string
    {
        return $this->routeForStep($this->currentStep($user));
    }

    public function routeForStep(string $step): string
    {
        if (!isset(self::ROUTES[$step])) {
            throw new \InvalidArgumentException(\sprintf('Étape d’inscription agence inconnue : %s.', $step));
        }

        return self::ROUTES[$step];
    }

    private function inferCurrentStep(User $user): string
    {
        if (!self::hasText($user->getPassword()) && (!$user->isVerified() || !self::hasText($user->getNom()) || !self::hasText($user->getPrenom()))) {
            return self::STEP_PROFILE;
        }

        if (self::hasText($user->getPassword()) && (!self::hasText($user->getNom()) || !self::hasText($user->getPrenom()))) {
            return self::STEP_PROFILE;
        }

        if (!$this->hasRequiredAddressData($user)) {
            return self::STEP_ADDRESS;
        }

        return self::STEP_PRESENTATION;
    }

    private function hasRequiredAddressData(User $user): bool
    {
        return self::hasText($user->getEntreprise())
            && self::hasText($user->getAdresse())
            && self::hasText($user->getCodePostal())
            && self::hasText($user->getVille())
            && null !== $user->getPays();
    }

    private function isAgency(User $user): bool
    {
        return \in_array(self::ROLE_AGENCY, $user->getRoles(), true);
    }

    private function isAdmin(User $user): bool
    {
        return \in_array(self::ROLE_ADMIN, $user->getRoles(), true);
    }

    private static function hasText(?string $value): bool
    {
        return null !== $value && '' !== mb_trim($value);
    }
}
