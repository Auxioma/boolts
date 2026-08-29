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

namespace App\Service\Booster;

/**
 * Levée lorsqu'un boost d'annonce ne peut pas être appliqué :
 * annonce inéligible, déjà boostée ou aucun boost disponible.
 *
 * Le message est destiné à être affiché tel quel à l'agence.
 */
final class BoostException extends \RuntimeException
{
}
