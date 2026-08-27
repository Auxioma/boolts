<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Subscription\SubscriptionProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
        $io = new SymfonyStyle($input, $output);
        $lock = $this->lockFactory->createLock('subscriptions_process', 900.0);

        $io->title('Traitement automatisé des abonnements Stripe');
        $io->definitionList(
            ['Commande' => $this->getName() ?? 'app:subscriptions:process'],
            ['Environnement' => (string) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'inconnu')],
            ['Démarrage' => (new \DateTimeImmutable())->format('Y-m-d H:i:s P')],
            ['Verrou' => 'subscriptions_process (expiration : 900 secondes)'],
        );

        if (!$lock->acquire()) {
            $this->logger->info('[SUBSCRIPTION CRON] Another subscription process is already running.');
            $io->warning('Traitement déjà en cours : aucune opération n’a été exécutée.');

            return Command::SUCCESS;
        }

        $startedAt = microtime(true);

        try {
            $this->logger->info('[SUBSCRIPTION CRON] Subscription processing started.');
            $report = $this->subscriptionProcessor->process();
            $this->logger->info('[SUBSCRIPTION CRON] Subscription processing finished.');
        } catch (\Throwable $exception) {
            $this->logger->critical('[SUBSCRIPTION CRON] Subscription processing aborted.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            $io->error('Traitement interrompu : '.$exception->getMessage());

            return Command::FAILURE;
        } finally {
            $lock->release();
        }

        $phaseLabels = [
            'ACTIVE_RENEWAL' => 'Renouvellements actifs',
            'PAYMENT_RETRY' => 'Relances de paiement',
            'PAYMENT_FAILURE_FINALIZATION' => 'Échecs de paiement définitifs',
            'CANCELLATION_FINALIZATION' => 'Résiliations arrivées à échéance',
            'STRIPE_SYNCHRONIZATION' => 'Synchronisations Stripe',
        ];

        $phaseRows = [];

        foreach ($report->phases() as $action => $counts) {
            $phaseRows[] = [
                $phaseLabels[$action] ?? $action,
                $counts['candidates'],
                $counts['succeeded'],
                $counts['skipped'],
                $counts['failed'],
            ];
        }

        $io->section('Bilan par étape');
        $io->table(
            ['Étape', 'Candidats', 'Succès', 'Ignorés', 'Erreurs'],
            $phaseRows,
        );

        $io->section('Détail des abonnements');

        if ([] === $report->entries()) {
            $io->writeln('Aucun abonnement ne nécessitait de traitement.');
        } else {
            $entryRows = [];

            foreach ($report->entries() as $entry) {
                $entryRows[] = [
                    $phaseLabels[$entry['action']] ?? $entry['action'],
                    $entry['result'],
                    $entry['subscription'],
                    $entry['agency'],
                    $entry['plan'],
                    $entry['status'],
                    $entry['providerSubscription'],
                    $entry['periodEnd'],
                    $entry['paymentFailures'],
                    $entry['detail'],
                ];
            }

            $io->table(
                [
                    'Étape',
                    'Résultat',
                    'Abonnement',
                    'Agence',
                    'Forfait',
                    'Statut',
                    'ID Stripe',
                    'Fin de période',
                    'Échecs',
                    'Détail',
                ],
                $entryRows,
            );
        }

        $duration = microtime(true) - $startedAt;
        $io->definitionList(
            ['Taille maximale par lot et par étape' => $report->batchSize()],
            ['Candidats' => $report->candidateCount()],
            ['Succès' => $report->succeededCount()],
            ['Ignorés' => $report->skippedCount()],
            ['Erreurs' => $report->failedCount()],
            ['Durée' => \sprintf('%.3f seconde(s)', $duration)],
            ['Fin' => (new \DateTimeImmutable())->format('Y-m-d H:i:s P')],
            ['Journal' => 'var/log/'.($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev').'.subscription.log'],
        );

        if ($report->failedCount() > 0) {
            $io->error(\sprintf(
                'Traitement terminé avec %d erreur(s). Consultez le détail ci-dessus et le journal.',
                $report->failedCount(),
            ));

            return Command::FAILURE;
        }

        $io->success('Traitement des abonnements terminé sans erreur.');

        return Command::SUCCESS;
    }
}
