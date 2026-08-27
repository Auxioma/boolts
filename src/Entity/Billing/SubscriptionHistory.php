<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Billing\Enum\SubscriptionHistoryEventType;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use App\Repository\Billing\SubscriptionHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionHistoryRepository::class)]
#[ORM\Table(name: 'subscription_history')]
#[ORM\Index(name: 'idx_subscription_history_subscription_event', columns: ['subscription_id', 'event_type'])]
class SubscriptionHistory
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AgencySubscription $subscription;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $agency;

    #[ORM\Column(enumType: SubscriptionHistoryEventType::class, length: 80)]
    private SubscriptionHistoryEventType $eventType;

    #[ORM\Column(enumType: SubscriptionStatus::class, length: 30, nullable: true)]
    private ?SubscriptionStatus $oldStatus = null;

    #[ORM\Column(enumType: SubscriptionStatus::class, length: 30, nullable: true)]
    private ?SubscriptionStatus $newStatus = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $oldPlan = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $newPlan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerInvoiceId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $providerPaymentIntentId = null;

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubscription(): AgencySubscription
    {
        return $this->subscription;
    }

    public function setSubscription(AgencySubscription $subscription): static
    {
        $this->subscription = $subscription;

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

    public function getEventType(): SubscriptionHistoryEventType
    {
        return $this->eventType;
    }

    public function setEventType(SubscriptionHistoryEventType $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getOldStatus(): ?SubscriptionStatus
    {
        return $this->oldStatus;
    }

    public function setOldStatus(?SubscriptionStatus $oldStatus): static
    {
        $this->oldStatus = $oldStatus;

        return $this;
    }

    public function getNewStatus(): ?SubscriptionStatus
    {
        return $this->newStatus;
    }

    public function setNewStatus(?SubscriptionStatus $newStatus): static
    {
        $this->newStatus = $newStatus;

        return $this;
    }

    public function getOldPlan(): ?string
    {
        return $this->oldPlan;
    }

    public function setOldPlan(?string $oldPlan): static
    {
        $this->oldPlan = $oldPlan;

        return $this;
    }

    public function getNewPlan(): ?string
    {
        return $this->newPlan;
    }

    public function setNewPlan(?string $newPlan): static
    {
        $this->newPlan = $newPlan;

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

    public function getProviderPaymentIntentId(): ?string
    {
        return $this->providerPaymentIntentId;
    }

    public function setProviderPaymentIntentId(?string $providerPaymentIntentId): static
    {
        $this->providerPaymentIntentId = $providerPaymentIntentId;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }
}
