<?php
// src/Mailer/TwoFactorEmailMailer.php
namespace App\Mailer;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class TwoFactorEmailMailer implements AuthCodeMailerInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {}

    /**
     * Le bundle te passe $user (qui implémente EmailTwoFactorInterface).
     * Le code est injecté dans le contexte Twig via l’event interne du bundle.
     */
    public function sendAuthCode($user): void
    {
        if (!method_exists($user, 'getEmailAuthCode')) {
            throw new \LogicException('User must have getEmailAuthCode() (implement EmailAuthCodeInterface).');
        }

        $authCode = (string) $user->getEmailAuthCode();
        $toEmail = method_exists($user, 'getEmail') ? (string) $user->getEmail() : null;

        if (!$toEmail) {
            throw new \LogicException('User has no email.');
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($toEmail)
            ->subject('Votre code de connexion sécurisé - Boolts')
            // Ce template reçoit "authenticationCode" dans son contexte
            // conformément au provider email officiel.
            // Doc: providers/email.html -> templating
            ->htmlTemplate('email/authentification/sendcode.html.twig')
            ->context([
                'user' => $user,
                'authCode' => $authCode,
                // Le provider injecte "authenticationCode" si tu utilises
                // le moteur par défaut. Avec un mailer custom, selon la doc,
                // tu peux le récupérer via un Code Generator custom;
                // mais le provider standard expose le code au template.
                // On le référence sans le forcer ici:
                // 'authenticationCode' => '...'
            ]);

        $this->mailer->send($email);
    }
}
