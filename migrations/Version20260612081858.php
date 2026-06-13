<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612081858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_view (id INT AUTO_INCREMENT NOT NULL, view_key VARCHAR(64) NOT NULL, visitor_hash VARCHAR(64) NOT NULL, viewed_at DATETIME NOT NULL, property_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX idx_property_view_property (property_id), INDEX idx_property_view_user (user_id), UNIQUE INDEX uniq_property_view_key (view_key), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE property_view ADD CONSTRAINT FK_E1E514B4549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_view ADD CONSTRAINT FK_E1E514B4A76ED395 FOREIGN KEY (user_id) REFERENCES `utilisateur` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_view DROP FOREIGN KEY FK_E1E514B4549213EC');
        $this->addSql('ALTER TABLE property_view DROP FOREIGN KEY FK_E1E514B4A76ED395');
        $this->addSql('DROP TABLE property_view');
    }
}
