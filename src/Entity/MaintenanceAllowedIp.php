<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity;

use App\Repository\MaintenanceAllowedIpRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaintenanceAllowedIpRepository::class)]
#[ORM\Table(name: 'maintenance_allowed_ip')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['ipAddress'],
    message: 'Cette adresse IP est déjà enregistrée.'
)]
class MaintenanceAllowedIp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 45, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez renseigner une adresse IP.')]
    #[Assert\Ip(message: 'Cette adresse IP n’est pas valide.')]
    private string $ipAddress = '';

    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();

        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $label = null !== $label ? mb_trim($label) : null;

        $this->label = '' !== $label ? $label : null;

        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = mb_trim($ipAddress);

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->normalizeIpAddress();

        $now = new \DateTimeImmutable();

        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->normalizeIpAddress();

        $this->updatedAt = new \DateTimeImmutable();
    }

    private function normalizeIpAddress(): void
    {
        $binary = @inet_pton($this->ipAddress);

        if (false === $binary) {
            return;
        }

        $normalized = inet_ntop($binary);

        if (false !== $normalized) {
            $this->ipAddress = $normalized;
        }
    }
}
