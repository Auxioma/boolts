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

namespace App\Tests\Entity;

use App\Tests\Support\ProjectClassDiscovery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EntityEnumTest extends TestCase
{
    #[DataProvider('enumProvider')]
    public function testEveryEntityEnumHasCasesAndUniqueBackedValues(string $enumClass): void
    {
        /** @var class-string<\UnitEnum> $enumClass */
        $cases = $enumClass::cases();

        self::assertNotEmpty($cases, \sprintf('%s doit définir au moins un case.', $enumClass));

        if (!is_subclass_of($enumClass, \BackedEnum::class)) {
            return;
        }

        $values = array_map(
            static fn (\BackedEnum $case): int|string => $case->value,
            $cases
        );

        self::assertSame(
            \count($values),
            \count(array_unique($values, \SORT_REGULAR)),
            \sprintf('%s contient des valeurs backed dupliquées.', $enumClass)
        );
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function enumProvider(): array
    {
        return ProjectClassDiscovery::asDataProvider(
            ProjectClassDiscovery::enumsIn('src/Entity')
        );
    }
}
