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

use App\Entity\Devise;
use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agency_billing_profile')]
class AgencyBillingProfile
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(
        targetEntity: User::class,
        inversedBy: 'billingProfile'
    )]
    #[ORM\JoinColumn(
        name: 'agency_id',
        referencedColumnName: 'id',
        nullable: false,
        unique: true,
        onDelete: 'CASCADE'
    )]
    private ?User $agency = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $stripeCustomerId;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Devise $preferredCurrency = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true, unique: true, onDelete: 'SET NULL')]
    private ?AgencyPaymentMethod $defaultPaymentMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $billingEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commercialName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 10, options: ['default' => 'fr'])]
    private string $locale = 'fr';

    #[ORM\Column(length: 30, options: ['default' => 'none'])]
    private string $taxExemptStatus = 'none';

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgency(): ?User
    {
        return $this->agency;
    }

    public function setAgency(?User $agency): static
    {
        $this->agency = $agency;

        if (
            null !== $agency
            && $agency->getBillingProfile() !== $this
        ) {
            $agency->setBillingProfile($this);
        }

        return $this;
    }

    public function getStripeCustomerId(): string
    {
        return $this->stripeCustomerId;
    }

    public function setStripeCustomerId(string $stripeCustomerId): static
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }

    public function getPreferredCurrency(): ?Devise
    {
        return $this->preferredCurrency;
    }

    public function setPreferredCurrency(?Devise $preferredCurrency): static
    {
        $this->preferredCurrency = $preferredCurrency;

        return $this;
    }

    public function getDefaultPaymentMethod(): ?AgencyPaymentMethod
    {
        return $this->defaultPaymentMethod;
    }

    public function setDefaultPaymentMethod(?AgencyPaymentMethod $defaultPaymentMethod): static
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;

        return $this;
    }

    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }

    public function setBillingEmail(?string $billingEmail): static
    {
        $this->billingEmail = $billingEmail;

        return $this;
    }

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function setLegalName(?string $legalName): static
    {
        $this->legalName = $legalName;

        return $this;
    }

    public function getCommercialName(): ?string
    {
        return $this->commercialName;
    }

    public function setCommercialName(?string $commercialName): static
    {
        $this->commercialName = $commercialName;

        return $this;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

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

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTaxExemptStatus(): string
    {
        return $this->taxExemptStatus;
    }

    public function setTaxExemptStatus(string $taxExemptStatus): static
    {
        $this->taxExemptStatus = $taxExemptStatus;

        return $this;
    }
}
