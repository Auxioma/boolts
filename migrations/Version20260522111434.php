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
final class Version20260522111434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE caracteristique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, icone VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE property ADD chambres VARCHAR(255) DEFAULT NULL, ADD salle_de_bains VARCHAR(255) DEFAULT NULL, ADD surface_total VARCHAR(255) DEFAULT NULL, CHANGE titre_du_logement titre_du_logement VARCHAR(255) DEFAULT NULL, CHANGE description_logement description_logement LONGTEXT DEFAULT NULL, CHANGE performance_energetique performance_energetique VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE caracteristique');
        $this->addSql('ALTER TABLE property DROP chambres, DROP salle_de_bains, DROP surface_total, CHANGE titre_du_logement titre_du_logement VARCHAR(255) NOT NULL, CHANGE description_logement description_logement LONGTEXT NOT NULL, CHANGE performance_energetique performance_energetique VARCHAR(50) DEFAULT NULL');
    }
}
