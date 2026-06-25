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
final class Version20260623142018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_bien_translation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_8E410EED2C2AC5D3 (translatable_id), UNIQUE INDEX category_bien_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE category_bien_translation ADD CONSTRAINT FK_8E410EED2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES category_bien (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_bien DROP name, DROP slug');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_bien_translation DROP FOREIGN KEY FK_8E410EED2C2AC5D3');
        $this->addSql('DROP TABLE category_bien_translation');
        $this->addSql('ALTER TABLE category_bien ADD name VARCHAR(255) NOT NULL, ADD slug VARCHAR(255) NOT NULL');
    }
}
