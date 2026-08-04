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

use App\Entity\Billing\Enum\PaymentFeeType;
use App\Entity\Devise;
use App\Entity\Shared\TimestampableTrait;
use App\Repository\Billing\PaymentFeeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentFeeRepository::class)]
#[ORM\Table(name: 'payment_fee')]
class PaymentFee
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Payment $payment;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Refund $refund = null;

    #[ORM\Column(enumType: PaymentFeeType::class, length: 60)]
    private PaymentFeeType $type;

    #[ORM\Column(type: 'bigint')]
    private int $amountMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerBalanceTransactionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isRefundable = false;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): static
    {
        $this->payment = $payment;

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

    public function getType(): PaymentFeeType
    {
        return $this->type;
    }

    public function setType(PaymentFeeType $type): static
    {
        $this->type = $type;

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

    public function getCurrency(): Devise
    {
        return $this->currency;
    }

    public function setCurrency(Devise $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getProviderBalanceTransactionId(): ?string
    {
        return $this->providerBalanceTransactionId;
    }

    public function setProviderBalanceTransactionId(?string $providerBalanceTransactionId): static
    {
        $this->providerBalanceTransactionId = $providerBalanceTransactionId;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isIsRefundable(): bool
    {
        return $this->isRefundable;
    }

    public function setIsRefundable(bool $isRefundable): static
    {
        $this->isRefundable = $isRefundable;

        return $this;
    }

    public function isRefundable(): ?bool
    {
        return $this->isRefundable;
    }
}
