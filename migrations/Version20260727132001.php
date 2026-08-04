<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
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
final class Version20260727132001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agency_profile_daily_visit (id INT AUTO_INCREMENT NOT NULL, viewed_on DATE NOT NULL, visits INT DEFAULT 0 NOT NULL, agency_id INT NOT NULL, INDEX IDX_507FAD7ECDEADB2A (agency_id), UNIQUE INDEX uniq_agency_profile_daily_visit (agency_id, viewed_on), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE agency_profile_daily_visit ADD CONSTRAINT FK_507FAD7ECDEADB2A FOREIGN KEY (agency_id) REFERENCES `utilisateur` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agency_profile_daily_visit DROP FOREIGN KEY FK_507FAD7ECDEADB2A');
        $this->addSql('DROP TABLE agency_profile_daily_visit');
    }
}
