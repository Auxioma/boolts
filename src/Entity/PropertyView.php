<?php

namespace App\Entity;

use App\Repository\PropertyViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropertyViewRepository::class)]
#[ORM\Table(name: 'property_view')]
#[ORM\Index(name: 'idx_property_view_property', columns: ['property_id'])]
#[ORM\Index(name: 'idx_property_view_user', columns: ['user_id'])]
#[ORM\UniqueConstraint(name: 'uniq_property_view_key', columns: ['view_key'])]
class PropertyView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Le bien immobilier consulté.
     */
    #[ORM\ManyToOne(targetEntity: Property::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Property $property = null;

    /**
     * L'utilisateur connecté si disponible.
     * Si le visiteur n'est pas connecté, cette valeur reste null.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Clé unique pour éviter de compter plusieurs fois la même vue.
     * Exemple : 1 vue par bien / visiteur / jour.
     */
    #[ORM\Column(name: 'view_key', length: 64)]
    private ?string $viewKey = null;

    /**
     * Hash du visiteur.
     * On ne stocke pas l'adresse IP en clair.
     */
    #[ORM\Column(name: 'visitor_hash', length: 64)]
    private ?string $visitorHash = null;

    /**
     * Date de consultation du bien.
     */
    #[ORM\Column(name: 'viewed_at')]
    private ?\DateTimeImmutable $viewedAt = null;

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

    public function getViewedAt(): ?\DateTimeImmutable
    {
        return $this->viewedAt;
    }

    public function setViewedAt(\DateTimeImmutable $viewedAt): static
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }
}