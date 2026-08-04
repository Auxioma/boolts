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

namespace App\Entity\Document;

use App\Repository\Document\RequiredDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequiredDocumentRepository::class)]
#[ORM\Table(name: 'required_document')]
class RequiredDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $required = true;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column]
    private int $maxSubmissions = 5;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $acceptedMimeTypes = null;

    #[ORM\Column]
    private int $maxFileSizeMb = 10;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = mb_trim($name);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

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

    public function getMaxSubmissions(): int
    {
        return $this->maxSubmissions;
    }

    public function setMaxSubmissions(int $maxSubmissions): static
    {
        if ($maxSubmissions < 1) {
            throw new \InvalidArgumentException('Le nombre maximal d’envois doit être supérieur à zéro.');
        }

        $this->maxSubmissions = $maxSubmissions;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getAcceptedMimeTypes(): ?string
    {
        return $this->acceptedMimeTypes;
    }

    public function setAcceptedMimeTypes(?string $acceptedMimeTypes): static
    {
        $this->acceptedMimeTypes = $acceptedMimeTypes;

        return $this;
    }

    public function getMaxFileSizeMb(): int
    {
        return $this->maxFileSizeMb;
    }

    public function setMaxFileSizeMb(int $maxFileSizeMb): static
    {
        $this->maxFileSizeMb = $maxFileSizeMb;

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

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
