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
use App\Repository\Billing\InvoiceLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceLineRepository::class)]
#[ORM\Table(name: 'invoice_line')]
class InvoiceLine
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lines')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Invoice $invoice;

    #[ORM\Column(length: 50)]
    private string $type;

    #[ORM\Column(length: 255)]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 3)]
    private string $quantity = '1.000';

    #[ORM\Column(type: 'bigint')]
    private int $unitAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $subtotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $discountAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $taxableAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $taxAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $totalMinor = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $periodStart = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $periodEnd = null;

    #[ORM\Column]
    private int $position = 0;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitAmountMinor(): int
    {
        return $this->unitAmountMinor;
    }

    public function setUnitAmountMinor(int $unitAmountMinor): static
    {
        $this->unitAmountMinor = $unitAmountMinor;

        return $this;
    }

    public function getSubtotalMinor(): int
    {
        return $this->subtotalMinor;
    }

    public function setSubtotalMinor(int $subtotalMinor): static
    {
        $this->subtotalMinor = $subtotalMinor;

        return $this;
    }

    public function getDiscountAmountMinor(): int
    {
        return $this->discountAmountMinor;
    }

    public function setDiscountAmountMinor(int $discountAmountMinor): static
    {
        $this->discountAmountMinor = $discountAmountMinor;

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

    public function getTaxAmountMinor(): int
    {
        return $this->taxAmountMinor;
    }

    public function setTaxAmountMinor(int $taxAmountMinor): static
    {
        $this->taxAmountMinor = $taxAmountMinor;

        return $this;
    }

    public function getTotalMinor(): int
    {
        return $this->totalMinor;
    }

    public function setTotalMinor(int $totalMinor): static
    {
        $this->totalMinor = $totalMinor;

        return $this;
    }

    public function getPeriodStart(): ?\DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function setPeriodStart(?\DateTimeImmutable $periodStart): static
    {
        $this->periodStart = $periodStart;

        return $this;
    }

    public function getPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(?\DateTimeImmutable $periodEnd): static
    {
        $this->periodEnd = $periodEnd;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
