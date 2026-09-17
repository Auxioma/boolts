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
 * Ajoute utilisateur.document_review_outcome_notified.
 *
 * Mémorise le dernier résultat de la revue documentaire notifié par e-mail à
 * l'agence ("approved" / "rejected"), afin de n'envoyer qu'un seul e-mail par
 * résultat lors de l'enregistrement de la fiche depuis l'administration.
 */
final class Version20260907120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'utilisateur : ajoute document_review_outcome_notified (VARCHAR(20) NULL).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `utilisateur` ADD document_review_outcome_notified VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `utilisateur` DROP document_review_outcome_notified');
    }
}
