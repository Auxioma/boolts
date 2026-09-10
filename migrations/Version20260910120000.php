<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute property.code_iso_pays.
 *
 * Mémorise le code ISO 3166-1 (alpha-2, ex. "FR") du pays renvoyé par Mapbox
 * lors de la saisie de l'adresse (étape 3 du tunnel « Mes biens »). La colonne
 * "pays" ne contenant qu'un libellé localisé, ce code offre un repère technique
 * fiable (détection France, filtres, etc.).
 */
final class Version20260910120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'property : ajoute code_iso_pays (VARCHAR(10) NULL).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `property` ADD code_iso_pays VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `property` DROP code_iso_pays');
    }
}
