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

namespace App\Service;

use App\Repository\PropertyRepository;

final class NumericSlugGenerator
{
    public function __construct(
        private readonly PropertyRepository $propertyRepository,
    ) {
    }

    public function generate(int $length = 16): string
    {
        do {
            $slug = $this->generateRandomDigits($length);
        } while (null !== $this->propertyRepository->findOneBy([
            'slug' => $slug,
        ]));

        return $slug;
    }

    private function generateRandomDigits(int $length): string
    {
        $slug = '';

        for ($i = 0; $i < $length; ++$i) {
            $slug .= (string) random_int(0, 9);
        }

        return $slug;
    }
}
