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
final class Version20260624202942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_translation (id INT AUTO_INCREMENT NOT NULL, adresse VARCHAR(255) DEFAULT NULL, adresse_complement VARCHAR(255) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, adresse_contact VARCHAR(255) DEFAULT NULL, ville_contact VARCHAR(255) DEFAULT NULL, pays_contact VARCHAR(255) DEFAULT NULL, adresse_complement_contact VARCHAR(255) DEFAULT NULL, locale VARCHAR(5) NOT NULL, translatable_id INT DEFAULT NULL, INDEX IDX_1D728CFA2C2AC5D3 (translatable_id), UNIQUE INDEX user_translation_unique_translation (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE user_translation ADD CONSTRAINT FK_1D728CFA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE utilisateur DROP adresse, DROP adresse_complement, DROP ville, DROP description, DROP adresse_contact, DROP ville_contact, DROP pays_contact, DROP adresse_complement_contact');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_translation DROP FOREIGN KEY FK_1D728CFA2C2AC5D3');
        $this->addSql('DROP TABLE user_translation');
        $this->addSql('ALTER TABLE `utilisateur` ADD adresse VARCHAR(255) DEFAULT NULL, ADD adresse_complement VARCHAR(255) DEFAULT NULL, ADD ville VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD adresse_contact VARCHAR(255) DEFAULT NULL, ADD ville_contact VARCHAR(255) DEFAULT NULL, ADD pays_contact VARCHAR(255) DEFAULT NULL, ADD adresse_complement_contact VARCHAR(255) DEFAULT NULL');
    }
}
