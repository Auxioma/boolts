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

namespace App\Tests\Support;

final class ProjectClassDiscovery
{
    /**
     * @return list<class-string>
     */
    public static function concreteClassesIn(string $relativeDirectory): array
    {
        $classes = [];

        foreach (self::phpFilesIn($relativeDirectory) as $file) {
            $symbol = self::symbolForFile($file);

            if (null === $symbol || !class_exists($symbol)) {
                continue;
            }

            $reflection = new \ReflectionClass($symbol);

            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait() || $reflection->isEnum()) {
                continue;
            }

            /* @var class-string $symbol */
            $classes[] = $symbol;
        }

        sort($classes);

        return array_values(array_unique($classes));
    }

    /**
     * @return list<class-string>
     */
    public static function classesIn(string $relativeDirectory): array
    {
        $classes = [];

        foreach (self::phpFilesIn($relativeDirectory) as $file) {
            $symbol = self::symbolForFile($file);

            if (null !== $symbol && class_exists($symbol)) {
                /* @var class-string $symbol */
                $classes[] = $symbol;
            }
        }

        sort($classes);

        return array_values(array_unique($classes));
    }

    /**
     * @return list<class-string>
     */
    public static function enumsIn(string $relativeDirectory): array
    {
        $enums = [];

        foreach (self::phpFilesIn($relativeDirectory) as $file) {
            $symbol = self::symbolForFile($file);

            if (null !== $symbol && enum_exists($symbol)) {
                /* @var class-string $symbol */
                $enums[] = $symbol;
            }
        }

        sort($enums);

        return array_values(array_unique($enums));
    }

    /**
     * @return list<string>
     */
    public static function phpFilesIn(string $relativeDirectory): array
    {
        $directory = self::projectRoot().'/'.mb_trim($relativeDirectory, '/');

        if (!is_dir($directory)) {
            throw new \RuntimeException(\sprintf('Répertoire introuvable : %s', $directory));
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== mb_strtolower($file->getExtension())) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    public static function symbolForFile(string $absoluteFile): ?string
    {
        $root = mb_rtrim(str_replace('\\', '/', self::projectRoot()), '/').'/';
        $file = str_replace('\\', '/', $absoluteFile);

        if (!str_starts_with($file, $root.'src/')) {
            return null;
        }

        $relative = mb_substr($file, mb_strlen($root.'src/'));

        if (!str_ends_with($relative, '.php')) {
            return null;
        }

        $withoutExtension = mb_substr($relative, 0, -4);

        return 'App\\'.str_replace('/', '\\', $withoutExtension);
    }

    public static function symbolExists(string $symbol): bool
    {
        return class_exists($symbol)
            || interface_exists($symbol)
            || trait_exists($symbol)
            || enum_exists($symbol);
    }

    public static function projectRoot(): string
    {
        return \dirname(__DIR__, 2);
    }

    /**
     * @param list<class-string> $classes
     *
     * @return array<string, array{0: class-string}>
     */
    public static function asDataProvider(array $classes): array
    {
        $data = [];

        foreach ($classes as $class) {
            $data[$class] = [$class];
        }

        return $data;
    }
}
