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

namespace App\Service\Document;

use App\Entity\Document\UserDocumentSubmission;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class AdminDocumentNotificationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $projectDir,
        private string $adminEmail,
        private string $adminName,
        private string $mailerFromEmail,
        private string $mailerFromName,
    ) {
    }

    /**
     * @param list<UserDocumentSubmission> $submissions
     */
    public function sendPendingDocumentNotification(User $agency, array $submissions): void
    {
        if ([] === $submissions) {
            return;
        }

        $adminEmail = mb_trim($this->adminEmail);

        if ('' === $adminEmail) {
            $this->logger->warning('Document admin notification skipped because ADMIN_EMAIL is empty.', [
                'agencyId' => $agency->getId(),
            ]);

            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($adminEmail, $this->adminName))
            ->subject('Nouveaux documents en attente de validation')
            ->htmlTemplate('email/document/admin_pending_documents.html.twig')
            ->context([
                'admin_name' => $this->adminName,
                'agency' => $agency,
                'submissions' => $submissions,
            ]);

        foreach ($submissions as $submission) {
            $attachmentPath = $this->documentAttachmentPath($submission);

            if (null === $attachmentPath) {
                continue;
            }

            $email->attachFromPath(
                $attachmentPath,
                $submission->getOriginalFileName(),
                $submission->getMimeType(),
            );
        }

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error('Document admin notification could not be sent.', [
                'exception' => $exception,
                'submissionIds' => array_map(
                    static fn (UserDocumentSubmission $submission): ?int => $submission->getId(),
                    $submissions,
                ),
                'agencyId' => $agency->getId(),
            ]);
        }
    }

    private function documentAttachmentPath(UserDocumentSubmission $submission): ?string
    {
        $storagePath = str_replace('\\', '/', $submission->getStoragePath());
        $storagePath = mb_ltrim($storagePath, '/');

        if (!str_starts_with($storagePath, 'uploads/document/')) {
            $this->logger->warning('Document admin notification attachment skipped because path is outside document uploads.', [
                'submissionId' => $submission->getId(),
                'storagePath' => $submission->getStoragePath(),
            ]);

            return null;
        }

        $attachmentPath = $this->projectDir.'/public/'.$storagePath;

        if (!is_file($attachmentPath) || !is_readable($attachmentPath)) {
            $this->logger->warning('Document admin notification attachment skipped because file is missing or unreadable.', [
                'submissionId' => $submission->getId(),
                'attachmentPath' => $attachmentPath,
            ]);

            return null;
        }

        return $attachmentPath;
    }
}
