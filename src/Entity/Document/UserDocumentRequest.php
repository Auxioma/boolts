<?php

namespace App\Entity\Document;

use App\Entity\User;
use App\Entity\Enum\DocumentRequestStatus;
use App\Repository\Document\UserDocumentRequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDocumentRequestRepository::class)]
#[ORM\Table(name: 'user_document_request')]
#[ORM\UniqueConstraint(
    name: 'uniq_user_required_document',
    columns: ['user_id', 'required_document_id']
)]
class UserDocumentRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documentRequests')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?RequiredDocument $requiredDocument = null;

    #[ORM\Column(
        enumType: DocumentRequestStatus::class,
        options: ['default' => 'waiting_upload']
    )]
    private DocumentRequestStatus $status =
        DocumentRequestStatus::WAITING_UPLOAD;

    #[ORM\Column]
    private bool $blocked = false;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $blockedReason = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $blockedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, UserDocumentSubmission>
     */
    #[ORM\OneToMany(
        mappedBy: 'documentRequest',
        targetEntity: UserDocumentSubmission::class,
        cascade: ['persist'],
        orphanRemoval: false
    )]
    #[ORM\OrderBy(['attemptNumber' => 'DESC'])]
    private Collection $submissions;

    public function __construct()
    {
        $this->submissions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRequiredDocument(): ?RequiredDocument
    {
        return $this->requiredDocument;
    }

    public function setRequiredDocument(
        ?RequiredDocument $requiredDocument
    ): static {
        $this->requiredDocument = $requiredDocument;

        return $this;
    }

    public function getStatus(): DocumentRequestStatus
    {
        return $this->status;
    }

    public function setStatus(DocumentRequestStatus $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    public function block(?string $reason = null): static
    {
        $this->blocked = true;
        $this->blockedReason = $reason;
        $this->blockedAt = new \DateTimeImmutable();
        $this->status = DocumentRequestStatus::BLOCKED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function unblock(): static
    {
        $this->blocked = false;
        $this->blockedReason = null;
        $this->blockedAt = null;
        $this->status = DocumentRequestStatus::WAITING_UPLOAD;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getBlockedReason(): ?string
    {
        return $this->blockedReason;
    }

    public function getBlockedAt(): ?\DateTimeImmutable
    {
        return $this->blockedAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function markAsCompleted(): static
    {
        $this->status = DocumentRequestStatus::APPROVED;
        $this->completedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return Collection<int, UserDocumentSubmission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function addSubmission(
        UserDocumentSubmission $submission
    ): static {
        if (!$this->submissions->contains($submission)) {
            $this->submissions->add($submission);
            $submission->setDocumentRequest($this);
        }

        return $this;
    }

    public function getSubmissionCount(): int
    {
        return $this->submissions->count();
    }

    public function getRemainingSubmissionCount(): int
    {
        $maximum = $this->requiredDocument?->getMaxSubmissions() ?? 5;

        return max(0, $maximum - $this->getSubmissionCount());
    }

    public function canSubmit(): bool
    {
        if ($this->blocked) {
            return false;
        }

        if ($this->status === DocumentRequestStatus::APPROVED) {
            return false;
        }

        $maximum = $this->requiredDocument?->getMaxSubmissions() ?? 5;

        return $this->getSubmissionCount() < $maximum;
    }

    public function getLatestSubmission(): ?UserDocumentSubmission
    {
        $latestSubmission = null;

        foreach ($this->submissions as $submission) {
            if (
                $latestSubmission === null
                || $submission->getAttemptNumber()
                > $latestSubmission->getAttemptNumber()
            ) {
                $latestSubmission = $submission;
            }
        }

        return $latestSubmission;
    }
}
