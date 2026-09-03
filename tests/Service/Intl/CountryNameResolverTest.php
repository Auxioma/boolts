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

namespace App\Tests\Service\Intl;

use App\Service\Intl\CountryNameResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CountryNameResolverTest extends TestCase
{
    #[DataProvider('codeProvider')]
    public function testToAlphaTwoCode(?string $input, ?string $expected): void
    {
        self::assertSame(
            $expected,
            (new CountryNameResolver())->toAlphaTwoCode($input)
        );
    }

    /**
     * @return iterable<string, array{0: ?string, 1: ?string}>
     */
    public static function codeProvider(): iterable
    {
        yield 'french name' => ['France', 'FR'];
        yield 'french name lowercase' => ['france', 'FR'];
        yield 'french name with accent' => ['Suède', 'SE'];
        yield 'english name' => ['Belgium', 'BE'];
        yield 'already a code' => ['FR', 'FR'];
        yield 'lowercase code' => ['be', 'BE'];
        yield 'null' => [null, null];
        yield 'blank' => ['   ', null];
        yield 'unknown label' => ['Pays Imaginaire', null];
    }

    public function testStoredCountryNameRoundTripsThroughTheFormCode(): void
    {
        $resolver = new CountryNameResolver();

        // Ce que la base contient (écrit par MapboxAddressTranslator).
        $stored = 'France';

        // Ce que reçoit CountryType pour présélectionner l'option.
        $code = $resolver->toAlphaTwoCode($stored);
        self::assertSame('FR', $code);

        // Ce qui est ré-enregistré après soumission : on retrouve un libellé.
        self::assertSame('France', $resolver->toName($code, 'fr'));
    }

    public function testToNameReturnsNullForUnknownCode(): void
    {
        self::assertNull((new CountryNameResolver())->toName('ZZ', 'fr'));
        self::assertNull((new CountryNameResolver())->toName(null, 'fr'));
    }

    public function testToNameFollowsRequestedLocale(): void
    {
        $resolver = new CountryNameResolver();

        self::assertSame('Allemagne', $resolver->toName('DE', 'fr'));
        self::assertSame('Germany', $resolver->toName('DE', 'en'));
    }
}
