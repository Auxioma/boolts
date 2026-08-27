<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826154500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Stripe subscription renewal recovery, payment attempts, history, and email idempotency storage.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->upPostgreSql();

            return;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->upMySql();

            return;
        }

        throw new \RuntimeException('Unsupported platform for subscription recovery migration.');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->downPostgreSql();

            return;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->downMySql();

            return;
        }

        throw new \RuntimeException('Unsupported platform for subscription recovery migration.');
    }

    private function upMySql(): void
    {
        $this->addSql('ALTER TABLE agency_subscription ADD cancel_requested_at DATETIME DEFAULT NULL, ADD provider_price_id VARCHAR(255) DEFAULT NULL, ADD provider_product_id VARCHAR(255) DEFAULT NULL, ADD provider_latest_invoice_id VARCHAR(255) DEFAULT NULL, ADD payment_failure_count INT DEFAULT 0 NOT NULL, ADD first_payment_failure_at DATETIME DEFAULT NULL, ADD last_payment_failure_at DATETIME DEFAULT NULL, ADD next_payment_retry_at DATETIME DEFAULT NULL, ADD payment_recovery_deadline DATETIME DEFAULT NULL, ADD last_successful_payment_at DATETIME DEFAULT NULL, ADD last_stripe_sync_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_agency_subscription_status_period_end ON agency_subscription (status, current_period_end)');
        $this->addSql('CREATE INDEX idx_agency_subscription_retry ON agency_subscription (status, next_payment_retry_at)');
        $this->addSql('CREATE INDEX idx_agency_subscription_open_paid ON agency_subscription (agency_id, status)');

        $this->addSql('ALTER TABLE payment ADD billing_period_start DATETIME DEFAULT NULL, ADD billing_period_end DATETIME DEFAULT NULL, ADD attempt_number INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_payment_provider_invoice ON payment (provider_invoice_id)');
        $this->addSql('CREATE INDEX idx_payment_subscription_type ON payment (subscription_id, type)');

        $this->addSql('ALTER TABLE payment_attempt ADD subscription_id INT DEFAULT NULL, ADD provider_invoice_id VARCHAR(255) DEFAULT NULL, ADD attempted_at DATETIME DEFAULT NULL');
        $this->addSql("UPDATE payment_attempt SET attempted_at = COALESCE(NULLIF(created_at, '0000-00-00 00:00:00'), CURRENT_TIMESTAMP) WHERE attempted_at IS NULL OR attempted_at = '0000-00-00 00:00:00'");
        $this->addSql('ALTER TABLE payment_attempt MODIFY attempted_at DATETIME NOT NULL');
        $this->addSql('CREATE INDEX idx_payment_attempt_subscription_invoice ON payment_attempt (subscription_id, provider_invoice_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_payment_attempt_invoice_number ON payment_attempt (subscription_id, provider_invoice_id, attempt_number)');
        $this->addSql('ALTER TABLE payment_attempt ADD CONSTRAINT FK_PAYMENT_ATTEMPT_SUBSCRIPTION FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE subscription_history (id INT AUTO_INCREMENT NOT NULL, event_type VARCHAR(80) NOT NULL, old_status VARCHAR(30) DEFAULT NULL, new_status VARCHAR(30) DEFAULT NULL, old_plan VARCHAR(80) DEFAULT NULL, new_plan VARCHAR(80) DEFAULT NULL, provider_invoice_id VARCHAR(255) DEFAULT NULL, provider_payment_intent_id VARCHAR(255) DEFAULT NULL, metadata JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, subscription_id INT NOT NULL, agency_id INT NOT NULL, INDEX idx_subscription_history_subscription_event (subscription_id, event_type), INDEX IDX_SUBSCRIPTION_HISTORY_AGENCY (agency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE subscription_history ADD CONSTRAINT FK_SUBSCRIPTION_HISTORY_SUBSCRIPTION FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription_history ADD CONSTRAINT FK_SUBSCRIPTION_HISTORY_AGENCY FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE subscription_email_log (id INT AUTO_INCREMENT NOT NULL, event_type VARCHAR(80) NOT NULL, event_key VARCHAR(255) NOT NULL, recipient_email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, context JSON NOT NULL, status VARCHAR(30) NOT NULL, queued_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL, failed_at DATETIME DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, subscription_id INT NOT NULL, agency_id INT NOT NULL, UNIQUE INDEX uniq_subscription_email_event (subscription_id, event_type, event_key), INDEX IDX_SUBSCRIPTION_EMAIL_LOG_AGENCY (agency_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE subscription_email_log ADD CONSTRAINT FK_SUBSCRIPTION_EMAIL_LOG_SUBSCRIPTION FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription_email_log ADD CONSTRAINT FK_SUBSCRIPTION_EMAIL_LOG_AGENCY FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
    }

    private function downMySql(): void
    {
        $this->addSql('ALTER TABLE subscription_email_log DROP FOREIGN KEY FK_SUBSCRIPTION_EMAIL_LOG_SUBSCRIPTION');
        $this->addSql('ALTER TABLE subscription_email_log DROP FOREIGN KEY FK_SUBSCRIPTION_EMAIL_LOG_AGENCY');
        $this->addSql('ALTER TABLE subscription_history DROP FOREIGN KEY FK_SUBSCRIPTION_HISTORY_SUBSCRIPTION');
        $this->addSql('ALTER TABLE subscription_history DROP FOREIGN KEY FK_SUBSCRIPTION_HISTORY_AGENCY');
        $this->addSql('ALTER TABLE payment_attempt DROP FOREIGN KEY FK_PAYMENT_ATTEMPT_SUBSCRIPTION');
        $this->addSql('DROP TABLE subscription_email_log');
        $this->addSql('DROP TABLE subscription_history');
        $this->addSql('DROP INDEX uniq_payment_attempt_invoice_number ON payment_attempt');
        $this->addSql('DROP INDEX idx_payment_attempt_subscription_invoice ON payment_attempt');
        $this->addSql('ALTER TABLE payment_attempt DROP subscription_id, DROP provider_invoice_id, DROP attempted_at');
        $this->addSql('DROP INDEX idx_payment_subscription_type ON payment');
        $this->addSql('DROP INDEX uniq_payment_provider_invoice ON payment');
        $this->addSql('ALTER TABLE payment DROP billing_period_start, DROP billing_period_end, DROP attempt_number');
        $this->addSql('DROP INDEX idx_agency_subscription_open_paid ON agency_subscription');
        $this->addSql('DROP INDEX idx_agency_subscription_retry ON agency_subscription');
        $this->addSql('DROP INDEX idx_agency_subscription_status_period_end ON agency_subscription');
        $this->addSql('ALTER TABLE agency_subscription DROP cancel_requested_at, DROP provider_price_id, DROP provider_product_id, DROP provider_latest_invoice_id, DROP payment_failure_count, DROP first_payment_failure_at, DROP last_payment_failure_at, DROP next_payment_retry_at, DROP payment_recovery_deadline, DROP last_successful_payment_at, DROP last_stripe_sync_at');
    }

    private function upPostgreSql(): void
    {
        $this->addSql('ALTER TABLE agency_subscription ADD cancel_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD provider_price_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD provider_product_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD provider_latest_invoice_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD payment_failure_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD first_payment_failure_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD last_payment_failure_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD next_payment_retry_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD payment_recovery_deadline TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD last_successful_payment_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE agency_subscription ADD last_stripe_sync_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_agency_subscription_status_period_end ON agency_subscription (status, current_period_end)');
        $this->addSql('CREATE INDEX idx_agency_subscription_retry ON agency_subscription (status, next_payment_retry_at)');
        $this->addSql("CREATE UNIQUE INDEX uniq_agency_one_open_paid_subscription ON agency_subscription (agency_id) WHERE status IN ('active', 'past_due', 'payment_failed', 'cancel_scheduled', 'incomplete', 'unpaid')");

        $this->addSql('ALTER TABLE payment ADD billing_period_start TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD billing_period_end TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD attempt_number INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_payment_provider_invoice ON payment (provider_invoice_id)');
        $this->addSql('CREATE INDEX idx_payment_subscription_type ON payment (subscription_id, type)');

        $this->addSql('ALTER TABLE payment_attempt ADD subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_attempt ADD provider_invoice_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_attempt ADD attempted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('UPDATE payment_attempt SET attempted_at = COALESCE(created_at, CURRENT_TIMESTAMP) WHERE attempted_at IS NULL');
        $this->addSql('ALTER TABLE payment_attempt ALTER COLUMN attempted_at SET NOT NULL');
        $this->addSql('CREATE INDEX idx_payment_attempt_subscription_invoice ON payment_attempt (subscription_id, provider_invoice_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_payment_attempt_invoice_number ON payment_attempt (subscription_id, provider_invoice_id, attempt_number)');
        $this->addSql('ALTER TABLE payment_attempt ADD CONSTRAINT FK_PAYMENT_ATTEMPT_SUBSCRIPTION FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE subscription_history (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, event_type VARCHAR(80) NOT NULL, old_status VARCHAR(30) DEFAULT NULL, new_status VARCHAR(30) DEFAULT NULL, old_plan VARCHAR(80) DEFAULT NULL, new_plan VARCHAR(80) DEFAULT NULL, provider_invoice_id VARCHAR(255) DEFAULT NULL, provider_payment_intent_id VARCHAR(255) DEFAULT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, subscription_id INT NOT NULL, agency_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_subscription_history_subscription_event ON subscription_history (subscription_id, event_type)');
        $this->addSql('CREATE INDEX IDX_SUBSCRIPTION_HISTORY_AGENCY ON subscription_history (agency_id)');
        $this->addSql('ALTER TABLE subscription_history ADD CONSTRAINT FK_SUBSCRIPTION_HISTORY_SUBSCRIPTION FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription_history ADD CONSTRAINT FK_SUBSCRIPTION_HISTORY_AGENCY FOREIGN KEY (agency_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE subscription_email_log (id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, event_type VARCHAR(80) NOT NULL, event_key VARCHAR(255) NOT NULL, recipient_email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, context JSON NOT NULL, status VARCHAR(30) NOT NULL, queued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, failed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, error_message TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, subscription_id INT NOT NULL, agency_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_subscription_email_event ON subscription_email_log (subscription_id, event_type, event_key)');
        $this->addSql('CREATE INDEX IDX_SUBSCRIPTION_EMAIL_LOG_AGENCY ON subscription_email_log (agency_id)');
        $this->addSql('ALTER TABLE subscription_email_log ADD CONSTRAINT FK_SUBSCRIPTION_EMAIL_LOG_SUBSCRIPTION FOREIGN KEY (subscription_id) REFERENCES agency_subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription_email_log ADD CONSTRAINT FK_SUBSCRIPTION_EMAIL_LOG_AGENCY FOREIGN KEY (agency_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    private function downPostgreSql(): void
    {
        $this->addSql('DROP TABLE subscription_email_log');
        $this->addSql('DROP TABLE subscription_history');
        $this->addSql('ALTER TABLE payment_attempt DROP CONSTRAINT FK_PAYMENT_ATTEMPT_SUBSCRIPTION');
        $this->addSql('DROP INDEX uniq_payment_attempt_invoice_number');
        $this->addSql('DROP INDEX idx_payment_attempt_subscription_invoice');
        $this->addSql('ALTER TABLE payment_attempt DROP subscription_id');
        $this->addSql('ALTER TABLE payment_attempt DROP provider_invoice_id');
        $this->addSql('ALTER TABLE payment_attempt DROP attempted_at');
        $this->addSql('DROP INDEX idx_payment_subscription_type');
        $this->addSql('DROP INDEX uniq_payment_provider_invoice');
        $this->addSql('ALTER TABLE payment DROP billing_period_start');
        $this->addSql('ALTER TABLE payment DROP billing_period_end');
        $this->addSql('ALTER TABLE payment DROP attempt_number');
        $this->addSql('DROP INDEX uniq_agency_one_open_paid_subscription');
        $this->addSql('DROP INDEX idx_agency_subscription_retry');
        $this->addSql('DROP INDEX idx_agency_subscription_status_period_end');
        $this->addSql('ALTER TABLE agency_subscription DROP cancel_requested_at');
        $this->addSql('ALTER TABLE agency_subscription DROP provider_price_id');
        $this->addSql('ALTER TABLE agency_subscription DROP provider_product_id');
        $this->addSql('ALTER TABLE agency_subscription DROP provider_latest_invoice_id');
        $this->addSql('ALTER TABLE agency_subscription DROP payment_failure_count');
        $this->addSql('ALTER TABLE agency_subscription DROP first_payment_failure_at');
        $this->addSql('ALTER TABLE agency_subscription DROP last_payment_failure_at');
        $this->addSql('ALTER TABLE agency_subscription DROP next_payment_retry_at');
        $this->addSql('ALTER TABLE agency_subscription DROP payment_recovery_deadline');
        $this->addSql('ALTER TABLE agency_subscription DROP last_successful_payment_at');
        $this->addSql('ALTER TABLE agency_subscription DROP last_stripe_sync_at');
    }
}
