<?php

declare(strict_types=1);

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

use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Devise;
use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use App\Repository\Billing\AgencySubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencySubscriptionRepository::class)]
#[ORM\Table(name: 'agency_subscription')]
#[ORM\Index(name: 'idx_agency_subscription_status_period_end', columns: ['status', 'current_period_end'])]
#[ORM\Index(name: 'idx_agency_subscription_retry', columns: ['status', 'next_payment_retry_at'])]
class AgencySubscription
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $agency;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private SubscriptionPlan $plan;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?SubscriptionPlanPrice $planPrice = null;

    #[ORM\Column(enumType: SubscriptionStatus::class, length: 30)]
    private SubscriptionStatus $status = SubscriptionStatus::FREE;

    #[ORM\Column]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $currentPeriodStart = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $currentPeriodEnd = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $cancelAtPeriodEnd = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelRequestedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerCustomerId = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerSubscriptionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerSubscriptionItemId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerPriceId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerProductId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerLatestInvoiceId = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $paymentFailureCount = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $firstPaymentFailureAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastPaymentFailureAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $nextPaymentRetryAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paymentRecoveryDeadline = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSuccessfulPaymentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastStripeSyncAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $propertyLimitSnapshot = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $includedBoostsSnapshot = 0;

    #[ORM\Column(options: ['default' => 7])]
    private int $boostDurationDaysSnapshot = 7;

    #[ORM\Column(type: 'bigint', options: ['default' => 0])]
    private int $amountSnapshotMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Devise $currencySnapshot = null;

    public function __construct()
    {
        $this->initializeTimestamps();
        $this->startedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPlan(): SubscriptionPlan
    {
        return $this->plan;
    }

    public function setPlan(SubscriptionPlan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getPlanPrice(): ?SubscriptionPlanPrice
    {
        return $this->planPrice;
    }

    public function setPlanPrice(?SubscriptionPlanPrice $planPrice): static
    {
        $this->planPrice = $planPrice;

        return $this;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getCurrentPeriodStart(): ?\DateTimeImmutable
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(?\DateTimeImmutable $currentPeriodStart): static
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?\DateTimeImmutable $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function getCancelAtPeriodEnd(): bool
    {
        return $this->cancelAtPeriodEnd;
    }

    public function setCancelAtPeriodEnd(bool $cancelAtPeriodEnd): static
    {
        $this->cancelAtPeriodEnd = $cancelAtPeriodEnd;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function getCancelRequestedAt(): ?\DateTimeImmutable
    {
        return $this->cancelRequestedAt;
    }

    public function setCancelRequestedAt(?\DateTimeImmutable $cancelRequestedAt): static
    {
        $this->cancelRequestedAt = $cancelRequestedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getProviderCustomerId(): ?string
    {
        return $this->providerCustomerId;
    }

    public function setProviderCustomerId(?string $providerCustomerId): static
    {
        $this->providerCustomerId = $providerCustomerId;

        return $this;
    }

    public function getProviderSubscriptionId(): ?string
    {
        return $this->providerSubscriptionId;
    }

    public function setProviderSubscriptionId(?string $providerSubscriptionId): static
    {
        $this->providerSubscriptionId = $providerSubscriptionId;

        return $this;
    }

    public function getProviderSubscriptionItemId(): ?string
    {
        return $this->providerSubscriptionItemId;
    }

    public function setProviderSubscriptionItemId(?string $providerSubscriptionItemId): static
    {
        $this->providerSubscriptionItemId = $providerSubscriptionItemId;

        return $this;
    }

    public function getProviderPriceId(): ?string
    {
        return $this->providerPriceId;
    }

    public function setProviderPriceId(?string $providerPriceId): static
    {
        $this->providerPriceId = $providerPriceId;

        return $this;
    }

    public function getProviderProductId(): ?string
    {
        return $this->providerProductId;
    }

    public function setProviderProductId(?string $providerProductId): static
    {
        $this->providerProductId = $providerProductId;

        return $this;
    }

    public function getProviderLatestInvoiceId(): ?string
    {
        return $this->providerLatestInvoiceId;
    }

    public function setProviderLatestInvoiceId(?string $providerLatestInvoiceId): static
    {
        $this->providerLatestInvoiceId = $providerLatestInvoiceId;

        return $this;
    }

    public function getPaymentFailureCount(): int
    {
        return $this->paymentFailureCount;
    }

    public function setPaymentFailureCount(int $paymentFailureCount): static
    {
        $this->paymentFailureCount = max(0, $paymentFailureCount);

        return $this;
    }

    public function incrementPaymentFailureCount(): static
    {
        ++$this->paymentFailureCount;

        return $this;
    }

    public function getFirstPaymentFailureAt(): ?\DateTimeImmutable
    {
        return $this->firstPaymentFailureAt;
    }

    public function setFirstPaymentFailureAt(?\DateTimeImmutable $firstPaymentFailureAt): static
    {
        $this->firstPaymentFailureAt = $firstPaymentFailureAt;

        return $this;
    }

    public function getLastPaymentFailureAt(): ?\DateTimeImmutable
    {
        return $this->lastPaymentFailureAt;
    }

    public function setLastPaymentFailureAt(?\DateTimeImmutable $lastPaymentFailureAt): static
    {
        $this->lastPaymentFailureAt = $lastPaymentFailureAt;

        return $this;
    }

    public function getNextPaymentRetryAt(): ?\DateTimeImmutable
    {
        return $this->nextPaymentRetryAt;
    }

    public function setNextPaymentRetryAt(?\DateTimeImmutable $nextPaymentRetryAt): static
    {
        $this->nextPaymentRetryAt = $nextPaymentRetryAt;

        return $this;
    }

    public function getPaymentRecoveryDeadline(): ?\DateTimeImmutable
    {
        return $this->paymentRecoveryDeadline;
    }

    public function setPaymentRecoveryDeadline(?\DateTimeImmutable $paymentRecoveryDeadline): static
    {
        $this->paymentRecoveryDeadline = $paymentRecoveryDeadline;

        return $this;
    }

    public function getLastSuccessfulPaymentAt(): ?\DateTimeImmutable
    {
        return $this->lastSuccessfulPaymentAt;
    }

    public function setLastSuccessfulPaymentAt(?\DateTimeImmutable $lastSuccessfulPaymentAt): static
    {
        $this->lastSuccessfulPaymentAt = $lastSuccessfulPaymentAt;

        return $this;
    }

    public function getLastStripeSyncAt(): ?\DateTimeImmutable
    {
        return $this->lastStripeSyncAt;
    }

    public function setLastStripeSyncAt(?\DateTimeImmutable $lastStripeSyncAt): static
    {
        $this->lastStripeSyncAt = $lastStripeSyncAt;

        return $this;
    }

    public function getPropertyLimitSnapshot(): ?int
    {
        return $this->propertyLimitSnapshot;
    }

    public function setPropertyLimitSnapshot(?int $propertyLimitSnapshot): static
    {
        $this->propertyLimitSnapshot = $propertyLimitSnapshot;

        return $this;
    }

    public function getIncludedBoostsSnapshot(): int
    {
        return $this->includedBoostsSnapshot;
    }

    public function setIncludedBoostsSnapshot(int $includedBoostsSnapshot): static
    {
        $this->includedBoostsSnapshot = $includedBoostsSnapshot;

        return $this;
    }

    public function getBoostDurationDaysSnapshot(): int
    {
        return $this->boostDurationDaysSnapshot;
    }

    public function setBoostDurationDaysSnapshot(int $boostDurationDaysSnapshot): static
    {
        $this->boostDurationDaysSnapshot = $boostDurationDaysSnapshot;

        return $this;
    }

    public function getAmountSnapshotMinor(): int
    {
        return $this->amountSnapshotMinor;
    }

    public function setAmountSnapshotMinor(int $amountSnapshotMinor): static
    {
        $this->amountSnapshotMinor = $amountSnapshotMinor;

        return $this;
    }

    public function getCurrencySnapshot(): ?Devise
    {
        return $this->currencySnapshot;
    }

    public function setCurrencySnapshot(?Devise $currencySnapshot): static
    {
        $this->currencySnapshot = $currencySnapshot;

        return $this;
    }

    public function isCancelAtPeriodEnd(): ?bool
    {
        return $this->cancelAtPeriodEnd;
    }
}
