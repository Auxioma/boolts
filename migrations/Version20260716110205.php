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
final class Version20260716110205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE langues CHANGE iso iso VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY `FK_1D1C63B328EAE92`');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B328EAE92 FOREIGN KEY (langues_id) REFERENCES langues (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE langues CHANGE iso iso VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE `utilisateur` DROP FOREIGN KEY FK_1D1C63B328EAE92');
        $this->addSql('ALTER TABLE `utilisateur` ADD CONSTRAINT `FK_1D1C63B328EAE92` FOREIGN KEY (langues_id) REFERENCES langues (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
