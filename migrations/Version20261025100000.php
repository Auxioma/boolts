<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Notifications agence : clé et paramètres de traduction du libellé.
 */
final class Version20261025100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Notifications agence : clé et paramètres de traduction';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE agency_notification ADD translation_key VARCHAR(255) DEFAULT NULL, ADD translation_parameters JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE agency_notification DROP translation_key, DROP translation_parameters');
    }
}
