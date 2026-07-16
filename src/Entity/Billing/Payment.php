<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use App\Entity\Devise;
use App\Entity\Booster\BoosterPack;
use App\Entity\Billing\Enum\PaymentType;
use App\Entity\Billing\Enum\PaymentStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment')]
class Payment
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $reference;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private User $agency;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private AgencyBillingProfile $billingProfile;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AgencyPaymentMethod $paymentMethod = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?AgencySubscription $subscription = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?BoosterPack $boosterPack = null;

    #[ORM\Column(enumType: PaymentType::class, length: 50)]
    private PaymentType $type;

    #[ORM\Column(enumType: PaymentStatus::class, length: 40)]
    private PaymentStatus $status = PaymentStatus::CREATED;

    #[ORM\Column(type: 'bigint')]
    private int $amountSubtotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $discountAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $taxAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountTotalMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountPaidMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $amountRefundedMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Devise $settlementCurrency = null;

    #[ORM\Column(type: 'decimal', precision: 18, scale: 8, nullable: true)]
    private ?string $exchangeRate = null;

    #[ORM\Column(type: 'bigint')]
    private int $grossSettlementAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $feeSettlementAmountMinor = 0;

    #[ORM\Column(type: 'bigint')]
    private int $netSettlementAmountMinor = 0;

    #[ORM\Column(length: 30)]
    private string $provider = 'stripe';

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerPaymentIntentId = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerChargeId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerInvoiceId = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerCheckoutSessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerBalanceTransactionId = null;

    #[ORM\Column(type: 'json')]
    private array $paymentMethodSnapshot = [];

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $failureCode = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $failureMessage = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $authorizedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $failedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int { return $this->id; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): static { $this->reference = $reference; return $this; }

    public function getAgency(): User { return $this->agency; }
    public function setAgency(User $agency): static { $this->agency = $agency; return $this; }

    public function getBillingProfile(): AgencyBillingProfile { return $this->billingProfile; }
    public function setBillingProfile(AgencyBillingProfile $billingProfile): static { $this->billingProfile = $billingProfile; return $this; }

    public function getPaymentMethod(): ?AgencyPaymentMethod { return $this->paymentMethod; }
    public function setPaymentMethod(?AgencyPaymentMethod $paymentMethod): static { $this->paymentMethod = $paymentMethod; return $this; }

    public function getSubscription(): ?AgencySubscription { return $this->subscription; }
    public function setSubscription(?AgencySubscription $subscription): static { $this->subscription = $subscription; return $this; }

    public function getBoosterPack(): ?BoosterPack { return $this->boosterPack; }
    public function setBoosterPack(?BoosterPack $boosterPack): static { $this->boosterPack = $boosterPack; return $this; }

    public function getType(): PaymentType { return $this->type; }
    public function setType(PaymentType $type): static { $this->type = $type; return $this; }

    public function getStatus(): PaymentStatus { return $this->status; }
    public function setStatus(PaymentStatus $status): static { $this->status = $status; return $this; }

    public function getAmountSubtotalMinor(): int { return $this->amountSubtotalMinor; }
    public function setAmountSubtotalMinor(int $amountSubtotalMinor): static { $this->amountSubtotalMinor = $amountSubtotalMinor; return $this; }

    public function getDiscountAmountMinor(): int { return $this->discountAmountMinor; }
    public function setDiscountAmountMinor(int $discountAmountMinor): static { $this->discountAmountMinor = $discountAmountMinor; return $this; }

    public function getTaxAmountMinor(): int { return $this->taxAmountMinor; }
    public function setTaxAmountMinor(int $taxAmountMinor): static { $this->taxAmountMinor = $taxAmountMinor; return $this; }

    public function getAmountTotalMinor(): int { return $this->amountTotalMinor; }
    public function setAmountTotalMinor(int $amountTotalMinor): static { $this->amountTotalMinor = $amountTotalMinor; return $this; }

    public function getAmountPaidMinor(): int { return $this->amountPaidMinor; }
    public function setAmountPaidMinor(int $amountPaidMinor): static { $this->amountPaidMinor = $amountPaidMinor; return $this; }

    public function getAmountRefundedMinor(): int { return $this->amountRefundedMinor; }
    public function setAmountRefundedMinor(int $amountRefundedMinor): static { $this->amountRefundedMinor = $amountRefundedMinor; return $this; }

    public function getCurrency(): Devise { return $this->currency; }
    public function setCurrency(Devise $currency): static { $this->currency = $currency; return $this; }

    public function getSettlementCurrency(): ?Devise { return $this->settlementCurrency; }
    public function setSettlementCurrency(?Devise $settlementCurrency): static { $this->settlementCurrency = $settlementCurrency; return $this; }

    public function getExchangeRate(): ?string { return $this->exchangeRate; }
    public function setExchangeRate(?string $exchangeRate): static { $this->exchangeRate = $exchangeRate; return $this; }

    public function getGrossSettlementAmountMinor(): int { return $this->grossSettlementAmountMinor; }
    public function setGrossSettlementAmountMinor(int $grossSettlementAmountMinor): static { $this->grossSettlementAmountMinor = $grossSettlementAmountMinor; return $this; }

    public function getFeeSettlementAmountMinor(): int { return $this->feeSettlementAmountMinor; }
    public function setFeeSettlementAmountMinor(int $feeSettlementAmountMinor): static { $this->feeSettlementAmountMinor = $feeSettlementAmountMinor; return $this; }

    public function getNetSettlementAmountMinor(): int { return $this->netSettlementAmountMinor; }
    public function setNetSettlementAmountMinor(int $netSettlementAmountMinor): static { $this->netSettlementAmountMinor = $netSettlementAmountMinor; return $this; }

    public function getProvider(): string { return $this->provider; }
    public function setProvider(string $provider): static { $this->provider = $provider; return $this; }

    public function getProviderPaymentIntentId(): ?string { return $this->providerPaymentIntentId; }
    public function setProviderPaymentIntentId(?string $providerPaymentIntentId): static { $this->providerPaymentIntentId = $providerPaymentIntentId; return $this; }

    public function getProviderChargeId(): ?string { return $this->providerChargeId; }
    public function setProviderChargeId(?string $providerChargeId): static { $this->providerChargeId = $providerChargeId; return $this; }

    public function getProviderInvoiceId(): ?string { return $this->providerInvoiceId; }
    public function setProviderInvoiceId(?string $providerInvoiceId): static { $this->providerInvoiceId = $providerInvoiceId; return $this; }

    public function getProviderCheckoutSessionId(): ?string { return $this->providerCheckoutSessionId; }
    public function setProviderCheckoutSessionId(?string $providerCheckoutSessionId): static { $this->providerCheckoutSessionId = $providerCheckoutSessionId; return $this; }

    public function getProviderBalanceTransactionId(): ?string { return $this->providerBalanceTransactionId; }
    public function setProviderBalanceTransactionId(?string $providerBalanceTransactionId): static { $this->providerBalanceTransactionId = $providerBalanceTransactionId; return $this; }

    public function getPaymentMethodSnapshot(): array { return $this->paymentMethodSnapshot; }
    public function setPaymentMethodSnapshot(array $paymentMethodSnapshot): static { $this->paymentMethodSnapshot = $paymentMethodSnapshot; return $this; }

    public function getMetadata(): array { return $this->metadata; }
    public function setMetadata(array $metadata): static { $this->metadata = $metadata; return $this; }

    public function getFailureCode(): ?string { return $this->failureCode; }
    public function setFailureCode(?string $failureCode): static { $this->failureCode = $failureCode; return $this; }

    public function getFailureMessage(): ?string { return $this->failureMessage; }
    public function setFailureMessage(?string $failureMessage): static { $this->failureMessage = $failureMessage; return $this; }

    public function getAuthorizedAt(): ?\DateTimeImmutable { return $this->authorizedAt; }
    public function setAuthorizedAt(?\DateTimeImmutable $authorizedAt): static { $this->authorizedAt = $authorizedAt; return $this; }

    public function getPaidAt(): ?\DateTimeImmutable { return $this->paidAt; }
    public function setPaidAt(?\DateTimeImmutable $paidAt): static { $this->paidAt = $paidAt; return $this; }

    public function getFailedAt(): ?\DateTimeImmutable { return $this->failedAt; }
    public function setFailedAt(?\DateTimeImmutable $failedAt): static { $this->failedAt = $failedAt; return $this; }

    public function getCanceledAt(): ?\DateTimeImmutable { return $this->canceledAt; }
    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static { $this->canceledAt = $canceledAt; return $this; }

}
