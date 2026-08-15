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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ClientDocumentNotificationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private string $mailerFromEmail,
        private string $mailerFromName,
    ) {
    }

    public function sendApprovedDocumentNotification(
        User $client,
        UserDocumentSubmission $submission,
    ): void {
        $this->sendDocumentNotification(
            client: $client,
            submission: $submission,
            subject: 'Vos documents ont été validés',
            template: 'email/document/valider.html.twig',
            failureMessage: 'Document approval notification could not be sent.',
            emptyEmailMessage: 'Document approval notification skipped because client email is empty.',
        );
    }

    public function sendRejectedDocumentNotification(
        User $client,
        UserDocumentSubmission $submission,
    ): void {
        $this->sendDocumentNotification(
            client: $client,
            submission: $submission,
            subject: 'Un ou plusieurs de vos documents n’ont pas été acceptés',
            template: 'email/document/refus.html.twig',
            failureMessage: 'Document rejection notification could not be sent.',
            emptyEmailMessage: 'Document rejection notification skipped because client email is empty.',
        );
    }

    private function sendDocumentNotification(
        User $client,
        UserDocumentSubmission $submission,
        string $subject,
        string $template,
        string $failureMessage,
        string $emptyEmailMessage,
    ): void {
        $recipientEmail = mb_trim((string) $client->getEmail());

        if ('' === $recipientEmail) {
            $this->logger->warning($emptyEmailMessage, [
                'clientId' => $client->getId(),
                'submissionId' => $submission->getId(),
            ]);

            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($recipientEmail, $this->clientName($client)))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'user' => $client,
                'agency' => $client,
                'agenceName' => $this->clientName($client),
                'submission' => $submission,
                'documentRequest' => $submission->getDocumentRequest(),
                'dashboardUrl' => $this->urlGenerator->generate(
                    'agence_immobiliere_dashboard',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'registrationDate' => $client->getCreatedAt()?->format('d/m/Y'),
                'rejectionReason' => $submission->getRejectionReason(),
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error($failureMessage, [
                'exception' => $exception,
                'clientId' => $client->getId(),
                'submissionId' => $submission->getId(),
            ]);
        }
    }

    private function clientName(User $client): string
    {
        $company = mb_trim((string) $client->getEntreprise());

        if ('' !== $company) {
            return $company;
        }

        $fullName = mb_trim(sprintf(
            '%s %s',
            (string) $client->getPrenom(),
            (string) $client->getNom(),
        ));

        if ('' !== $fullName) {
            return $fullName;
        }

        return mb_trim((string) $client->getEmail());
    }
}
