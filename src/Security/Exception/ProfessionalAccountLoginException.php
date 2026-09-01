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

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Levée lorsqu'un compte professionnel (ROLE_AGENCE) tente de se connecter
 * depuis la page de connexion visiteur (/login), réservée aux comptes ROLE_USER.
 */
final class ProfessionalAccountLoginException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Ce compte est un compte professionnel. Merci de vous connecter depuis votre espace pro.';
    }
}
