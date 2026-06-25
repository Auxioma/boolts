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
final class Version20260624092105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE caracteristique_translation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_9AB160AD2C2AC5D3 (translatable_id), UNIQUE INDEX caracteristique_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE caracteristique_translation ADD CONSTRAINT FK_9AB160AD2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES caracteristique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE caracteristique DROP nom');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE caracteristique_translation DROP FOREIGN KEY FK_9AB160AD2C2AC5D3');
        $this->addSql('DROP TABLE caracteristique_translation');
        $this->addSql('ALTER TABLE caracteristique ADD nom VARCHAR(255) NOT NULL');
    }
}
