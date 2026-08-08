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

namespace App\Service\ContactForm;

use App\Entity\FormContact\Contact;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class ContactMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $mailerFromEmail,
        private readonly string $mailerFromName,
    ) {
    }

    public function sendContactMessage(Contact $contact, string $agencyEmail): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to(new Address($agencyEmail))
            ->replyTo(new Address($contact->getEmail()))
            ->subject('Nouvelle demande de contact')
            ->htmlTemplate('email/contact_form/contact.html.twig')
            ->context([
                'contact' => $contact,
            ]);

        $this->mailer->send($email);
    }
}
