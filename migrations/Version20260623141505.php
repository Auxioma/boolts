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
final class Version20260623141505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_translation (id INT AUTO_INCREMENT NOT NULL, adresse VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, full_address VARCHAR(255) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, district VARCHAR(255) DEFAULT NULL, locality VARCHAR(255) DEFAULT NULL, neighborhood VARCHAR(255) DEFAULT NULL, poi VARCHAR(255) DEFAULT NULL, titre_du_logement VARCHAR(255) DEFAULT NULL, description_logement LONGTEXT DEFAULT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_B0C85592C2AC5D3 (translatable_id), UNIQUE INDEX property_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE property_translation ADD CONSTRAINT FK_B0C85592C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property DROP adresse, DROP ville, DROP pays, DROP full_address, DROP region, DROP district, DROP locality, DROP neighborhood, DROP poi, DROP titre_du_logement, DROP description_logement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_translation DROP FOREIGN KEY FK_B0C85592C2AC5D3');
        $this->addSql('DROP TABLE property_translation');
        $this->addSql('ALTER TABLE property ADD adresse VARCHAR(255) DEFAULT NULL, ADD ville VARCHAR(255) DEFAULT NULL, ADD pays VARCHAR(255) DEFAULT NULL, ADD full_address VARCHAR(255) DEFAULT NULL, ADD region VARCHAR(255) DEFAULT NULL, ADD district VARCHAR(255) DEFAULT NULL, ADD locality VARCHAR(255) DEFAULT NULL, ADD neighborhood VARCHAR(255) DEFAULT NULL, ADD poi VARCHAR(255) DEFAULT NULL, ADD titre_du_logement VARCHAR(255) DEFAULT NULL, ADD description_logement LONGTEXT DEFAULT NULL');
    }
}
