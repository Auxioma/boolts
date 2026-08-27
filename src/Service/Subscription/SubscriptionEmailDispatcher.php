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
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SubscriptionEmailDispatcher
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionEmailLogRepository $emailLogRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function dispatchOnce(
        AgencySubscription $subscription,
        SubscriptionEmailType $type,
        string $eventKey,
        array $context = [],
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

        $this->messageBus->dispatch(new SendSubscriptionEmailMessage((int) $emailLog->getId()));
    }
}
