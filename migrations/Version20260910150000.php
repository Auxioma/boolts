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
 * property : surface_total / prix / montant_loyer_hors_charge / annee_construction
 * passent de VARCHAR(255) à un type numérique.
 *
 * Ces champs étaient comparés numériquement par les filtres « Mes biens »
 * (PropertyRepository::addRangeFilter : `champ >= :min` / `champ <= :max`).
 * Sur une colonne VARCHAR, MySQL fait une conversion implicite chaîne -> nombre
 * qui tronque toute valeur « sale » : "1 200" -> 1, "120 m²" -> 120,
 * "250 000 €" -> 250, "120,5" -> 120, "vers 1970" -> 0. Des biens valides
 * étaient donc exclus (ou gardés) à tort.
 *
 * Les valeurs existantes sont nettoyées avant le changement de type :
 *  - montants : espaces (normaux + insécables) retirés, virgule décimale -> point,
 *    puis suppression de tout caractère hors [0-9.] (unités « m² », « € », etc.) ;
 *    une valeur restée non numérique (séparateur de milliers, texte libre) est
 *    mise à NULL plutôt que persistée fausse ;
 *  - année : on garde le premier groupe de 4 chiffres ("1990-2000" -> 1990,
 *    "vers 1983" -> 1983), sinon NULL.
 */
final class Version20260910150000 extends AbstractMigration
{
    /**
     * Montants : nom de colonne => déclaration du nouveau type.
     */
    private const array DECIMAL_COLUMNS = [
        'surface_total' => 'NUMERIC(10, 2)',
        'prix' => 'NUMERIC(14, 2)',
        'montant_loyer_hors_charge' => 'NUMERIC(12, 2)',
    ];

    public function getDescription(): string
    {
        return 'property : surface_total / prix / montant_loyer_hors_charge -> NUMERIC, annee_construction -> SMALLINT.';
    }

    public function up(Schema $schema): void
    {
        foreach (array_keys(self::DECIMAL_COLUMNS) as $column) {
            // Espaces (normaux + insécables C2A0) puis virgule décimale -> point.
            $this->addSql(\sprintf(
                "UPDATE property SET %1\$s = REPLACE(REPLACE(REPLACE(%1\$s, ' ', ''), UNHEX('C2A0'), ''), ',', '.') WHERE %1\$s IS NOT NULL",
                $column
            ));

            // Retrait des unités et de tout caractère parasite (« m² », « € », lettres…).
            $this->addSql(\sprintf(
                "UPDATE property SET %1\$s = REGEXP_REPLACE(%1\$s, '[^0-9.]', '') WHERE %1\$s IS NOT NULL",
                $column
            ));

            // Ce qui n'est toujours pas un nombre propre est neutralisé.
            $this->addSql(\sprintf(
                "UPDATE property SET %1\$s = NULL WHERE %1\$s IS NOT NULL AND %1\$s NOT REGEXP '^[0-9]+(\\.[0-9]+)?\$'",
                $column
            ));
        }

        /*
         * prix / montant_loyer_hors_charge : la colonne non pertinente n'est pas
         * NULL mais vaut "0" (une location stocke prix = "0", une vente stocke
         * montant_loyer_hors_charge = "0"). On la remet à NULL : sinon
         * `property.prix ?? property.montantLoyerHorsCharge` (Twig) affiche
         * "0 €" pour les locations, et COALESCE renvoie ce "0" aux filtres de
         * fourchette de prix. Un prix / loyer de 0 n'a de toute façon aucun sens.
         */
        $this->addSql('UPDATE property SET prix = NULL WHERE prix = 0');
        $this->addSql('UPDATE property SET montant_loyer_hors_charge = NULL WHERE montant_loyer_hors_charge = 0');

        // Année : premier groupe de 4 chiffres, sinon NULL.
        $this->addSql("UPDATE property SET annee_construction = REGEXP_SUBSTR(annee_construction, '[0-9]{4}') WHERE annee_construction IS NOT NULL");
        $this->addSql("UPDATE property SET annee_construction = NULL WHERE annee_construction IS NOT NULL AND annee_construction NOT REGEXP '^[0-9]{1,4}$'");

        foreach (self::DECIMAL_COLUMNS as $column => $declaration) {
            $this->addSql(\sprintf('ALTER TABLE property MODIFY %s %s DEFAULT NULL', $column, $declaration));
        }

        $this->addSql('ALTER TABLE property MODIFY annee_construction SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        foreach (array_keys(self::DECIMAL_COLUMNS) as $column) {
            $this->addSql(\sprintf('ALTER TABLE property MODIFY %s VARCHAR(255) DEFAULT NULL', $column));
        }

        $this->addSql('ALTER TABLE property MODIFY annee_construction VARCHAR(255) DEFAULT NULL');
    }
}
