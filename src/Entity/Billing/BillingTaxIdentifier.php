<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Repository\Billing\BillingTaxIdentifierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillingTaxIdentifierRepository::class)]
#[ORM\Table(name: 'billing_tax_identifier')]
class BillingTaxIdentifier
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AgencyBillingProfile $billingProfile;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(length: 2)]
    private string $countryCode;

    #[ORM\Column(length: 255)]
    private string $value;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $stripeTaxId = null;

    #[ORM\Column(length: 30)]
    private string $verificationStatus = 'pending';

    #[ORM\Column(options: ['default' => false])]
    private bool $isPrimary = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBillingProfile(): AgencyBillingProfile
    {
        return $this->billingProfile;
    }

    public function setBillingProfile(AgencyBillingProfile $billingProfile): static
    {
        $this->billingProfile = $billingProfile;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getStripeTaxId(): ?string
    {
        return $this->stripeTaxId;
    }

    public function setStripeTaxId(?string $stripeTaxId): static
    {
        $this->stripeTaxId = $stripeTaxId;

        return $this;
    }

    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }

    public function setVerificationStatus(string $verificationStatus): static
    {
        $this->verificationStatus = $verificationStatus;

        return $this;
    }

    public function isIsPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(bool $isPrimary): static
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    public function isPrimary(): ?bool
    {
        return $this->isPrimary;
    }
}
