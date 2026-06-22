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
final class Version20260618152509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_image DROP FOREIGN KEY `FK_32EC552549213EC`');
        $this->addSql('ALTER TABLE property_image CHANGE property_id property_id INT NOT NULL');
        $this->addSql('ALTER TABLE property_image ADD CONSTRAINT FK_32EC552549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_image DROP FOREIGN KEY FK_32EC552549213EC');
        $this->addSql('ALTER TABLE property_image CHANGE property_id property_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE property_image ADD CONSTRAINT `FK_32EC552549213EC` FOREIGN KEY (property_id) REFERENCES property (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
