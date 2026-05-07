<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507144949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pays (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, iso VARCHAR(5) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE translation (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(255) NOT NULL, locale VARCHAR(255) NOT NULL, translation LONGTEXT NOT NULL, page VARCHAR(255) NOT NULL, UNIQUE INDEX uniq_translation_key_locale (`key`, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE `utilisateur` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(250) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, email_auth_code VARCHAR(255) DEFAULT NULL, email_auth_code_expires_at DATETIME DEFAULT NULL, email_auth_code_requested_at DATETIME DEFAULT NULL, failed_verification_attempts INT DEFAULT 0 NOT NULL, is_verified TINYINT NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, adresse_complement VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, pays_id INT DEFAULT NULL, INDEX IDX_1D1C63B3A6E44244 (pays_id), INDEX IDX_USER_VERIFIED (is_verified), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT FK_1D1C63B3A6E44244 FOREIGN KEY (pays_id) REFERENCES pays (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B3A6E44244');
        $this->addSql('DROP TABLE pays');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE translation');
        $this->addSql('DROP TABLE `utilisateur`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
