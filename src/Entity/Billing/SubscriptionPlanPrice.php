<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use App\Entity\Devise;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'subscription_plan_price')]
#[ORM\UniqueConstraint(name: 'uniq_subscription_plan_currency', columns: ['plan_id', 'currency_id'])]
class SubscriptionPlanPrice
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private SubscriptionPlan $plan;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Devise $currency;

    #[ORM\Column(type: 'bigint')]
    private int $amountMinor = 0;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $paymentProviderPriceId = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int { return $this->id; }

    public function getPlan(): SubscriptionPlan { return $this->plan; }
    public function setPlan(SubscriptionPlan $plan): static { $this->plan = $plan; return $this; }

    public function getCurrency(): Devise { return $this->currency; }
    public function setCurrency(Devise $currency): static { $this->currency = $currency; return $this; }

    public function getAmountMinor(): int { return $this->amountMinor; }
    public function setAmountMinor(int $amountMinor): static { $this->amountMinor = $amountMinor; return $this; }

    public function getPaymentProviderPriceId(): ?string { return $this->paymentProviderPriceId; }
    public function setPaymentProviderPriceId(?string $paymentProviderPriceId): static { $this->paymentProviderPriceId = $paymentProviderPriceId; return $this; }

    public function isIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

}
