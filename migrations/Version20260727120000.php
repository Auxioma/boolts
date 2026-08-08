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

final class Version20260727120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la date de mise en favoris pour les statistiques du tableau de bord.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE favoris ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_favoris_created_at ON favoris (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_favoris_created_at ON favoris');
        $this->addSql('ALTER TABLE favoris DROP created_at');
    }
}
