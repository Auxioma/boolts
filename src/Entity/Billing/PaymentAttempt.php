<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\Devise;
use App\Entity\Billing\Enum\PaymentAttemptStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_attempt')]
class PaymentAttempt
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Payment $payment;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AgencyPaymentMethod $paymentMethod = null;

    #[ORM\Column]
    private int $attemptNumber = 1;

    #[ORM\Column(enumType: PaymentAttemptStatus::class, length: 30)]
    private PaymentAttemptStatus $status = PaymentAttemptStatus::PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerPaymentIntentId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerChargeId = null;

    #[ORM\Column(type: 'bigint')]
    private int $amountMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $requiresActionType = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $declineCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $failureCode = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $failureMessage = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int { return $this->id; }

    public function getPayment(): Payment { return $this->payment; }
    public function setPayment(Payment $payment): static { $this->payment = $payment; return $this; }

    public function getPaymentMethod(): ?AgencyPaymentMethod { return $this->paymentMethod; }
    public function setPaymentMethod(?AgencyPaymentMethod $paymentMethod): static { $this->paymentMethod = $paymentMethod; return $this; }

    public function getAttemptNumber(): int { return $this->attemptNumber; }
    public function setAttemptNumber(int $attemptNumber): static { $this->attemptNumber = $attemptNumber; return $this; }

    public function getStatus(): PaymentAttemptStatus { return $this->status; }
    public function setStatus(PaymentAttemptStatus $status): static { $this->status = $status; return $this; }

    public function getProviderPaymentIntentId(): ?string { return $this->providerPaymentIntentId; }
    public function setProviderPaymentIntentId(?string $providerPaymentIntentId): static { $this->providerPaymentIntentId = $providerPaymentIntentId; return $this; }

    public function getProviderChargeId(): ?string { return $this->providerChargeId; }
    public function setProviderChargeId(?string $providerChargeId): static { $this->providerChargeId = $providerChargeId; return $this; }

    public function getAmountMinor(): int { return $this->amountMinor; }
    public function setAmountMinor(int $amountMinor): static { $this->amountMinor = $amountMinor; return $this; }

    public function getCurrency(): Devise { return $this->currency; }
    public function setCurrency(Devise $currency): static { $this->currency = $currency; return $this; }

    public function getRequiresActionType(): ?string { return $this->requiresActionType; }
    public function setRequiresActionType(?string $requiresActionType): static { $this->requiresActionType = $requiresActionType; return $this; }

    public function getDeclineCode(): ?string { return $this->declineCode; }
    public function setDeclineCode(?string $declineCode): static { $this->declineCode = $declineCode; return $this; }

    public function getFailureCode(): ?string { return $this->failureCode; }
    public function setFailureCode(?string $failureCode): static { $this->failureCode = $failureCode; return $this; }

    public function getFailureMessage(): ?string { return $this->failureMessage; }
    public function setFailureMessage(?string $failureMessage): static { $this->failureMessage = $failureMessage; return $this; }

    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function setCompletedAt(?\DateTimeImmutable $completedAt): static { $this->completedAt = $completedAt; return $this; }

}
