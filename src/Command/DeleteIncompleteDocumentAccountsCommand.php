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

namespace App\Command;

use App\Entity\Enum\DocumentSubmissionStatus;
use App\Entity\User;
use App\Repository\Document\RequiredDocumentRepository;
use App\Repository\Document\UserDocumentSubmissionRepository;
use App\Repository\UserRepository;
use App\Service\Document\ClientDocumentNotificationMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:documents:delete-incomplete-accounts',
    description: 'Envoie les rappels et supprime les agences sans documents obligatoires transmis après 60 jours.'
)]
final class DeleteIncompleteDocumentAccountsCommand extends Command
{
    private const DELETE_AFTER_DAYS = 60;

    /**
     * @var list<int>
     */
    private const WARNING_DAYS_BEFORE_DELETION = [
        30,
        15,
        5,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly RequiredDocumentRepository $requiredDocumentRepository,
        private readonly UserDocumentSubmissionRepository $documentSubmissionRepository,
        private readonly ClientDocumentNotificationMailer $clientDocumentNotificationMailer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Affiche les comptes concernés sans envoyer de mail ni supprimer de compte.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $now = new \DateTimeImmutable();

        $requiredDocumentCount = $this->requiredDocumentRepository->countEnabledRequiredDocuments();

        if (0 === $requiredDocumentCount) {
            $io->warning('Aucun document obligatoire actif n’est configuré. Aucun compte ne sera traité.');

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $io->note('Mode dry-run actif : aucun mail ne sera envoyé et aucun compte ne sera supprimé.');
        }

        try {
            $accountsToDelete = $this->deleteExpiredAccounts($now, $dryRun, $io);
            $warningsToSend = $this->sendDueWarnings($now, $dryRun, $io);
        } catch (\Throwable $exception) {
            $io->error('Erreur pendant le traitement : '.$exception->getMessage());

            return Command::FAILURE;
        }

        if ($dryRun) {
            $io->success(\sprintf(
                '%d compte(s) seraient supprimé(s), %d rappel(s) seraient envoyé(s).',
                $accountsToDelete,
                $warningsToSend,
            ));

            return Command::SUCCESS;
        }

        $io->success(\sprintf(
            '%d compte(s) supprimé(s), %d rappel(s) envoyé(s).',
            $accountsToDelete,
            $warningsToSend,
        ));

        return Command::SUCCESS;
    }

    private function deleteExpiredAccounts(
        \DateTimeImmutable $now,
        bool $dryRun,
        SymfonyStyle $io,
    ): int {
        $cutoff = $this->subDays($now, self::DELETE_AFTER_DAYS);
        $users = $this->userRepository->findAgenciesExpiredForMissingDocuments($cutoff);
        $deletedCount = 0;

        foreach ($users as $user) {
            if ($this->hasTransmittedRequiredDocuments($user)) {
                continue;
            }

            ++$deletedCount;
            $this->writeAccountLine($io, $dryRun ? 'A supprimer' : 'Suppression', $user);

            if (!$dryRun) {
                $this->entityManager->remove($user);
            }
        }

        if (!$dryRun && $deletedCount > 0) {
            $this->entityManager->flush();
        }

        return $deletedCount;
    }

    private function sendDueWarnings(
        \DateTimeImmutable $now,
        bool $dryRun,
        SymfonyStyle $io,
    ): int {
        $sentCount = 0;

        foreach (self::WARNING_DAYS_BEFORE_DELETION as $index => $daysBeforeDeletion) {
            $warningAgeInDays = self::DELETE_AFTER_DAYS - $daysBeforeDeletion;
            $nextWarningDaysBeforeDeletion = self::WARNING_DAYS_BEFORE_DELETION[$index + 1] ?? 0;
            $windowEndAgeInDays = self::DELETE_AFTER_DAYS - $nextWarningDaysBeforeDeletion;
            $createdAtOrBefore = $this->subDays($now, $warningAgeInDays);
            $createdAtAfter = $this->subDays($now, $windowEndAgeInDays);
            $users = $this->userRepository->findAgenciesForDocumentDeletionWarning(
                $createdAtOrBefore,
                $createdAtAfter,
                $daysBeforeDeletion,
            );

            foreach ($users as $user) {
                if ($this->hasTransmittedRequiredDocuments($user)) {
                    continue;
                }

                ++$sentCount;
                $this->writeAccountLine(
                    $io,
                    $dryRun ? \sprintf('Rappel J-%d à envoyer', $daysBeforeDeletion) : \sprintf('Rappel J-%d', $daysBeforeDeletion),
                    $user,
                );

                if ($dryRun) {
                    continue;
                }

                $deletionDate = $this->deletionDateFor($user);

                if (!$this->clientDocumentNotificationMailer->sendAccountDeletionWarning($user, $deletionDate, $daysBeforeDeletion)) {
                    --$sentCount;

                    continue;
                }

                $this->markDeletionWarningAsSent($user, $daysBeforeDeletion, $now);
            }
        }

        if (!$dryRun && $sentCount > 0) {
            $this->entityManager->flush();
        }

        return $sentCount;
    }

    private function hasTransmittedRequiredDocuments(User $user): bool
    {
        return $this->documentSubmissionRepository->hasLatestSubmissionForEveryRequiredDocumentWithStatus(
            $user,
            [
                DocumentSubmissionStatus::PENDING,
                DocumentSubmissionStatus::APPROVED,
            ],
        );
    }

    private function deletionDateFor(User $user): \DateTimeImmutable
    {
        $createdAt = $user->getCreatedAt();

        if (!$createdAt instanceof \DateTimeImmutable) {
            return new \DateTimeImmutable();
        }

        return $createdAt->add(new \DateInterval('P'.self::DELETE_AFTER_DAYS.'D'));
    }

    private function markDeletionWarningAsSent(
        User $user,
        int $daysBeforeDeletion,
        \DateTimeImmutable $sentAt,
    ): void {
        match ($daysBeforeDeletion) {
            30 => $user->setDocumentDeletionWarningThirtyDaysSentAt($sentAt),
            15 => $user->setDocumentDeletionWarningFifteenDaysSentAt($sentAt),
            5 => $user->setDocumentDeletionWarningFiveDaysSentAt($sentAt),
            default => throw new \InvalidArgumentException(\sprintf('Unsupported warning delay "%d".', $daysBeforeDeletion)),
        };
    }

    private function subDays(\DateTimeImmutable $date, int $days): \DateTimeImmutable
    {
        return $date->sub(new \DateInterval('P'.$days.'D'));
    }

    private function writeAccountLine(SymfonyStyle $io, string $label, User $user): void
    {
        $io->writeln(\sprintf(
            '%s : #%s %s, créé le %s',
            $label,
            $user->getId() ?? 'nouveau',
            $user->getEmail() ?? 'email non renseigné',
            $user->getCreatedAt()?->format('d/m/Y H:i') ?? 'date inconnue',
        ));
    }
}
