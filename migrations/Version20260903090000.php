<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Élargit les colonnes issues du géocodage Mapbox (VARCHAR(255) -> LONGTEXT).
 *
 * Certains identifiants et libellés d'adresse renvoyés par Mapbox (POI
 * notamment) dépassent 255 caractères, ce qui provoquait une erreur
 * « data too long » lors de la création d'un bien immobilier.
 */
final class Version20260903090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'property.mapbox_id + property_translation (adresse, ville, pays, full_address, region, district, locality, neighborhood, poi) : VARCHAR(255) -> LONGTEXT.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property CHANGE mapbox_id mapbox_id LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE property_translation CHANGE adresse adresse LONGTEXT DEFAULT NULL, CHANGE ville ville LONGTEXT DEFAULT NULL, CHANGE pays pays LONGTEXT DEFAULT NULL, CHANGE full_address full_address LONGTEXT DEFAULT NULL, CHANGE region region LONGTEXT DEFAULT NULL, CHANGE district district LONGTEXT DEFAULT NULL, CHANGE locality locality LONGTEXT DEFAULT NULL, CHANGE neighborhood neighborhood LONGTEXT DEFAULT NULL, CHANGE poi poi LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property CHANGE mapbox_id mapbox_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE property_translation CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE ville ville VARCHAR(255) DEFAULT NULL, CHANGE pays pays VARCHAR(255) DEFAULT NULL, CHANGE full_address full_address VARCHAR(255) DEFAULT NULL, CHANGE region region VARCHAR(255) DEFAULT NULL, CHANGE district district VARCHAR(255) DEFAULT NULL, CHANGE locality locality VARCHAR(255) DEFAULT NULL, CHANGE neighborhood neighborhood VARCHAR(255) DEFAULT NULL, CHANGE poi poi VARCHAR(255) DEFAULT NULL');
    }
}
