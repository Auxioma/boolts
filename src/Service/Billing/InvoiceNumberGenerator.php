<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Service\Billing;

use Doctrine\DBAL\Connection;

/**
 * Attribue le prochain numéro de facture Boolts.
 *
 * Le compteur vit dans la table {@see invoice_number_sequence}. L’allocation
 * repose sur l’astuce MySQL `LAST_INSERT_ID(expr)` : l’UPDATE est atomique et
 * verrouille la ligne le temps de la transaction, tandis que `LAST_INSERT_ID()`
 * est propre à la session — deux requêtes concurrentes ne peuvent donc jamais
 * recevoir le même numéro.
 *
 * Format : « I-100001 », « I-100002 », … (première facture émise = I-100001).
 */
final readonly class InvoiceNumberGenerator
{
    private const string PREFIX = 'I-';
    private const string SEQUENCE_KEY = 'invoice';

    public function __construct(
        private Connection $connection,
    ) {
    }

    public function next(): string
    {
        $updated = $this->connection->executeStatement(
            'UPDATE invoice_number_sequence
                SET `last_value` = LAST_INSERT_ID(`last_value` + 1)
                WHERE sequence_key = :key',
            ['key' => self::SEQUENCE_KEY],
        );

        if (1 !== $updated) {
            throw new \RuntimeException(
                'Séquence de numérotation des factures introuvable (exécutez les migrations).'
            );
        }

        $value = (int) $this->connection->fetchOne('SELECT LAST_INSERT_ID()');

        return self::PREFIX.$value;
    }
}
