<?php

declare(strict_types=1);

namespace App\Tests\Support;

use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use UnitEnum;

final class TestValueFactory
{
    /**
     * @return array{0: bool, 1: mixed}
     */
    public static function forParameter(ReflectionParameter $parameter): array
    {
        return self::forType($parameter->getType(), $parameter->getName());
    }

    /**
     * @return array{0: bool, 1: mixed}
     */
    public static function forType(?ReflectionType $type, string $name = ''): array
    {
        if ($type === null || $type instanceof ReflectionIntersectionType) {
            return [false, null];
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType->getName() === 'null') {
                    continue;
                }

                [$supported, $value] = self::forType($unionType, $name);

                if ($supported) {
                    return [true, $value];
                }
            }

            return [false, null];
        }

        if (!$type instanceof ReflectionNamedType) {
            return [false, null];
        }

        $typeName = $type->getName();

        if ($type->isBuiltin()) {
            return match ($typeName) {
                'string' => [true, self::stringValue($name)],
                'int' => [true, 42],
                'float' => [true, 42.5],
                'bool' => [true, true],
                'array' => [true, self::arrayValue($name)],
                'iterable' => [true, ['test']],
                'mixed' => [true, 'test-value'],
                default => [false, null],
            };
        }

        if (is_a($typeName, DateTimeInterface::class, true)) {
            return [true, new DateTimeImmutable('2026-08-07 12:00:00')];
        }

        if (enum_exists($typeName)) {
            /** @var class-string<UnitEnum> $typeName */
            $cases = $typeName::cases();

            return $cases === [] ? [false, null] : [true, $cases[0]];
        }

        if (interface_exists($typeName)) {
            return [false, null];
        }

        if (!class_exists($typeName)) {
            return [false, null];
        }

        $reflection = new ReflectionClass($typeName);

        if (!$reflection->isInstantiable()) {
            return [false, null];
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0) {
            return [true, $reflection->newInstance()];
        }

        return [true, $reflection->newInstanceWithoutConstructor()];
    }

    private static function stringValue(string $name): string
    {
        $normalized = strtolower($name);

        return match (true) {
            str_contains($normalized, 'email') => 'phpunit13@boolts.test',
            str_contains($normalized, 'url') => 'https://example.test',
            str_contains($normalized, 'slug') => 'phpunit-13-test',
            str_contains($normalized, 'phone'), str_contains($normalized, 'telephone') => '+33600000000',
            str_contains($normalized, 'postal'), str_contains($normalized, 'codepostal') => '75001',
            str_contains($normalized, 'latitude') => '48.8566000',
            str_contains($normalized, 'longitude') => '2.3522000',
            str_contains($normalized, 'price'), str_contains($normalized, 'prix'), str_contains($normalized, 'montant') => '1000.00',
            default => 'phpunit-13-test',
        };
    }

    /**
     * @return array<mixed>
     */
    private static function arrayValue(string $name): array
    {
        return strtolower($name) === 'roles'
            ? ['ROLE_TEST']
            : ['phpunit-13-test'];
    }
}
