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
use App\Tests\Support\TestValueFactory;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EntityAccessorTest extends TestCase
{
    #[DataProvider('entityProvider')]
    public function testMappedScalarAccessorsCanRoundTripValues(string $entityClass): void
    {
        $reflection = new \ReflectionClass($entityClass);
        $entity = self::instantiate($reflection);
        $testedProperties = 0;

        foreach ($reflection->getProperties() as $property) {
            if ([] === $property->getAttributes(ORM\Column::class)) {
                continue;
            }

            $propertyName = $property->getName();
            $suffix = ucfirst($propertyName);
            $setterName = 'set'.$suffix;
            $getterName = self::findGetter($reflection, $suffix);

            if (null === $getterName || !$reflection->hasMethod($setterName)) {
                continue;
            }

            $setter = $reflection->getMethod($setterName);
            $getter = $reflection->getMethod($getterName);

            if (!self::isUsableSetter($setter) || !$getter->isPublic() || 0 !== $getter->getNumberOfRequiredParameters()) {
                continue;
            }

            $parameter = $setter->getParameters()[0];
            [$supported, $value] = TestValueFactory::forParameter($parameter);

            if (!$supported) {
                continue;
            }

            $returnValue = $setter->invoke($entity, $value);
            $actual = $getter->invoke($entity);

            self::assertSetterReturnContract($setter, $entity, $returnValue);
            self::assertRoundTrip($propertyName, $value, $actual, $entityClass);
            ++$testedProperties;
        }

        // Certaines entités de liaison peuvent ne contenir aucun couple get/set scalaire exploitable.
        // Dans ce cas, le chargement et l'instanciation restent une assertion utile, tandis que
        // EntityMetadataTest vérifie leur mapping Doctrine.
        if (0 === $testedProperties) {
            self::assertNotEmpty(
                $reflection->getAttributes(ORM\Entity::class),
                \sprintf('%s doit rester une entité Doctrine chargeable.', $entityClass)
            );
        }
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function entityProvider(): array
    {
        $entities = [];

        foreach (ProjectClassDiscovery::concreteClassesIn('src/Entity') as $class) {
            $reflection = new \ReflectionClass($class);

            if ([] !== $reflection->getAttributes(ORM\Entity::class)) {
                $entities[] = $class;
            }
        }

        return ProjectClassDiscovery::asDataProvider($entities);
    }

    private static function instantiate(\ReflectionClass $reflection): object
    {
        $constructor = $reflection->getConstructor();

        if (null === $constructor || 0 === $constructor->getNumberOfRequiredParameters()) {
            return $reflection->newInstance();
        }

        return $reflection->newInstanceWithoutConstructor();
    }

    private static function findGetter(\ReflectionClass $reflection, string $suffix): ?string
    {
        foreach (['get'.$suffix, 'is'.$suffix, 'has'.$suffix] as $method) {
            if ($reflection->hasMethod($method)) {
                return $method;
            }
        }

        return null;
    }

    private static function isUsableSetter(\ReflectionMethod $setter): bool
    {
        return $setter->isPublic()
            && 1 === $setter->getNumberOfParameters()
            && $setter->getNumberOfRequiredParameters() <= 1;
    }

    private static function assertSetterReturnContract(\ReflectionMethod $setter, object $entity, mixed $returnValue): void
    {
        $returnType = $setter->getReturnType();

        if (!$returnType instanceof \ReflectionNamedType) {
            return;
        }

        if (\in_array($returnType->getName(), ['self', 'static'], true)) {
            self::assertSame($entity, $returnValue, \sprintf('%s() doit retourner l’entité courante.', $setter->getName()));
        }
    }

    private static function assertRoundTrip(string $propertyName, mixed $expected, mixed $actual, string $entityClass): void
    {
        $message = \sprintf('%s::$%s ne respecte pas le contrat setter/getter.', $entityClass, $propertyName);

        if ('roles' === $propertyName && \is_array($expected) && \is_array($actual)) {
            foreach ($expected as $role) {
                self::assertContains($role, $actual, $message);
            }

            return;
        }

        if ($expected instanceof \DateTimeInterface && $actual instanceof \DateTimeInterface) {
            self::assertSame($expected, $actual, $message);

            return;
        }

        if ($expected instanceof \UnitEnum || $expected instanceof \BackedEnum) {
            self::assertSame($expected, $actual, $message);

            return;
        }

        if (\is_object($expected) && \is_object($actual)) {
            self::assertSame($expected, $actual, $message);

            return;
        }

        self::assertSame($expected, $actual, $message);
    }
}
