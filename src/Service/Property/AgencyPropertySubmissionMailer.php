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

use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * E-mails transactionnels envoyés à l'agence propriétaire tout au long du
 * cycle de vie d'une annonce : soumission, publication, boost, mise en
 * pause, réactivation et suppression.
 */
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

    /**
     * Annonce soumise : elle quitte le brouillon et attend la validation
     * d'un administrateur.
     */
    public function sendSubmissionPendingNotification(User $agency, Property $property): void
    {
        $this->dispatch(
            $agency,
            $property,
            'Votre annonce a été envoyée pour validation',
            'email/property/submission_pending.html.twig',
            'submission',
        );
    }

    /**
     * Annonce publiée : statut passé à « Publiée » depuis le back-office.
     */
    public function sendPublicationNotification(User $agency, Property $property): void
    {
        $this->dispatch(
            $agency,
            $property,
            'Votre annonce est publiée',
            'email/property/published.html.twig',
            'publication',
            ['publicUrl' => $this->publicUrl($property)],
        );
    }

    /**
     * Boost activé sur l'annonce (l'annonce en pause est republiée au passage).
     */
    public function sendBoostActivatedNotification(
        User $agency,
        Property $property,
        \DateTimeInterface $boostEndsAt,
    ): void {
        $this->dispatch(
            $agency,
            $property,
            'Le boost de votre annonce est activé',
            'email/property/boost_activated.html.twig',
            'boost activation',
            [
                'boostEndsAt' => $boostEndsAt,
                'publicUrl' => $this->publicUrl($property),
            ],
        );
    }

    /**
     * Annonce mise en pause (dépubliée) par l'agence.
     */
    public function sendPausedNotification(User $agency, Property $property): void
    {
        $this->dispatch(
            $agency,
            $property,
            'Votre annonce a été mise en pause',
            'email/property/paused.html.twig',
            'pause',
        );
    }

    /**
     * Annonce réactivée (republiée) par l'agence.
     */
    public function sendReactivatedNotification(User $agency, Property $property): void
    {
        $this->dispatch(
            $agency,
            $property,
            'Votre annonce a été réactivée',
            'email/property/reactivated.html.twig',
            'reactivation',
            ['publicUrl' => $this->publicUrl($property)],
        );
    }

    /**
     * Annonce supprimée par l'agence.
     */
    public function sendDeletedNotification(User $agency, Property $property): void
    {
        $this->dispatch(
            $agency,
            $property,
            'Votre annonce a été supprimée',
            'email/property/deleted.html.twig',
            'deletion',
        );
    }

    /**
     * Aiguille vers l'e-mail correspondant au nouveau statut d'une annonce
     * (mise en pause, réactivation, suppression). Pratique pour les actions
     * groupées où le statut cible est le même pour toutes les annonces.
     */
    public function sendStatusChangeNotification(
        User $agency,
        Property $property,
        StatutAnnonceImmobiliere $statut,
    ): void {
        match ($statut) {
            StatutAnnonceImmobiliere::DEPUBLIEE => $this->sendPausedNotification($agency, $property),
            StatutAnnonceImmobiliere::PUBLIEE => $this->sendReactivatedNotification($agency, $property),
            StatutAnnonceImmobiliere::SUPPRIMEE => $this->sendDeletedNotification($agency, $property),
            default => null,
        };
    }

    /**
     * Construit et envoie l'e-mail à l'agence, en journalisant les cas où il
     * ne peut pas partir (adresse vide, échec du transport).
     *
     * @param array<string, mixed> $extraContext contexte Twig additionnel
     * @param string               $logLabel     libellé utilisé dans les logs
     */
    private function dispatch(
        User $agency,
        Property $property,
        string $subject,
        string $template,
        string $logLabel,
        array $extraContext = [],
    ): void {
        $recipientEmail = mb_trim((string) $agency->getEmail());

        if ('' === $recipientEmail) {
            $this->logger->warning(\sprintf('Property %s notification skipped because agency email is empty.', $logLabel), [
                'agencyId' => $agency->getId(),
                'propertyId' => $property->getId(),
            ]);

            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($recipientEmail, $this->agencyName($agency)))
            ->subject($subject)
            ->htmlTemplate($template)
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
                ...$extraContext,
            ]);

        try {
            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger->error(\sprintf('Property %s notification could not be sent.', $logLabel), [
                'exception' => $exception,
                'agencyId' => $agency->getId(),
                'propertyId' => $property->getId(),
            ]);
        }
    }

    /**
     * URL publique de l'annonce, ou null si aucun slug n'est disponible.
     */
    private function publicUrl(Property $property): ?string
    {
        $slug = mb_trim((string) $property->getSlug());

        if ('' === $slug) {
            return null;
        }

        return $this->urlGenerator->generate(
            'app_public_detail_bien',
            ['slug' => $slug],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
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
