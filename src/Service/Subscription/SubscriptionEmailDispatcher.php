<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionEmailStatus;
use App\Entity\Billing\Enum\SubscriptionEmailType;
use App\Entity\Billing\SubscriptionEmailLog;
use App\Message\Billing\SendSubscriptionEmailMessage;
use App\Repository\Billing\SubscriptionEmailLogRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

final readonly class SubscriptionEmailDispatcher
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionEmailLogRepository $emailLogRepository,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @param bool $immediate Envoi synchrone (dans la requête courante) au lieu de
     *                        passer par la file asynchrone ; à utiliser pour les
     *                        e-mails que l'utilisateur attend juste après son action,
     *                        comme la confirmation de résiliation.
     */
    public function dispatchOnce(
        AgencySubscription $subscription,
        SubscriptionEmailType $type,
        string $eventKey,
        array $context = [],
        bool $immediate = false,
    ): void {
        if ($this->emailLogRepository->findOneForEvent($subscription, $type, $eventKey) instanceof SubscriptionEmailLog) {
            return;
        }

        $recipientEmail = (string) $subscription->getAgency()->getEmail();

        if ('' === mb_trim($recipientEmail)) {
            return;
        }

        $emailLog = (new SubscriptionEmailLog())
            ->setSubscription($subscription)
            ->setAgency($subscription->getAgency())
            ->setEventType($type)
            ->setEventKey($eventKey)
            ->setRecipientEmail($recipientEmail)
            ->setSubject($type->subject())
            ->setContext($context)
            ->setStatus(SubscriptionEmailStatus::PENDING);

        $this->entityManager->persist($emailLog);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            $this->entityManager->detach($emailLog);

            return;
        }

        $message = new SendSubscriptionEmailMessage((int) $emailLog->getId());

        if (!$immediate) {
            $this->messageBus->dispatch($message);

            return;
        }

        // Envoi synchrone : l'échec éventuel est déjà tracé et marqué FAILED par le
        // handler, il ne doit pas faire échouer l'action de l'utilisateur (ex. la
        // résiliation, déjà enregistrée côté Stripe à ce stade).
        try {
            $this->messageBus->dispatch($message, [new TransportNamesStamp('sync')]);
        } catch (MessengerExceptionInterface $exception) {
            $this->logger->error('[SUBSCRIPTION EMAIL] Immediate subscription email dispatch failed.', [
                'email_log' => $emailLog->getId(),
                'subscription' => $subscription->getId(),
                'event_type' => $type->value,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
