<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prevent physical user deletion from cascading into agency billing profiles.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE agency_billing_profile DROP CONSTRAINT FK_7F37F400CDEADB2A');
            $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F400CDEADB2A FOREIGN KEY (agency_id) REFERENCES utilisateur (id) ON DELETE RESTRICT NOT DEFERRABLE INITIALLY IMMEDIATE');

            return;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('ALTER TABLE agency_billing_profile DROP FOREIGN KEY FK_7F37F400CDEADB2A');
            $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F400CDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE RESTRICT');

            return;
        }

        throw new \RuntimeException('Unsupported platform for agency billing profile deletion guard migration.');
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql('ALTER TABLE agency_billing_profile DROP CONSTRAINT FK_7F37F400CDEADB2A');
            $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F400CDEADB2A FOREIGN KEY (agency_id) REFERENCES utilisateur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

            return;
        }

        if ($platform instanceof AbstractMySQLPlatform) {
            $this->addSql('ALTER TABLE agency_billing_profile DROP FOREIGN KEY FK_7F37F400CDEADB2A');
            $this->addSql('ALTER TABLE agency_billing_profile ADD CONSTRAINT FK_7F37F400CDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');

            return;
        }

        throw new \RuntimeException('Unsupported platform for agency billing profile deletion guard migration.');
    }
}
