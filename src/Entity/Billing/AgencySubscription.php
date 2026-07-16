<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use App\Entity\Devise;
use App\Entity\Billing\Enum\SubscriptionStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agency_subscription')]
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
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerCustomerId = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerSubscriptionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerSubscriptionItemId = null;

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

    public function getId(): ?int { return $this->id; }

    public function getAgency(): User { return $this->agency; }
    public function setAgency(User $agency): static { $this->agency = $agency; return $this; }

    public function getPlan(): SubscriptionPlan { return $this->plan; }
    public function setPlan(SubscriptionPlan $plan): static { $this->plan = $plan; return $this; }

    public function getPlanPrice(): ?SubscriptionPlanPrice { return $this->planPrice; }
    public function setPlanPrice(?SubscriptionPlanPrice $planPrice): static { $this->planPrice = $planPrice; return $this; }

    public function getStatus(): SubscriptionStatus { return $this->status; }
    public function setStatus(SubscriptionStatus $status): static { $this->status = $status; return $this; }

    public function getStartedAt(): \DateTimeImmutable { return $this->startedAt; }
    public function setStartedAt(\DateTimeImmutable $startedAt): static { $this->startedAt = $startedAt; return $this; }

    public function getCurrentPeriodStart(): ?\DateTimeImmutable { return $this->currentPeriodStart; }
    public function setCurrentPeriodStart(?\DateTimeImmutable $currentPeriodStart): static { $this->currentPeriodStart = $currentPeriodStart; return $this; }

    public function getCurrentPeriodEnd(): ?\DateTimeImmutable { return $this->currentPeriodEnd; }
    public function setCurrentPeriodEnd(?\DateTimeImmutable $currentPeriodEnd): static { $this->currentPeriodEnd = $currentPeriodEnd; return $this; }

    public function getCancelAtPeriodEnd(): bool { return $this->cancelAtPeriodEnd; }
    public function setCancelAtPeriodEnd(bool $cancelAtPeriodEnd): static { $this->cancelAtPeriodEnd = $cancelAtPeriodEnd; return $this; }

    public function getCanceledAt(): ?\DateTimeImmutable { return $this->canceledAt; }
    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static { $this->canceledAt = $canceledAt; return $this; }

    public function getEndedAt(): ?\DateTimeImmutable { return $this->endedAt; }
    public function setEndedAt(?\DateTimeImmutable $endedAt): static { $this->endedAt = $endedAt; return $this; }

    public function getProviderCustomerId(): ?string { return $this->providerCustomerId; }
    public function setProviderCustomerId(?string $providerCustomerId): static { $this->providerCustomerId = $providerCustomerId; return $this; }

    public function getProviderSubscriptionId(): ?string { return $this->providerSubscriptionId; }
    public function setProviderSubscriptionId(?string $providerSubscriptionId): static { $this->providerSubscriptionId = $providerSubscriptionId; return $this; }

    public function getProviderSubscriptionItemId(): ?string { return $this->providerSubscriptionItemId; }
    public function setProviderSubscriptionItemId(?string $providerSubscriptionItemId): static { $this->providerSubscriptionItemId = $providerSubscriptionItemId; return $this; }

    public function getPropertyLimitSnapshot(): ?int { return $this->propertyLimitSnapshot; }
    public function setPropertyLimitSnapshot(?int $propertyLimitSnapshot): static { $this->propertyLimitSnapshot = $propertyLimitSnapshot; return $this; }

    public function getIncludedBoostsSnapshot(): int { return $this->includedBoostsSnapshot; }
    public function setIncludedBoostsSnapshot(int $includedBoostsSnapshot): static { $this->includedBoostsSnapshot = $includedBoostsSnapshot; return $this; }

    public function getBoostDurationDaysSnapshot(): int { return $this->boostDurationDaysSnapshot; }
    public function setBoostDurationDaysSnapshot(int $boostDurationDaysSnapshot): static { $this->boostDurationDaysSnapshot = $boostDurationDaysSnapshot; return $this; }

    public function getAmountSnapshotMinor(): int { return $this->amountSnapshotMinor; }
    public function setAmountSnapshotMinor(int $amountSnapshotMinor): static { $this->amountSnapshotMinor = $amountSnapshotMinor; return $this; }

    public function getCurrencySnapshot(): ?Devise { return $this->currencySnapshot; }
    public function setCurrencySnapshot(?Devise $currencySnapshot): static { $this->currencySnapshot = $currencySnapshot; return $this; }

}
