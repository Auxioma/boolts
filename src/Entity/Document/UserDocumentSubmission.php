<?php

namespace App\Entity\Document;

use App\Entity\User;
use App\Entity\Enum\DocumentSubmissionStatus;
use App\Repository\Document\UserDocumentSubmissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDocumentSubmissionRepository::class)]
#[ORM\Table(name: 'user_document_submission')]
#[ORM\UniqueConstraint(
    name: 'uniq_document_attempt',
    columns: ['document_request_id', 'attempt_number']
)]
class UserDocumentSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'submissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?UserDocumentRequest $documentRequest = null;

    #[ORM\Column(length: 255)]
    private string $fileName;

    #[ORM\Column(length: 255)]
    private string $originalFileName;

    #[ORM\Column(length: 150)]
    private string $mimeType;

    #[ORM\Column]
    private int $fileSize;

    #[ORM\Column(length: 500)]
    private string $storagePath;

    #[ORM\Column(length: 64)]
    private string $checksum;

    #[ORM\Column]
    private int $attemptNumber;

    #[ORM\Column(
        enumType: DocumentSubmissionStatus::class,
        options: ['default' => 'pending']
    )]
    private DocumentSubmissionStatus $status =
        DocumentSubmissionStatus::PENDING;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $reviewedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $submittedAt;

    public function __construct()
    {
        $this->submittedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentRequest(): ?UserDocumentRequest
    {
        return $this->documentRequest;
    }

    public function setDocumentRequest(
        ?UserDocumentRequest $documentRequest
    ): static {
        $this->documentRequest = $documentRequest;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getOriginalFileName(): string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): static
    {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): static
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    public function getChecksum(): string
    {
        return $this->checksum;
    }

    public function setChecksum(string $checksum): static
    {
        $this->checksum = $checksum;

        return $this;
    }

    public function getAttemptNumber(): int
    {
        return $this->attemptNumber;
    }

    public function setAttemptNumber(int $attemptNumber): static
    {
        $this->attemptNumber = $attemptNumber;

        return $this;
    }

    public function getStatus(): DocumentSubmissionStatus
    {
        return $this->status;
    }

    public function approve(User $administrator): static
    {
        $this->status = DocumentSubmissionStatus::APPROVED;
        $this->reviewedBy = $administrator;
        $this->reviewedAt = new \DateTimeImmutable();
        $this->rejectionReason = null;

        return $this;
    }

    public function reject(
        User $administrator,
        string $reason
    ): static {
        $this->status = DocumentSubmissionStatus::REJECTED;
        $this->reviewedBy = $administrator;
        $this->reviewedAt = new \DateTimeImmutable();
        $this->rejectionReason = trim($reason);

        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function getSubmittedAt(): \DateTimeImmutable
    {
        return $this->submittedAt;
    }
}
