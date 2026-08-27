<?php

declare(strict_types=1);

namespace App\Entity\Billing;

use App\Entity\Billing\Enum\SubscriptionEmailStatus;
use App\Entity\Billing\Enum\SubscriptionEmailType;
use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use App\Repository\Billing\SubscriptionEmailLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionEmailLogRepository::class)]
#[ORM\Table(name: 'subscription_email_log')]
#[ORM\UniqueConstraint(name: 'uniq_subscription_email_event', columns: ['subscription_id', 'event_type', 'event_key'])]
class SubscriptionEmailLog
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

    #[ORM\Column(enumType: SubscriptionEmailType::class, length: 80)]
    private SubscriptionEmailType $eventType;

    #[ORM\Column(length: 255)]
    private string $eventKey;

    #[ORM\Column(length: 255)]
    private string $recipientEmail;

    #[ORM\Column(length: 255)]
    private string $subject;

    #[ORM\Column(type: 'json')]
    private array $context = [];

    #[ORM\Column(enumType: SubscriptionEmailStatus::class, length: 30)]
    private SubscriptionEmailStatus $status = SubscriptionEmailStatus::PENDING;

    #[ORM\Column]
    private \DateTimeImmutable $queuedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $failedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    public function __construct()
    {
        $this->initializeTimestamps();
        $this->queuedAt = new \DateTimeImmutable();
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

    public function getEventType(): SubscriptionEmailType
    {
        return $this->eventType;
    }

    public function setEventType(SubscriptionEmailType $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getEventKey(): string
    {
        return $this->eventKey;
    }

    public function setEventKey(string $eventKey): static
    {
        $this->eventKey = $eventKey;

        return $this;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail(string $recipientEmail): static
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getStatus(): SubscriptionEmailStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionEmailStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getQueuedAt(): \DateTimeImmutable
    {
        return $this->queuedAt;
    }

    public function setQueuedAt(\DateTimeImmutable $queuedAt): static
    {
        $this->queuedAt = $queuedAt;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failedAt;
    }

    public function setFailedAt(?\DateTimeImmutable $failedAt): static
    {
        $this->failedAt = $failedAt;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
