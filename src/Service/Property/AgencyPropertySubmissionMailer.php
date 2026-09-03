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

namespace App\Service\Property;

use App\Entity\Property;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class AgencyPropertySubmissionMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private string $mailerFromEmail,
        private string $mailerFromName,
    ) {
    }

    public function sendSubmissionPendingNotification(
        User $agency,
        Property $property,
    ): void {
        $recipientEmail = mb_trim((string) $agency->getEmail());

        if ('' === $recipientEmail) {
            $this->logger->warning('Property submission notification skipped because agency email is empty.', [
                'agencyId' => $agency->getId(),
                'propertyId' => $property->getId(),
            ]);

            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($recipientEmail, $this->agencyName($agency)))
            ->subject('Votre annonce a été envoyée pour validation')
            ->htmlTemplate('email/property/submission_pending.html.twig')
            ->context([
                'agency' => $agency,
                'agencyName' => $this->agencyName($agency),
                'property' => $property,
                'propertyTitle' => $this->propertyTitle($property),
                'mesBiensUrl' => $this->urlGenerator->generate(
                    'agence_immobiliere_mes_biens_list',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error('Property submission notification could not be sent.', [
                'exception' => $exception,
                'agencyId' => $agency->getId(),
                'propertyId' => $property->getId(),
            ]);
        }
    }

    private function agencyName(User $agency): string
    {
        $company = mb_trim((string) $agency->getEntreprise());

        if ('' !== $company) {
            return $company;
        }

        $fullName = mb_trim(\sprintf(
            '%s %s',
            (string) $agency->getPrenom(),
            (string) $agency->getNom(),
        ));

        if ('' !== $fullName) {
            return $fullName;
        }

        return mb_trim((string) $agency->getEmail());
    }

    private function propertyTitle(Property $property): string
    {
        $title = mb_trim((string) $property->getTitreDuLogement());

        if ('' !== $title) {
            return $title;
        }

        $parts = array_filter(
            [
                $property->getTypeTransaction()?->getName(),
                $property->getTypeBien()?->getName(),
                $property->getVille(),
            ],
            static fn (?string $value): bool => null !== $value && '' !== mb_trim($value)
        );

        $title = mb_trim(implode(' ', $parts));

        if ('' !== $title) {
            return $title;
        }

        return 'Annonce #'.($property->getId() ?? 'nouvelle');
    }
}
