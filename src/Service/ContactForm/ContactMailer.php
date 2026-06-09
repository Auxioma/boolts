<?php

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
