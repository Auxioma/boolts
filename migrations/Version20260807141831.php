<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807141831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la configuration de maintenance et les adresses IP autorisées';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE maintenance_allowed_ip (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(100) DEFAULT NULL, ip_address VARCHAR(45) NOT NULL, enabled TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CA6E11B122FFD58C (ip_address), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE maintenance_setting (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT DEFAULT 0 NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, starts_at DATETIME DEFAULT NULL, ends_at DATETIME DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql("INSERT INTO maintenance_setting (enabled, title, message, starts_at, ends_at, updated_at) VALUES (0, 'Maintenance en cours', 'BOOLTS évolue pour vous. Notre plateforme est temporairement indisponible. Merci de votre patience.', NULL, NULL, CURRENT_TIMESTAMP)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE maintenance_allowed_ip');
        $this->addSql('DROP TABLE maintenance_setting');
    }
}
