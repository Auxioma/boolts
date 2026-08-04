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

namespace App\Entity\Billing;

use App\Entity\Billing\Enum\PaymentMethodSetupStatus;
use App\Entity\Shared\TimestampableTrait;
use App\Repository\Billing\AgencyPaymentMethodRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencyPaymentMethodRepository::class)]
#[ORM\Table(name: 'agency_payment_method')]
class AgencyPaymentMethod
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AgencyBillingProfile $billingProfile;

    #[ORM\Column(length: 255, unique: true)]
    private string $stripePaymentMethodId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSetupIntentId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeMandateId = null;

    #[ORM\Column(length: 30)]
    private string $type = 'card';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $last4 = null;

    #[ORM\Column(nullable: true)]
    private ?int $expMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $expYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardholderName = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $funding = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fingerprint = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isDefault = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(enumType: PaymentMethodSetupStatus::class, length: 30)]
    private PaymentMethodSetupStatus $setupStatus = PaymentMethodSetupStatus::PENDING;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $detachedAt = null;

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

    public function getStripePaymentMethodId(): string
    {
        return $this->stripePaymentMethodId;
    }

    public function setStripePaymentMethodId(string $stripePaymentMethodId): static
    {
        $this->stripePaymentMethodId = $stripePaymentMethodId;

        return $this;
    }

    public function getStripeSetupIntentId(): ?string
    {
        return $this->stripeSetupIntentId;
    }

    public function setStripeSetupIntentId(?string $stripeSetupIntentId): static
    {
        $this->stripeSetupIntentId = $stripeSetupIntentId;

        return $this;
    }

    public function getStripeMandateId(): ?string
    {
        return $this->stripeMandateId;
    }

    public function setStripeMandateId(?string $stripeMandateId): static
    {
        $this->stripeMandateId = $stripeMandateId;

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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function setLast4(?string $last4): static
    {
        $this->last4 = $last4;

        return $this;
    }

    public function getExpMonth(): ?int
    {
        return $this->expMonth;
    }

    public function setExpMonth(?int $expMonth): static
    {
        $this->expMonth = $expMonth;

        return $this;
    }

    public function getExpYear(): ?int
    {
        return $this->expYear;
    }

    public function setExpYear(?int $expYear): static
    {
        $this->expYear = $expYear;

        return $this;
    }

    public function getCardholderName(): ?string
    {
        return $this->cardholderName;
    }

    public function setCardholderName(?string $cardholderName): static
    {
        $this->cardholderName = $cardholderName;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getFunding(): ?string
    {
        return $this->funding;
    }

    public function setFunding(?string $funding): static
    {
        $this->funding = $funding;

        return $this;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(?string $fingerprint): static
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    public function isIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSetupStatus(): PaymentMethodSetupStatus
    {
        return $this->setupStatus;
    }

    public function setSetupStatus(PaymentMethodSetupStatus $setupStatus): static
    {
        $this->setupStatus = $setupStatus;

        return $this;
    }

    public function getDetachedAt(): ?\DateTimeImmutable
    {
        return $this->detachedAt;
    }

    public function setDetachedAt(?\DateTimeImmutable $detachedAt): static
    {
        $this->detachedAt = $detachedAt;

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }
}
