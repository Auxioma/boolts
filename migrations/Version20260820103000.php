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

final class Version20260820103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le suivi des rappels avant suppression de compte pour documents non transmis.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `utilisateur` ADD document_deletion_warning_thirty_days_sent_at DATETIME DEFAULT NULL, ADD document_deletion_warning_fifteen_days_sent_at DATETIME DEFAULT NULL, ADD document_deletion_warning_five_days_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `utilisateur` DROP document_deletion_warning_thirty_days_sent_at, DROP document_deletion_warning_fifteen_days_sent_at, DROP document_deletion_warning_five_days_sent_at');
    }
}
