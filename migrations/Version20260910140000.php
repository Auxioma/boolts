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
 * property_image.position : passage de VARCHAR(255) à INT.
 *
 * La position des photos d'un bien est un rang (1, 2, 3, …) utilisé pour
 * l'ordre d'affichage (couverture en premier). Stockée en chaîne, elle se
 * triait lexicographiquement ("10" avant "2") dès la 10ᵉ photo — visible sur
 * la page publique du bien qui trie en SQL (PropertyRepository::…addOrderBy(
 * 'propertyImage.position', 'ASC')).
 *
 * Le nouvel upload AJAX de l'étape 6 « Mes biens » réordonne jusqu'à 50 photos :
 * un entier est indispensable. Les valeurs existantes sont des chaînes
 * numériques, le CAST est donc direct et sans perte.
 */
final class Version20260910140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'property_image.position : VARCHAR(255) -> INT.';
    }

    public function up(Schema $schema): void
    {
        // Sécurité : neutralise une éventuelle valeur non numérique avant le CAST.
        $this->addSql("UPDATE property_image SET position = '0' WHERE position IS NULL OR position NOT REGEXP '^[0-9]+$'");
        $this->addSql('ALTER TABLE property_image MODIFY position INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE property_image MODIFY position VARCHAR(255) NOT NULL');
    }
}
