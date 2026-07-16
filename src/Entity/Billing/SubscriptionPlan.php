<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Shared\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'subscription_plan')]
#[ORM\UniqueConstraint(name: 'uniq_subscription_plan_code', columns: ['code'])]
class SubscriptionPlan
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $code;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $propertyLimit = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $includedBoosts = 0;

    #[ORM\Column(options: ['default' => 7])]
    private int $boostDurationDays = 7;

    #[ORM\Column(options: ['default' => false])]
    private bool $isFree = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $isDefault = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int { return $this->id; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getPropertyLimit(): ?int { return $this->propertyLimit; }
    public function setPropertyLimit(?int $propertyLimit): static { $this->propertyLimit = $propertyLimit; return $this; }

    public function getIncludedBoosts(): int { return $this->includedBoosts; }
    public function setIncludedBoosts(int $includedBoosts): static { $this->includedBoosts = $includedBoosts; return $this; }

    public function getBoostDurationDays(): int { return $this->boostDurationDays; }
    public function setBoostDurationDays(int $boostDurationDays): static { $this->boostDurationDays = $boostDurationDays; return $this; }

    public function isIsFree(): bool { return $this->isFree; }
    public function setIsFree(bool $isFree): static { $this->isFree = $isFree; return $this; }

    public function isIsDefault(): bool { return $this->isDefault; }
    public function setIsDefault(bool $isDefault): static { $this->isDefault = $isDefault; return $this; }

    public function isIsActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }

}
