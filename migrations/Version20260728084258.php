<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728084258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE required_document (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, required TINYINT NOT NULL, enabled TINYINT NOT NULL, max_submissions INT NOT NULL, position INT NOT NULL, accepted_mime_types VARCHAR(255) DEFAULT NULL, max_file_size_mb INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE user_document_request (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) DEFAULT \'waiting_upload\' NOT NULL, blocked TINYINT NOT NULL, blocked_reason VARCHAR(500) DEFAULT NULL, blocked_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, required_document_id INT NOT NULL, INDEX IDX_E2ED6B9EA76ED395 (user_id), INDEX IDX_E2ED6B9E837C14A7 (required_document_id), UNIQUE INDEX uniq_user_required_document (user_id, required_document_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE user_document_submission (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, original_file_name VARCHAR(255) NOT NULL, mime_type VARCHAR(150) NOT NULL, file_size INT NOT NULL, storage_path VARCHAR(500) NOT NULL, checksum VARCHAR(64) NOT NULL, attempt_number INT NOT NULL, status VARCHAR(255) DEFAULT \'pending\' NOT NULL, rejection_reason LONGTEXT DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, submitted_at DATETIME NOT NULL, document_request_id INT NOT NULL, reviewed_by_id INT DEFAULT NULL, INDEX IDX_50059ED7E3BD13F3 (document_request_id), INDEX IDX_50059ED7FC6B21F1 (reviewed_by_id), UNIQUE INDEX uniq_document_attempt (document_request_id, attempt_number), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE user_document_request ADD CONSTRAINT FK_E2ED6B9EA76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_document_request ADD CONSTRAINT FK_E2ED6B9E837C14A7 FOREIGN KEY (required_document_id) REFERENCES required_document (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE user_document_submission ADD CONSTRAINT FK_50059ED7E3BD13F3 FOREIGN KEY (document_request_id) REFERENCES user_document_request (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_document_submission ADD CONSTRAINT FK_50059ED7FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES `utilisateur` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_document_request DROP FOREIGN KEY FK_E2ED6B9EA76ED395');
        $this->addSql('ALTER TABLE user_document_request DROP FOREIGN KEY FK_E2ED6B9E837C14A7');
        $this->addSql('ALTER TABLE user_document_submission DROP FOREIGN KEY FK_50059ED7E3BD13F3');
        $this->addSql('ALTER TABLE user_document_submission DROP FOREIGN KEY FK_50059ED7FC6B21F1');
        $this->addSql('DROP TABLE required_document');
        $this->addSql('DROP TABLE user_document_request');
        $this->addSql('DROP TABLE user_document_submission');
    }
}
