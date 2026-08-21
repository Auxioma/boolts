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

namespace App\Tests\Architecture;

use App\Tests\Support\ProjectClassDiscovery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SourceDiscoveryTest extends TestCase
{
    #[DataProvider('sourceFileProvider')]
    public function testEveryPhpFileDeclaresAnAutoloadablePsr4Symbol(string $file): void
    {
        $symbol = ProjectClassDiscovery::symbolForFile($file);

        self::assertNotNull($symbol, \sprintf('Impossible de déterminer le symbole PSR-4 pour %s', $file));
        self::assertTrue(
            ProjectClassDiscovery::symbolExists($symbol),
            \sprintf('Le fichier %s devrait déclarer le symbole autoloadable %s.', $file, $symbol)
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function sourceFileProvider(): array
    {
        $files = array_merge(
            ProjectClassDiscovery::phpFilesIn('src/Entity'),
            ProjectClassDiscovery::phpFilesIn('src/Form'),
            ProjectClassDiscovery::phpFilesIn('src/Controller'),
        );

        $data = [];

        foreach ($files as $file) {
            $data[$file] = [$file];
        }

        return $data;
    }
}
