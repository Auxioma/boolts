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

use App\Entity\Traits\CreatedAtTraits;
use App\Entity\User;
use App\Repository\Messagerie\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Message (texte, image et/ou document) d'une conversation.
 *
 * Les images sont publiques (public/messagerie) ; les documents sont privés
 * (var/messagerie_documents) et servis uniquement aux participants.
 */
#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'messagerie_message')]
#[ORM\Index(name: 'IDX_MESSAGE_UNREAD', columns: ['conversation_id', 'read_at'])]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Message
{
    use CreatedAtTraits;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $sender = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[Vich\UploadableField(mapping: 'messagerie', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[Vich\UploadableField(mapping: 'messagerie_documents', fileNameProperty: 'documentName', size: 'documentSize', originalName: 'documentOriginalName')]
    private ?File $documentFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentName = null;

    /** Nom du fichier tel qu'envoyé (affiché et proposé au téléchargement). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentOriginalName = null;

    #[ORM\Column(nullable: true)]
    private ?int $documentSize = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    public function setDocumentFile(?File $documentFile = null): void
    {
        $this->documentFile = $documentFile;
    }

    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    public function setDocumentName(?string $documentName): void
    {
        $this->documentName = $documentName;
    }

    public function getDocumentOriginalName(): ?string
    {
        return $this->documentOriginalName;
    }

    public function setDocumentOriginalName(?string $documentOriginalName): void
    {
        $this->documentOriginalName = $documentOriginalName;
    }

    public function getDocumentSize(): ?int
    {
        return $this->documentSize;
    }

    public function setDocumentSize(?int $documentSize): void
    {
        $this->documentSize = $documentSize;
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
}
