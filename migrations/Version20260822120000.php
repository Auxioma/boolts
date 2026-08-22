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

final class Version20260822120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le suivi de progression pour reprendre une inscription agence incomplète.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `utilisateur` ADD agency_registration_step VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `utilisateur` DROP agency_registration_step');
    }
}
