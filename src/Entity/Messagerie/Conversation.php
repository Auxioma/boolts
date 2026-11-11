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

namespace App\Entity\Messagerie;

use App\Entity\Property;
use App\Entity\Traits\CreatedAtTraits;
use App\Entity\User;
use App\Repository\Messagerie\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fil de discussion entre un visiteur et une agence.
 *
 * - Avec un bien : conversation au sujet d'une annonce, une seule par couple (bien, visiteur).
 * - Sans bien : contact direct avec l'agence (page agence), une seule par couple (agence, visiteur).
 */
#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'messagerie_conversation')]
#[ORM\UniqueConstraint(name: 'UNIQ_CONVERSATION_PROPERTY_VISITEUR', columns: ['property_id', 'visiteur_id'])]
#[ORM\Index(name: 'IDX_CONVERSATION_AGENCE_LAST', columns: ['agence_id', 'last_message_at'])]
#[ORM\HasLifecycleCallbacks]
class Conversation
{
    use CreatedAtTraits;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Null pour un contact direct avec l'agence (sans annonce). */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Property $property = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $agence = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $visiteur = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastMessageAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastMessagePreview = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Contact direct avec l'agence (conversation sans annonce).
     */
    public function isDirect(): bool
    {
        return null === $this->property;
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

    public function getAgence(): ?User
    {
        return $this->agence;
    }

    public function setAgence(?User $agence): static
    {
        $this->agence = $agence;

        return $this;
    }

    public function getVisiteur(): ?User
    {
        return $this->visiteur;
    }

    public function setVisiteur(?User $visiteur): static
    {
        $this->visiteur = $visiteur;

        return $this;
    }

    public function getLastMessageAt(): ?\DateTimeImmutable
    {
        return $this->lastMessageAt;
    }

    public function setLastMessageAt(?\DateTimeImmutable $lastMessageAt): static
    {
        $this->lastMessageAt = $lastMessageAt;

        return $this;
    }

    public function getLastMessagePreview(): ?string
    {
        return $this->lastMessagePreview;
    }

    public function setLastMessagePreview(?string $lastMessagePreview): static
    {
        $this->lastMessagePreview = $lastMessagePreview;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function isParticipant(?User $user): bool
    {
        if (null === $user) {
            return false;
        }

        return $user === $this->agence || $user === $this->visiteur;
    }

    /**
     * Interlocuteur de l'utilisateur donné.
     */
    public function getOtherParticipant(User $user): ?User
    {
        return $user === $this->agence ? $this->visiteur : $this->agence;
    }
}
