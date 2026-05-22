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
final class Version20260522120455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE property_caracteristique (property_id INT NOT NULL, caracteristique_id INT NOT NULL, INDEX IDX_D6F4BE49549213EC (property_id), INDEX IDX_D6F4BE491704EEB7 (caracteristique_id), PRIMARY KEY (property_id, caracteristique_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE property_caracteristique ADD CONSTRAINT FK_D6F4BE49549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_caracteristique ADD CONSTRAINT FK_D6F4BE491704EEB7 FOREIGN KEY (caracteristique_id) REFERENCES caracteristique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property ADD statut VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property_caracteristique DROP FOREIGN KEY FK_D6F4BE49549213EC');
        $this->addSql('ALTER TABLE property_caracteristique DROP FOREIGN KEY FK_D6F4BE491704EEB7');
        $this->addSql('DROP TABLE property_caracteristique');
        $this->addSql('ALTER TABLE property DROP statut');
    }
}
