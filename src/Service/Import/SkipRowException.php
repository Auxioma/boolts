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
 * Signale qu'une ligne du CSV ne peut pas être importée (donnée requise
 * manquante ou association introuvable) et doit être reportée comme erreur
 * sans interrompre le reste de l'import.
 */
final class SkipRowException extends \RuntimeException
{
}
