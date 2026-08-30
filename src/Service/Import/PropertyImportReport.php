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

namespace App\Service\Import;

/**
 * Résultat d'un import CSV de biens immobiliers.
 *
 * Agrège le nombre de lignes traitées, de biens créés, ainsi que les
 * erreurs (ligne ignorée) et avertissements (ligne importée mais partielle,
 * par exemple une image inaccessible) rencontrés.
 */
final class PropertyImportReport
{
    private int $processed = 0;

    private int $created = 0;

    /**
     * @var list<string>
     */
    private array $errors = [];

    /**
     * @var list<string>
     */
    private array $warnings = [];

    public function addProcessed(): void
    {
        ++$this->processed;
    }

    public function addCreated(): void
    {
        ++$this->created;
    }

    /**
     * @param int|string $line Numéro de ligne du fichier (en-tête = ligne 1)
     */
    public function addError(int|string $line, string $message): void
    {
        $this->errors[] = \sprintf('Ligne %s : %s', $line, $message);
    }

    public function addWarning(int|string $line, string $message): void
    {
        $this->warnings[] = \sprintf('Ligne %s : %s', $line, $message);
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return list<string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }

    public function summaryLine(): string
    {
        return \sprintf(
            '%d ligne(s) traitée(s), %d bien(s) créé(s), %d erreur(s), %d avertissement(s).',
            $this->processed,
            $this->created,
            \count($this->errors),
            \count($this->warnings),
        );
    }
}
