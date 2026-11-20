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

namespace App\Service\Intl;

use App\Entity\User;

/**
 * Langue d’un e-mail envoyé à un utilisateur : sa langue préférée si elle est supportée,
 * sinon le français. Utile hors requête HTTP (workers Messenger, commandes, back-office).
 */
final class RecipientLocale
{
    public const DEFAULT = 'fr';

    public static function for(?User $user): string
    {
        $iso = $user?->getLangues()?->getIso();

        return \in_array($iso, VisitorLocaleResolver::SUPPORTED, true) ? $iso : self::DEFAULT;
    }
}
