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

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Intl\Countries;

/**
 * Backfill de property.code_iso_pays pour les annonces déjà en base.
 *
 * La colonne (ajoutée par {@see Version20260910120000}) n'est renseignée que
 * pour les biens saisis / modifiés après sa mise en place. Or la page d'accueil,
 * la recherche et les « biens similaires » filtrent désormais le pays via ce
 * code ISO plutôt que via le libellé traduit "property_translation.pays". Sans
 * reprise, tous les biens antérieurs disparaîtraient de ces listes.
 *
 * On retrouve le code ISO 3166-1 alpha-2 à partir du libellé stocké, en
 * s'appuyant sur les noms de pays connus de Symfony Intl (fr + en), insensible
 * à la casse et aux accents.
 */
final class Version20260910130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'property : backfill de code_iso_pays depuis le libellé pays des traductions.';
    }

    public function up(Schema $schema): void
    {
        $labelToIsoCode = $this->labelToIsoCodeMap();

        $storedLabels = $this->connection->fetchFirstColumn(
            "SELECT DISTINCT pt.pays
             FROM property_translation pt
             INNER JOIN property p ON p.id = pt.translatable_id
             WHERE p.code_iso_pays IS NULL
               AND pt.pays IS NOT NULL
               AND TRIM(pt.pays) <> ''"
        );

        // code ISO => libellés bruts (tels que stockés) à rattacher.
        $isoCodeToStoredLabels = [];

        foreach ($storedLabels as $storedLabel) {
            $key = $this->stripAccents($this->normalize((string) $storedLabel));
            $isoCode = $labelToIsoCode[$key] ?? null;

            if (null !== $isoCode) {
                $isoCodeToStoredLabels[$isoCode][] = (string) $storedLabel;
            }
        }

        foreach ($isoCodeToStoredLabels as $isoCode => $labels) {
            $this->addSql(
                'UPDATE property p
                 INNER JOIN property_translation pt ON pt.translatable_id = p.id
                 SET p.code_iso_pays = :isoCode
                 WHERE p.code_iso_pays IS NULL
                   AND pt.pays IN (:labels)',
                [
                    'isoCode' => $isoCode,
                    'labels' => array_values(array_unique($labels)),
                ],
                [
                    'labels' => ArrayParameterType::STRING,
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        /*
         * Reprise de données non réversible : on ne sait pas distinguer un code
         * posé par ce backfill d'un code saisi légitimement depuis Mapbox.
         */
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Libellé de pays normalisé (minuscules, sans accents) => code ISO alpha-2.
     *
     * @return array<string, string>
     */
    private function labelToIsoCodeMap(): array
    {
        $map = [];

        foreach (['fr', 'en'] as $locale) {
            foreach (Countries::getNames($locale) as $isoCode => $name) {
                $map[$this->stripAccents($this->normalize($name))] = $isoCode;
            }
        }

        return $map;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(mb_trim($value));

        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    private function stripAccents(string $value): string
    {
        if (class_exists(\Normalizer::class)) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D) ?: $value;
        }

        return preg_replace('/[\x{0300}-\x{036f}]/u', '', $value) ?? $value;
    }
}
