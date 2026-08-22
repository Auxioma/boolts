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

namespace App\Tests\Service;

use App\Entity\Pays;
use App\Entity\HoraireOuverture;
use App\Entity\User;
use App\Service\Authentification\AgencyRegistrationProgress;
use PHPUnit\Framework\TestCase;

final class AgencyRegistrationProgressTest extends TestCase
{
    private AgencyRegistrationProgress $progress;

    protected function setUp(): void
    {
        $this->progress = new AgencyRegistrationProgress();
    }

    public function testEmailOnlyAgencyStartsAtProfileStepWhenNoStoredStepExists(): void
    {
        $user = (new User())
            ->setEmail('agence@example.test')
            ->setRoles(['ROLE_AGENCE'])
        ;

        self::assertTrue($this->progress->isIncompleteAgencyRegistration($user));
        self::assertSame(AgencyRegistrationProgress::STEP_PROFILE, $this->progress->currentStep($user));
        self::assertSame('app_professionnelle_step_trois', $this->progress->routeForCurrentStep($user));
    }

    public function testStoredStepWinsForOptionalRegistrationScreens(): void
    {
        $user = $this->createAgencyWithRequiredAddressData()
            ->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_OPENING_HOURS)
        ;

        self::assertTrue($this->progress->isIncompleteAgencyRegistration($user));
        self::assertSame(AgencyRegistrationProgress::STEP_OPENING_HOURS, $this->progress->currentStep($user));
        self::assertSame('app_professionnelle_step_six', $this->progress->routeForCurrentStep($user));
    }

    public function testGoogleAgencyWithoutPasswordCanResumeFromStoredAddressStep(): void
    {
        $user = (new User())
            ->setEmail('google-agence@example.test')
            ->setRoles(['ROLE_AGENCE'])
            ->setIsVerified(true)
            ->setNom('Martin')
            ->setPrenom('Claire')
            ->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_ADDRESS)
        ;

        self::assertTrue($this->progress->isIncompleteAgencyRegistration($user));
        self::assertSame(AgencyRegistrationProgress::STEP_ADDRESS, $this->progress->currentStep($user));
    }

    public function testCompletedGoogleAgencyWithoutPasswordDoesNotResumeRegistration(): void
    {
        $user = $this->createAgencyWithRequiredAddressData()
            ->setPassword(null)
            ->setIsVerified(true)
            ->setAgencyRegistrationStep(null)
        ;
        $user->addHoraireOuverture((new HoraireOuverture())->setJour('lundi'));

        self::assertFalse($this->progress->isIncompleteAgencyRegistration($user));
    }

    public function testCompletedAgencyDoesNotResumeRegistration(): void
    {
        $user = $this->createAgencyWithRequiredAddressData()
            ->setIsVerified(true)
            ->setAgencyRegistrationStep(null)
        ;

        self::assertFalse($this->progress->isIncompleteAgencyRegistration($user));
    }

    public function testVisitorAccountDoesNotResumeAgencyRegistration(): void
    {
        $user = (new User())
            ->setEmail('visiteur@example.test')
            ->setRoles(['ROLE_USER'])
        ;

        self::assertFalse($this->progress->isIncompleteAgencyRegistration($user));
    }

    private function createAgencyWithRequiredAddressData(): User
    {
        return (new User())
            ->setEmail('agence@example.test')
            ->setRoles(['ROLE_AGENCE'])
            ->setNom('Martin')
            ->setPrenom('Claire')
            ->setPassword('hashed-password')
            ->setEntreprise('Agence Exemple')
            ->setAdresse('10 rue Exemple')
            ->setCodePostal('75001')
            ->setVille('Paris')
            ->setPays(new Pays())
        ;
    }
}
