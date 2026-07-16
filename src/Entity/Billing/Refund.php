<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\Devise;
use App\Entity\Billing\Enum\RefundStatus;
use App\Entity\Billing\Enum\RefundReason;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'refund')]
class Refund
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $reference;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Payment $payment;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Invoice $invoice = null;

    #[ORM\Column(enumType: RefundStatus::class, length: 30)]
    private RefundStatus $status = RefundStatus::PENDING;

    #[ORM\Column(enumType: RefundReason::class, length: 50)]
    private RefundReason $reason = RefundReason::OTHER;

    #[ORM\Column(type: 'bigint')]
    private int $amountMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerRefundId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerBalanceTransactionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $failureReason = null;

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    #[ORM\Column]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $failedAt = null;

    public function __construct()
    {
        $this->initializeTimestamps();
        $this->requestedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = $reference; return $this; }

    public function getPayment(): Payment { return $this->payment; }
    public function setPayment(Payment $payment): static { $this->payment = $payment; return $this; }

    public function getInvoice(): ?Invoice { return $this->invoice; }
    public function setInvoice(?Invoice $invoice): static { $this->invoice = $invoice; return $this; }

    public function getStatus(): RefundStatus { return $this->status; }
    public function setStatus(RefundStatus $status): static { $this->status = $status; return $this; }

    public function getReason(): RefundReason { return $this->reason; }
    public function setReason(RefundReason $reason): static { $this->reason = $reason; return $this; }

    public function getAmountMinor(): int { return $this->amountMinor; }
    public function setAmountMinor(int $amountMinor): static { $this->amountMinor = $amountMinor; return $this; }

    public function getCurrency(): Devise { return $this->currency; }
    public function setCurrency(Devise $currency): static { $this->currency = $currency; return $this; }

    public function getProviderRefundId(): ?string { return $this->providerRefundId; }
    public function setProviderRefundId(?string $providerRefundId): static { $this->providerRefundId = $providerRefundId; return $this; }

    public function getProviderBalanceTransactionId(): ?string { return $this->providerBalanceTransactionId; }
    public function setProviderBalanceTransactionId(?string $providerBalanceTransactionId): static { $this->providerBalanceTransactionId = $providerBalanceTransactionId; return $this; }

    public function getFailureReason(): ?string { return $this->failureReason; }
    public function setFailureReason(?string $failureReason): static { $this->failureReason = $failureReason; return $this; }

    public function getMetadata(): array { return $this->metadata; }
    public function setMetadata(array $metadata): static { $this->metadata = $metadata; return $this; }

    public function getRequestedAt(): \DateTimeImmutable { return $this->requestedAt; }
    public function setRequestedAt(\DateTimeImmutable $requestedAt): static { $this->requestedAt = $requestedAt; return $this; }

    public function getProcessedAt(): ?\DateTimeImmutable { return $this->processedAt; }
    public function setProcessedAt(?\DateTimeImmutable $processedAt): static { $this->processedAt = $processedAt; return $this; }

    public function getFailedAt(): ?\DateTimeImmutable { return $this->failedAt; }
    public function setFailedAt(?\DateTimeImmutable $failedAt): static { $this->failedAt = $failedAt; return $this; }

}
