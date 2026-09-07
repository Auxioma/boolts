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

    /**
     * E-mail unique de synthèse envoyé à l'agence lorsque la revue de ses
     * documents est finalisée depuis l'administration : tous les documents
     * sont validés.
     */
    public function sendApprovedDocumentsReviewSummary(User $client): void
    {
        $this->sendReviewSummaryNotification(
            client: $client,
            subject: 'Vos documents ont été validés',
            template: 'email/document/valider.html.twig',
            failureMessage: 'Document review approval summary could not be sent.',
            emptyEmailMessage: 'Document review approval summary skipped because client email is empty.',
        );
    }

    /**
     * E-mail unique de synthèse envoyé à l'agence lorsque la revue de ses
     * documents est finalisée depuis l'administration : au moins un document
     * a été refusé.
     */
    public function sendRejectedDocumentsReviewSummary(User $client): void
    {
        $this->sendReviewSummaryNotification(
            client: $client,
            subject: 'Un ou plusieurs de vos documents n’ont pas été acceptés',
            template: 'email/document/refus.html.twig',
            failureMessage: 'Document review rejection summary could not be sent.',
            emptyEmailMessage: 'Document review rejection summary skipped because client email is empty.',
        );
    }

    public function sendAccountDeletionWarning(
        User $client,
        \DateTimeImmutable $deletionDate,
        int $daysBeforeDeletion,
    ): bool {
        $recipientEmail = mb_trim((string) $client->getEmail());

        if ('' === $recipientEmail) {
            $this->logger->warning('Document account deletion warning skipped because client email is empty.', [
                'clientId' => $client->getId(),
                'daysBeforeDeletion' => $daysBeforeDeletion,
            ]);

            return false;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($recipientEmail, $this->clientName($client)))
            ->subject(\sprintf(
                'Votre compte Boolts sera supprimé dans %d %s',
                $daysBeforeDeletion,
                1 === $daysBeforeDeletion ? 'jour' : 'jours',
            ))
            ->htmlTemplate('email/document/account_deletion_warning.html.twig')
            ->context([
                'user' => $client,
                'agency' => $client,
                'agenceName' => $this->clientName($client),
                'dashboardUrl' => $this->urlGenerator->generate(
                    'agence_immobiliere_dashboard',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'registrationDate' => $client->getCreatedAt()?->format('d/m/Y'),
                'deletionDate' => $deletionDate,
                'daysBeforeDeletion' => $daysBeforeDeletion,
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error('Document account deletion warning could not be sent.', [
                'exception' => $exception,
                'clientId' => $client->getId(),
                'daysBeforeDeletion' => $daysBeforeDeletion,
            ]);

            return false;
        }

        return true;
    }

    private function sendReviewSummaryNotification(
        User $client,
        string $subject,
        string $template,
        string $failureMessage,
        string $emptyEmailMessage,
    ): void {
        $recipientEmail = mb_trim((string) $client->getEmail());

        if ('' === $recipientEmail) {
            $this->logger->warning($emptyEmailMessage, [
                'clientId' => $client->getId(),
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
                'dashboardUrl' => $this->urlGenerator->generate(
                    'agence_immobiliere_dashboard',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'registrationDate' => $client->getCreatedAt()?->format('d/m/Y'),
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error($failureMessage, [
                'exception' => $exception,
                'clientId' => $client->getId(),
            ]);
        }
    }

    private function clientName(User $client): string
    {
        $company = mb_trim((string) $client->getEntreprise());

        if ('' !== $company) {
            return $company;
        }

        $fullName = mb_trim(\sprintf(
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
