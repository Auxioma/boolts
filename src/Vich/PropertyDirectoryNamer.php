<?php

namespace App\Vich;

use App\Entity\Property;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

final class PropertyDirectoryNamer implements DirectoryNamerInterface
{
    public function directoryName(
        object|array $object,
        PropertyMapping $mapping
    ): string {

        if (\is_array($object)) {
            throw new \RuntimeException(
                'Le directory namer attend un objet.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SI L'OBJET EST PROPERTY
        |--------------------------------------------------------------------------
        */

        if ($object instanceof Property) {

            $propertyId = $object->getId();

            if (null === $propertyId) {
                return 'tmp';
            }

            return implode('/', mb_str_split((string) $propertyId));
        }

        /*
        |--------------------------------------------------------------------------
        | SI L'OBJET EST PROPERTYIMAGE
        |--------------------------------------------------------------------------
        */

        if (method_exists($object, 'getProperty')) {

            $property = $object->getProperty();

            if (!$property instanceof Property) {
                return 'tmp';
            }

            $propertyId = $property->getId();

            if (null === $propertyId) {
                return 'tmp';
            }

            return implode('/', mb_str_split((string) $propertyId));
        }

        return 'tmp';
    }
}