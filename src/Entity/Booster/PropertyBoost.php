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

namespace App\Entity\Booster;

use App\Entity\Billing\Enum\PropertyBoostStatus;
use App\Entity\Property;
use App\Entity\Shared\TimestampableTrait;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'property_boost')]
class PropertyBoost
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Property $property;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $agency;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'RESTRICT')]
    private BoosterTransaction $boosterTransaction;

    #[ORM\Column(enumType: PropertyBoostStatus::class, length: 20)]
    private PropertyBoostStatus $status = PropertyBoostStatus::ACTIVE;

    #[ORM\Column]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    public function __construct()
    {
        $this->initializeTimestamps();
        $this->startsAt = new \DateTimeImmutable();
        $this->endsAt = $this->startsAt->modify('+7 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function setProperty(Property $property): static
    {
        $this->property = $property;

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

    public function getBoosterTransaction(): BoosterTransaction
    {
        return $this->boosterTransaction;
    }

    public function setBoosterTransaction(BoosterTransaction $boosterTransaction): static
    {
        $this->boosterTransaction = $boosterTransaction;

        return $this;
    }

    public function getStatus(): PropertyBoostStatus
    {
        return $this->status;
    }

    public function setStatus(PropertyBoostStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): \DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }
}
