<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260831120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add scheduled paid-to-paid plan downgrade fields (Stripe subscription schedule) on agency_subscription.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE agency_subscription ADD provider_schedule_id VARCHAR(255) DEFAULT NULL');
            $this->addSql('ALTER TABLE agency_subscription ADD pending_plan_price_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE agency_subscription ADD pending_plan_change_effective_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->addSql('ALTER TABLE agency_subscription ADD pending_plan_change_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->addSql('ALTER TABLE agency_subscription ADD CONSTRAINT FK_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE FOREIGN KEY (pending_plan_price_id) REFERENCES subscription_plan_price (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->addSql('CREATE INDEX IDX_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE ON agency_subscription (pending_plan_price_id)');

            return;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('ALTER TABLE agency_subscription ADD provider_schedule_id VARCHAR(255) DEFAULT NULL, ADD pending_plan_price_id INT DEFAULT NULL, ADD pending_plan_change_effective_at DATETIME DEFAULT NULL, ADD pending_plan_change_requested_at DATETIME DEFAULT NULL');
            $this->addSql('ALTER TABLE agency_subscription ADD CONSTRAINT FK_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE FOREIGN KEY (pending_plan_price_id) REFERENCES subscription_plan_price (id) ON DELETE SET NULL');
            $this->addSql('CREATE INDEX IDX_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE ON agency_subscription (pending_plan_price_id)');

            return;
        }

        throw new \RuntimeException('Unsupported platform for scheduled plan downgrade migration.');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE agency_subscription DROP CONSTRAINT FK_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE');
            $this->addSql('DROP INDEX IDX_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE');
            $this->addSql('ALTER TABLE agency_subscription DROP provider_schedule_id');
            $this->addSql('ALTER TABLE agency_subscription DROP pending_plan_price_id');
            $this->addSql('ALTER TABLE agency_subscription DROP pending_plan_change_effective_at');
            $this->addSql('ALTER TABLE agency_subscription DROP pending_plan_change_requested_at');

            return;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('ALTER TABLE agency_subscription DROP FOREIGN KEY FK_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE');
            $this->addSql('DROP INDEX IDX_AGENCY_SUBSCRIPTION_PENDING_PLAN_PRICE ON agency_subscription');
            $this->addSql('ALTER TABLE agency_subscription DROP provider_schedule_id, DROP pending_plan_price_id, DROP pending_plan_change_effective_at, DROP pending_plan_change_requested_at');

            return;
        }

        throw new \RuntimeException('Unsupported platform for scheduled plan downgrade migration.');
    }
}
