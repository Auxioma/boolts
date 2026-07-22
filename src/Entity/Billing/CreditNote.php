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

use App\Entity\Billing\Enum\CreditNoteStatus;
use App\Entity\Devise;
use App\Entity\Shared\TimestampableTrait;
use App\Repository\Billing\CreditNoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditNoteRepository::class)]
#[ORM\Table(name: 'credit_note')]
class CreditNote
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $number;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Invoice $invoice;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Refund $refund = null;

    #[ORM\Column(enumType: CreditNoteStatus::class, length: 20)]
    private CreditNoteStatus $status = CreditNoteStatus::DRAFT;

    #[ORM\Column(length: 255)]
    private string $reason;

    #[ORM\Column(type: 'bigint')]
    private int $subtotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $taxTotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $totalMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(type: 'json')]
    private array $sellerSnapshot = [];

    #[ORM\Column(type: 'json')]
    private array $customerSnapshot = [];

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerCreditNoteId = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $issuedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $voidedAt = null;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
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

    public function getRefund(): ?Refund
    {
        return $this->refund;
    }

    public function setRefund(?Refund $refund): static
    {
        $this->refund = $refund;

        return $this;
    }

    public function getStatus(): CreditNoteStatus
    {
        return $this->status;
    }

    public function setStatus(CreditNoteStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

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

    public function getTaxTotalMinor(): int
    {
        return $this->taxTotalMinor;
    }

    public function setTaxTotalMinor(int $taxTotalMinor): static
    {
        $this->taxTotalMinor = $taxTotalMinor;

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

    public function getCurrency(): Devise
    {
        return $this->currency;
    }

    public function setCurrency(Devise $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getSellerSnapshot(): array
    {
        return $this->sellerSnapshot;
    }

    public function setSellerSnapshot(array $sellerSnapshot): static
    {
        $this->sellerSnapshot = $sellerSnapshot;

        return $this;
    }

    public function getCustomerSnapshot(): array
    {
        return $this->customerSnapshot;
    }

    public function setCustomerSnapshot(array $customerSnapshot): static
    {
        $this->customerSnapshot = $customerSnapshot;

        return $this;
    }

    public function getProviderCreditNoteId(): ?string
    {
        return $this->providerCreditNoteId;
    }

    public function setProviderCreditNoteId(?string $providerCreditNoteId): static
    {
        $this->providerCreditNoteId = $providerCreditNoteId;

        return $this;
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(?\DateTimeImmutable $issuedAt): static
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getVoidedAt(): ?\DateTimeImmutable
    {
        return $this->voidedAt;
    }

    public function setVoidedAt(?\DateTimeImmutable $voidedAt): static
    {
        $this->voidedAt = $voidedAt;

        return $this;
    }
}
