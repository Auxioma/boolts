<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820055304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD document_deletion_warning_thirty_days_sent_at DATETIME DEFAULT NULL, ADD document_deletion_warning_fifteen_days_sent_at DATETIME DEFAULT NULL, ADD document_deletion_warning_five_days_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `utilisateur` DROP document_deletion_warning_thirty_days_sent_at, DROP document_deletion_warning_fifteen_days_sent_at, DROP document_deletion_warning_five_days_sent_at');
    }
}
