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

final class Version20260728084258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration vide : tables déjà créées dans la migration précédente.';
    }

    public function up(Schema $schema): void
    {
        // jai retirer les doublons
    }

    public function down(Schema $schema): void
    {
        // same ici !
    }
}
