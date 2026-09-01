<?php

declare(strict_types=1);

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

use App\Repository\AgencyNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencyNotificationRepository::class)]
#[ORM\Table(name: 'agency_notification')]
class AgencyNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom de la notification (action réalisée côté agence immobilière).
     */
    #[ORM\Column(length: 255)]
    private string $nom;

    /**
     * Date de création de la notification.
     */
    #[ORM\Column]
    private \DateTimeImmutable $date;

    /**
     * Date de lecture (null tant que la notification n'a pas été lue).
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    /**
     * Agence immobilière destinataire de la notification.
     */
    #[ORM\ManyToOne(inversedBy: 'agencyNotifications')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $agency;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function isRead(): bool
    {
        return $this->readAt instanceof \DateTimeImmutable;
    }

    public function markAsRead(?\DateTimeImmutable $readAt = null): static
    {
        $this->readAt = $readAt ?? new \DateTimeImmutable();

        return $this;
    }

    public function markAsUnread(): static
    {
        $this->readAt = null;

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
}
