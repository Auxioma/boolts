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
final class Version20260624110122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_search_session (id BIGINT AUTO_INCREMENT NOT NULL, uuid BINARY(16) NOT NULL, transaction_type_id BIGINT NOT NULL, ville VARCHAR(180) DEFAULT NULL, cp VARCHAR(50) DEFAULT NULL, pays VARCHAR(180) NOT NULL, filters JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8B51E14AD17F50A6 (uuid), INDEX idx_property_search_session_uuid (uuid), INDEX idx_property_search_session_transaction_type (transaction_type_id), INDEX idx_property_search_session_ville (ville), INDEX idx_property_search_session_cp (cp), INDEX idx_property_search_session_pays (pays), INDEX idx_property_search_session_expires_at (expires_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE property_search_session');
    }
}
