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

namespace App\Vich;

use App\Entity\User;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

final class AvatarDirectoryNamer implements DirectoryNamerInterface
{
    public function directoryName(
        object|array $object,
        PropertyMapping $mapping,
    ): string {
        if (!$object instanceof User) {
            return 'tmp';
        }

        $userId = $object->getId();

        if (null === $userId) {
            return 'tmp';
        }

        return implode('/', mb_str_split((string) $userId));
    }
}
