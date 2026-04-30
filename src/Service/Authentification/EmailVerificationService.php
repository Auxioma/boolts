<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Service\Authentification;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

final readonly class EmailVerificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $em,
    ) {
    }

    public function generateCode(): string
    {
        return mb_str_pad((string) random_int(0, 999999), 6, '0', \STR_PAD_LEFT);
    }

    public function prepare(User $user, int $ttlMinutes = 60): void
    {
        $code = $this->generateCode();

        $user
            ->setEmailAuthCode($code)
            ->setEmailAuthCodeExpiresAt(new \DateTimeImmutable(\sprintf('+%d minutes', $ttlMinutes)))
            ->setEmailAuthCodeRequestedAt(new \DateTimeImmutable())
            ->setFailedVerificationAttempts(0)
        ;

        $this->em->flush();
    }

    public function send(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from('support@boolts.com')
            ->to((string) $user->getEmail())
            ->subject('Votre code de connexion Boolts')
            ->htmlTemplate('email/authentification/opt/opt.html.twig')
            ->context([
                'user' => $user,
                'code' => $user->getEmailAuthCode(),
                'expiresAt' => $user->getEmailAuthCodeExpiresAt(),
            ])
        ;

        $this->mailer->send($email);
    }

    public function verify(User $user, string $submittedCode): bool
    {
        if ('' === mb_trim($submittedCode)) {
            return false;
        }

        if ('' === $user->getEmailAuthCode()) {
            return false;
        }

        if (null === $user->getEmailAuthCodeExpiresAt()) {
            return false;
        }

        if ($user->getEmailAuthCodeExpiresAt() < new \DateTimeImmutable()) {
            return false;
        }

        return hash_equals($user->getEmailAuthCode(), mb_trim($submittedCode));
    }
}
