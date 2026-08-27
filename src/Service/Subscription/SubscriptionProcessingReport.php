<?php

declare(strict_types=1);

namespace App\Service\Subscription;

use App\Entity\Billing\AgencySubscription;

final class SubscriptionProcessingReport
{
    /**
     * @var array<string, array{candidates: int, succeeded: int, failed: int, skipped: int}>
     */
    private array $phases = [];

    /**
     * @var list<array{
     *     action: string,
     *     result: string,
     *     subscription: int,
     *     agency: int,
     *     plan: string,
     *     status: string,
     *     providerSubscription: string,
     *     periodEnd: string,
     *     paymentFailures: int,
     *     detail: string
     * }>
     */
    private array $entries = [];

    public function __construct(
        private readonly \DateTimeImmutable $startedAt,
        private readonly int $batchSize,
    ) {
    }

    public function startPhase(string $action, int $candidateCount): void
    {
        $this->phases[$action] = [
            'candidates' => $candidateCount,
            'succeeded' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
    }

    public function succeeded(string $action, AgencySubscription $subscription): void
    {
        ++$this->phases[$action]['succeeded'];
        $this->entries[] = $this->createEntry($action, 'SUCCESS', $subscription, 'Traitement terminé.');
    }

    public function failed(
        string $action,
        AgencySubscription $subscription,
        \Throwable $exception,
    ): void {
        ++$this->phases[$action]['failed'];
        $this->entries[] = $this->createEntry(
            $action,
            'ERROR',
            $subscription,
            $exception->getMessage(),
        );
    }

    public function skipped(
        string $action,
        AgencySubscription $subscription,
        string $reason,
    ): void {
        ++$this->phases[$action]['skipped'];
        $this->entries[] = $this->createEntry($action, 'SKIPPED', $subscription, $reason);
    }

    /**
     * @return array<string, array{candidates: int, succeeded: int, failed: int, skipped: int}>
     */
    public function phases(): array
    {
        return $this->phases;
    }

    /**
     * @return list<array{
     *     action: string,
     *     result: string,
     *     subscription: int,
     *     agency: int,
     *     plan: string,
     *     status: string,
     *     providerSubscription: string,
     *     periodEnd: string,
     *     paymentFailures: int,
     *     detail: string
     * }>
     */
    public function entries(): array
    {
        return $this->entries;
    }

    public function startedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function batchSize(): int
    {
        return $this->batchSize;
    }

    public function candidateCount(): int
    {
        return array_sum(array_column($this->phases, 'candidates'));
    }

    public function succeededCount(): int
    {
        return array_sum(array_column($this->phases, 'succeeded'));
    }

    public function failedCount(): int
    {
        return array_sum(array_column($this->phases, 'failed'));
    }

    public function skippedCount(): int
    {
        return array_sum(array_column($this->phases, 'skipped'));
    }

    /**
     * @return array{
     *     action: string,
     *     result: string,
     *     subscription: int,
     *     agency: int,
     *     plan: string,
     *     status: string,
     *     providerSubscription: string,
     *     periodEnd: string,
     *     paymentFailures: int,
     *     detail: string
     * }
     */
    private function createEntry(
        string $action,
        string $result,
        AgencySubscription $subscription,
        string $detail,
    ): array {
        return [
            'action' => $action,
            'result' => $result,
            'subscription' => (int) $subscription->getId(),
            'agency' => (int) $subscription->getAgency()->getId(),
            'plan' => $subscription->getPlan()->getName(),
            'status' => $subscription->getStatus()->value,
            'providerSubscription' => $subscription->getProviderSubscriptionId() ?? '-',
            'periodEnd' => $subscription->getCurrentPeriodEnd()?->format('Y-m-d H:i:s') ?? '-',
            'paymentFailures' => $subscription->getPaymentFailureCount(),
            'detail' => $detail,
        ];
    }
}
