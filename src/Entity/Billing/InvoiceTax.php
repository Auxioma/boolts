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

use App\Entity\Shared\TimestampableTrait;
use App\Repository\Billing\InvoiceTaxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceTaxRepository::class)]
#[ORM\Table(name: 'invoice_tax')]
class InvoiceTax
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Invoice $invoice;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?InvoiceLine $invoiceLine = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(length: 2)]
    private string $countryCode;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $regionCode = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 5)]
    private string $rate = '0.00000';

    #[ORM\Column(type: 'bigint')]
    private int $taxableAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountMinor = 0;

    #[ORM\Column]
    private bool $inclusive = false;

    #[ORM\Column(length: 30)]
    private string $taxBehavior = 'exclusive';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerTaxRateId = null;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getInvoiceLine(): ?InvoiceLine
    {
        return $this->invoiceLine;
    }

    public function setInvoiceLine(?InvoiceLine $invoiceLine): static
    {
        $this->invoiceLine = $invoiceLine;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(?string $regionCode): static
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getTaxableAmountMinor(): int
    {
        return $this->taxableAmountMinor;
    }

    public function setTaxableAmountMinor(int $taxableAmountMinor): static
    {
        $this->taxableAmountMinor = $taxableAmountMinor;

        return $this;
    }

    public function getAmountMinor(): int
    {
        return $this->amountMinor;
    }

    public function setAmountMinor(int $amountMinor): static
    {
        $this->amountMinor = $amountMinor;

        return $this;
    }

    public function getInclusive(): bool
    {
        return $this->inclusive;
    }

    public function setInclusive(bool $inclusive): static
    {
        $this->inclusive = $inclusive;

        return $this;
    }

    public function getTaxBehavior(): string
    {
        return $this->taxBehavior;
    }

    public function setTaxBehavior(string $taxBehavior): static
    {
        $this->taxBehavior = $taxBehavior;

        return $this;
    }

    public function getProviderTaxRateId(): ?string
    {
        return $this->providerTaxRateId;
    }

    public function setProviderTaxRateId(?string $providerTaxRateId): static
    {
        $this->providerTaxRateId = $providerTaxRateId;

        return $this;
    }

    public function isInclusive(): ?bool
    {
        return $this->inclusive;
    }
}
