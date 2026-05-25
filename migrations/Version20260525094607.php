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
final class Version20260525094607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property ADD dpe_lettre VARCHAR(255) DEFAULT NULL, ADD ges VARCHAR(255) DEFAULT NULL, ADD ges_lettre VARCHAR(255) DEFAULT NULL, ADD dpe_min VARCHAR(255) DEFAULT NULL, ADD dpe_max VARCHAR(255) DEFAULT NULL, ADD date_indexation_energie DATETIME DEFAULT NULL, CHANGE performance_energetique dpe VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property ADD performance_energetique VARCHAR(255) DEFAULT NULL, DROP dpe, DROP dpe_lettre, DROP ges, DROP ges_lettre, DROP dpe_min, DROP dpe_max, DROP date_indexation_energie');
    }
}
