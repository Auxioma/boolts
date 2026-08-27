<?php

declare(strict_types=1);

namespace App\MessageHandler\Billing;

use App\Entity\Billing\Enum\SubscriptionEmailStatus;
use App\Entity\Billing\SubscriptionEmailLog;
use App\Message\Billing\SendSubscriptionEmailMessage;
use App\Repository\Billing\SubscriptionEmailLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
final readonly class SendSubscriptionEmailMessageHandler
{
    public function __construct(
        private SubscriptionEmailLogRepository $emailLogRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
        private string $mailerFromEmail,
        private string $mailerFromName,
    ) {
    }

    public function __invoke(SendSubscriptionEmailMessage $message): void
    {
        $emailLog = $this->emailLogRepository->find($message->emailLogId);

        if (!$emailLog instanceof SubscriptionEmailLog) {
            return;
        }

        if (SubscriptionEmailStatus::SENT === $emailLog->getStatus()) {
            return;
        }

        $context = [
            ...$emailLog->getContext(),
            'email_log' => $emailLog,
            'subscription' => $emailLog->getSubscription(),
            'agency' => $emailLog->getAgency(),
            'portal_url' => $this->urlGenerator->generate(
                'account_subscription_customer_portal',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
        ];

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to($emailLog->getRecipientEmail())
            ->subject($emailLog->getSubject())
            ->htmlTemplate('emails/subscription/'.$emailLog->getEventType()->value.'.html.twig')
            ->context($context);

        try {
            $this->mailer->send($email);

            $emailLog
                ->setStatus(SubscriptionEmailStatus::SENT)
                ->setSentAt(new \DateTimeImmutable())
                ->setErrorMessage(null);

            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $emailLog
                ->setStatus(SubscriptionEmailStatus::FAILED)
                ->setFailedAt(new \DateTimeImmutable())
                ->setErrorMessage($exception->getMessage());

            $this->entityManager->flush();

            $this->logger->error('[SUBSCRIPTION EMAIL] Subscription email failed.', [
                'email_log' => $emailLog->getId(),
                'subscription' => $emailLog->getSubscription()->getId(),
                'event_type' => $emailLog->getEventType()->value,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
