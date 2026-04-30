<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Vich;

use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

final class ImagesDirectoryNamer implements DirectoryNamerInterface
{
    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if (\is_array($object)) {
            throw new \RuntimeException('Le directory namer attend un objet, pas un tableau.');
        }

        if (!method_exists($object, 'getId')) {
            throw new \RuntimeException(\sprintf('La classe "%s" doit avoir une méthode getId().', $object::class));
        }

        $id = $object->getId();

        if (null === $id) {
            return 'tmp';
        }

        return implode('/', mb_str_split((string) $id));
    }
}
