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
final class Version20260521103315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_bien_transaction ADD icone VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE property ADD type_transaction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE7903E29B FOREIGN KEY (type_transaction_id) REFERENCES category_bien_transaction (id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE7903E29B ON property (type_transaction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_bien_transaction DROP icone');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE7903E29B');
        $this->addSql('DROP INDEX IDX_8BF21CDE7903E29B ON property');
        $this->addSql('ALTER TABLE property DROP type_transaction_id');
    }
}
