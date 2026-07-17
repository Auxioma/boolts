<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity\Billing;

use App\Entity\Billing\Enum\WebhookEventStatus;
use App\Entity\Shared\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payment_webhook_event')]
class PaymentWebhookEvent
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private string $provider = 'stripe';

    #[ORM\Column(length: 255, unique: true)]
    private string $providerEventId;

    #[ORM\Column(length: 150)]
    private string $eventType;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $apiVersion = null;

    #[ORM\Column]
    private bool $livemode = false;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column(enumType: WebhookEventStatus::class, length: 30)]
    private WebhookEventStatus $status = WebhookEventStatus::RECEIVED;

    #[ORM\Column]
    private int $attemptCount = 0;

    #[ORM\Column]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $failedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    public function __construct()
    {
        $this->initializeTimestamps();
        $this->receivedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProviderEventId(): string
    {
        return $this->providerEventId;
    }

    public function setProviderEventId(string $providerEventId): static
    {
        $this->providerEventId = $providerEventId;

        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    public function setApiVersion(?string $apiVersion): static
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    public function getLivemode(): bool
    {
        return $this->livemode;
    }

    public function setLivemode(bool $livemode): static
    {
        $this->livemode = $livemode;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getStatus(): WebhookEventStatus
    {
        return $this->status;
    }

    public function setStatus(WebhookEventStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    public function setAttemptCount(int $attemptCount): static
    {
        $this->attemptCount = $attemptCount;

        return $this;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeImmutable $receivedAt): static
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): static
    {
        $this->processedAt = $processedAt;

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
