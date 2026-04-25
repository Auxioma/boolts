<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260425144930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD email_auth_code VARCHAR(255) DEFAULT NULL, ADD email_auth_code_expires_at DATETIME DEFAULT NULL, ADD email_auth_code_requested_at DATETIME DEFAULT NULL, ADD failed_verification_attempts INT DEFAULT 0 NOT NULL, CHANGE email email VARCHAR(250) NOT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `utilisateur` DROP email_auth_code, DROP email_auth_code_expires_at, DROP email_auth_code_requested_at, DROP failed_verification_attempts, CHANGE email email VARCHAR(180) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL');
    }
}
