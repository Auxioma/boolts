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

use App\Entity\Billing\Enum\InvoiceStatus;
use App\Entity\Billing\Enum\InvoiceType;
use App\Entity\Devise;
use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice')]
class Invoice
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $number;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private User $agency;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private AgencyBillingProfile $billingProfile;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AgencySubscription $subscription = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AgencySubscriptionPeriod $subscriptionPeriod = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Payment $payment = null;

    #[ORM\Column(enumType: InvoiceStatus::class, length: 30)]
    private InvoiceStatus $status = InvoiceStatus::DRAFT;

    #[ORM\Column(enumType: InvoiceType::class, length: 30)]
    private InvoiceType $type;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(type: 'bigint')]
    private int $subtotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $discountTotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $taxableTotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $taxTotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $totalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountPaidMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountDueMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountRefundedMinor = 0;

    #[ORM\Column(type: 'json')]
    private array $sellerSnapshot = [];

    #[ORM\Column(type: 'json')]
    private array $customerSnapshot = [];

    #[ORM\Column(type: 'json')]
    private array $taxSnapshot = [];

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerInvoiceId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $providerInvoicePdfUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $providerHostedInvoiceUrl = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $issuedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dueAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

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

    public function getAgency(): User
    {
        return $this->agency;
    }

    public function setAgency(User $agency): static
    {
        $this->agency = $agency;

        return $this;
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

    public function getSubscription(): ?AgencySubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?AgencySubscription $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getSubscriptionPeriod(): ?AgencySubscriptionPeriod
    {
        return $this->subscriptionPeriod;
    }

    public function setSubscriptionPeriod(?AgencySubscriptionPeriod $subscriptionPeriod): static
    {
        $this->subscriptionPeriod = $subscriptionPeriod;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): InvoiceType
    {
        return $this->type;
    }

    public function setType(InvoiceType $type): static
    {
        $this->type = $type;

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

    public function getSubtotalMinor(): int
    {
        return $this->subtotalMinor;
    }

    public function setSubtotalMinor(int $subtotalMinor): static
    {
        $this->subtotalMinor = $subtotalMinor;

        return $this;
    }

    public function getDiscountTotalMinor(): int
    {
        return $this->discountTotalMinor;
    }

    public function setDiscountTotalMinor(int $discountTotalMinor): static
    {
        $this->discountTotalMinor = $discountTotalMinor;

        return $this;
    }

    public function getTaxableTotalMinor(): int
    {
        return $this->taxableTotalMinor;
    }

    public function setTaxableTotalMinor(int $taxableTotalMinor): static
    {
        $this->taxableTotalMinor = $taxableTotalMinor;

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

    public function getAmountPaidMinor(): int
    {
        return $this->amountPaidMinor;
    }

    public function setAmountPaidMinor(int $amountPaidMinor): static
    {
        $this->amountPaidMinor = $amountPaidMinor;

        return $this;
    }

    public function getAmountDueMinor(): int
    {
        return $this->amountDueMinor;
    }

    public function setAmountDueMinor(int $amountDueMinor): static
    {
        $this->amountDueMinor = $amountDueMinor;

        return $this;
    }

    public function getAmountRefundedMinor(): int
    {
        return $this->amountRefundedMinor;
    }

    public function setAmountRefundedMinor(int $amountRefundedMinor): static
    {
        $this->amountRefundedMinor = $amountRefundedMinor;

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

    public function getTaxSnapshot(): array
    {
        return $this->taxSnapshot;
    }

    public function setTaxSnapshot(array $taxSnapshot): static
    {
        $this->taxSnapshot = $taxSnapshot;

        return $this;
    }

    public function getProviderInvoiceId(): ?string
    {
        return $this->providerInvoiceId;
    }

    public function setProviderInvoiceId(?string $providerInvoiceId): static
    {
        $this->providerInvoiceId = $providerInvoiceId;

        return $this;
    }

    public function getProviderInvoicePdfUrl(): ?string
    {
        return $this->providerInvoicePdfUrl;
    }

    public function setProviderInvoicePdfUrl(?string $providerInvoicePdfUrl): static
    {
        $this->providerInvoicePdfUrl = $providerInvoicePdfUrl;

        return $this;
    }

    public function getProviderHostedInvoiceUrl(): ?string
    {
        return $this->providerHostedInvoiceUrl;
    }

    public function setProviderHostedInvoiceUrl(?string $providerHostedInvoiceUrl): static
    {
        $this->providerHostedInvoiceUrl = $providerHostedInvoiceUrl;

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

    public function getDueAt(): ?\DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function setDueAt(?\DateTimeImmutable $dueAt): static
    {
        $this->dueAt = $dueAt;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

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
