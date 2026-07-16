<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\Devise;
use App\Entity\Billing\Enum\SubscriptionPeriodStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'agency_subscription_period')]
class AgencySubscriptionPeriod
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AgencySubscription $subscription;

    #[ORM\Column]
    private \DateTimeImmutable $periodStart;

    #[ORM\Column]
    private \DateTimeImmutable $periodEnd;

    #[ORM\Column(nullable: true)]
    private ?int $propertyLimit = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $includedBoosts = 0;

    #[ORM\Column(type: 'bigint', options: ['default' => 0])]
    private int $amountMinor = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Payment $payment = null;

    #[ORM\Column(enumType: SubscriptionPeriodStatus::class, length: 30)]
    private SubscriptionPeriodStatus $status = SubscriptionPeriodStatus::PENDING;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $providerInvoiceId = null;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int { return $this->id; }

    public function getSubscription(): AgencySubscription { return $this->subscription; }
    public function setSubscription(AgencySubscription $subscription): static { $this->subscription = $subscription; return $this; }

    public function getPeriodStart(): \DateTimeImmutable { return $this->periodStart; }
    public function setPeriodStart(\DateTimeImmutable $periodStart): static { $this->periodStart = $periodStart; return $this; }

    public function getPeriodEnd(): \DateTimeImmutable { return $this->periodEnd; }
    public function setPeriodEnd(\DateTimeImmutable $periodEnd): static { $this->periodEnd = $periodEnd; return $this; }

    public function getPropertyLimit(): ?int { return $this->propertyLimit; }
    public function setPropertyLimit(?int $propertyLimit): static { $this->propertyLimit = $propertyLimit; return $this; }

    public function getIncludedBoosts(): int { return $this->includedBoosts; }
    public function setIncludedBoosts(int $includedBoosts): static { $this->includedBoosts = $includedBoosts; return $this; }

    public function getAmountMinor(): int { return $this->amountMinor; }
    public function setAmountMinor(int $amountMinor): static { $this->amountMinor = $amountMinor; return $this; }

    public function getCurrency(): Devise { return $this->currency; }
    public function setCurrency(Devise $currency): static { $this->currency = $currency; return $this; }

    public function getPayment(): ?Payment { return $this->payment; }
    public function setPayment(?Payment $payment): static { $this->payment = $payment; return $this; }

    public function getStatus(): SubscriptionPeriodStatus { return $this->status; }
    public function setStatus(SubscriptionPeriodStatus $status): static { $this->status = $status; return $this; }

    public function getProviderInvoiceId(): ?string { return $this->providerInvoiceId; }
    public function setProviderInvoiceId(?string $providerInvoiceId): static { $this->providerInvoiceId = $providerInvoiceId; return $this; }

}
