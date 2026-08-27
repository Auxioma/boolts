<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Subscription\SubscriptionProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

#[AsCommand(
    name: 'app:subscriptions:process',
    description: 'Synchronise Stripe et traite les renouvellements, relances et résiliations d’abonnements.',
)]
final class ProcessSubscriptionsCommand extends Command
{
    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly SubscriptionProcessor $subscriptionProcessor,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->lockFactory->createLock('subscriptions_process', 900.0);

        if (!$lock->acquire()) {
            $this->logger->info('[SUBSCRIPTION CRON] Another subscription process is already running.');
            $output->writeln('<comment>Traitement déjà en cours, arrêt propre.</comment>');

            return Command::SUCCESS;
        }

        try {
            $this->logger->info('[SUBSCRIPTION CRON] Subscription processing started.');
            $this->subscriptionProcessor->process();
            $this->logger->info('[SUBSCRIPTION CRON] Subscription processing finished.');
        } finally {
            $lock->release();
        }

        return Command::SUCCESS;
    }
}
