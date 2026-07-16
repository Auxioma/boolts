<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency
 * pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency
 * et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation
 * sans autorisation préalable est interdite.
 */

namespace App\Entity;

use App\Repository\PropertyViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropertyViewRepository::class)]
#[ORM\Table(name: 'property_view')]
#[ORM\Index(
    name: 'idx_property_view_property',
    columns: ['property_id']
)]
#[ORM\Index(
    name: 'idx_property_view_user',
    columns: ['user_id']
)]
#[ORM\Index(
    name: 'idx_property_view_viewed_at',
    columns: ['viewed_at']
)]
#[ORM\Index(
    name: 'idx_property_view_property_date',
    columns: ['property_id', 'viewed_at']
)]
#[ORM\UniqueConstraint(
    name: 'uniq_property_view_key',
    columns: ['view_key']
)]
class PropertyView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Bien immobilier consulté.
     */
    #[ORM\ManyToOne(
        targetEntity: Property::class,
        inversedBy: 'propertyViews'
    )]
    #[ORM\JoinColumn(
        name: 'property_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private ?Property $property = null;

    /**
     * Utilisateur connecté ayant consulté le bien.
     *
     * Cette valeur reste nulle pour un visiteur anonyme.
     */
    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'propertyViews'
    )]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?User $user = null;

    /**
     * Clé unique permettant d'éviter les vues en double.
     *
     * Exemple :
     * une vue par annonce, visiteur et journée.
     */
    #[ORM\Column(
        name: 'view_key',
        length: 64,
        unique: true
    )]
    private ?string $viewKey = null;

    /**
     * Empreinte hachée du visiteur.
     *
     * L'adresse IP ne doit pas être stockée en clair.
     */
    #[ORM\Column(
        name: 'visitor_hash',
        length: 64
    )]
    private ?string $visitorHash = null;

    /**
     * Date et heure de consultation.
     */
    #[ORM\Column(name: 'viewed_at')]
    private \DateTimeImmutable $viewedAt;

    public function __construct()
    {
        $this->viewedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(?Property $property): static
    {
        $this->property = $property;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getViewKey(): ?string
    {
        return $this->viewKey;
    }

    public function setViewKey(string $viewKey): static
    {
        $this->viewKey = $viewKey;

        return $this;
    }

    public function getVisitorHash(): ?string
    {
        return $this->visitorHash;
    }

    public function setVisitorHash(string $visitorHash): static
    {
        $this->visitorHash = $visitorHash;

        return $this;
    }

    public function getViewedAt(): \DateTimeImmutable
    {
        return $this->viewedAt;
    }

    public function setViewedAt(
        \DateTimeImmutable $viewedAt
    ): static {
        $this->viewedAt = $viewedAt;

        return $this;
    }
}