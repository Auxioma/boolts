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

namespace App\Entity\Search;

use App\Repository\Search\PropertySearchSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PropertySearchSessionRepository::class)]
#[ORM\Table(name: 'property_search_session')]
#[ORM\Index(columns: ['uuid'], name: 'idx_property_search_session_uuid')]
#[ORM\Index(columns: ['transaction_type_id'], name: 'idx_property_search_session_transaction_type')]
#[ORM\Index(columns: ['ville'], name: 'idx_property_search_session_ville')]
#[ORM\Index(columns: ['cp'], name: 'idx_property_search_session_cp')]
#[ORM\Index(columns: ['pays'], name: 'idx_property_search_session_pays')]
#[ORM\Index(columns: ['expires_at'], name: 'idx_property_search_session_expires_at')]
class PropertySearchSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $id = null;

    /**
     * UUID public utilisé dans le cookie et/ou l’URL.
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(name: 'transaction_type_id', type: Types::BIGINT)]
    private ?string $transactionTypeId = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cp = null;

    #[ORM\Column(length: 180)]
    private ?string $pays = null;

    /**
     * Pour stocker tous les filtres supplémentaires :
     * prix, surface, chambres, caractéristiques, etc.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $filters = [];

    #[ORM\Column(name: 'created_at')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'expires_at')]
    private ?\DateTimeImmutable $expiresAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v7();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->expiresAt = new \DateTimeImmutable('+30 days');
    }

    public function refreshUpdatedAt(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getUuidString(): ?string
    {
        return $this->uuid?->toRfc4122();
    }

    public function setUuid(Uuid $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getTransactionTypeId(): ?string
    {
        return $this->transactionTypeId;
    }

    public function setTransactionTypeId(int|string $transactionTypeId): self
    {
        $this->transactionTypeId = (string) $transactionTypeId;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters): self
    {
        $this->filters = $filters ?? [];

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
