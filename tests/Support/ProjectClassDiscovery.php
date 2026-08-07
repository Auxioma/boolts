<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Tests générés pour le projet Boolts.
 */

namespace App\Tests\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;

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

            if ($symbol === null || !class_exists($symbol)) {
                continue;
            }

            $reflection = new ReflectionClass($symbol);

            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait() || $reflection->isEnum()) {
                continue;
            }

            /** @var class-string $symbol */
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

            if ($symbol !== null && class_exists($symbol)) {
                /** @var class-string $symbol */
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

            if ($symbol !== null && enum_exists($symbol)) {
                /** @var class-string $symbol */
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
        $directory = self::projectRoot().'/'.trim($relativeDirectory, '/');

        if (!is_dir($directory)) {
            throw new RuntimeException(sprintf('Répertoire introuvable : %s', $directory));
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    public static function symbolForFile(string $absoluteFile): ?string
    {
        $root = rtrim(str_replace('\\', '/', self::projectRoot()), '/').'/';
        $file = str_replace('\\', '/', $absoluteFile);

        if (!str_starts_with($file, $root.'src/')) {
            return null;
        }

        $relative = substr($file, strlen($root.'src/'));

        if (!str_ends_with($relative, '.php')) {
            return null;
        }

        $withoutExtension = substr($relative, 0, -4);

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
        return dirname(__DIR__, 2);
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
