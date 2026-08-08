<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Tests\Support\ProjectClassDiscovery;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class EntityCollectionInitializationTest extends TestCase
{
    #[DataProvider('entityProvider')]
    public function testMappedToManyCollectionsAreInitializedByConstructor(string $entityClass): void
    {
        $reflection = new ReflectionClass($entityClass);
        $constructor = $reflection->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            self::assertTrue(true);

            return;
        }

        $entity = $reflection->newInstance();
        $tested = 0;

        foreach ($reflection->getProperties() as $property) {
            $isCollectionAssociation = $property->getAttributes(ORM\OneToMany::class) !== []
                || $property->getAttributes(ORM\ManyToMany::class) !== [];

            if (!$isCollectionAssociation) {
                continue;
            }

            $getter = 'get'.ucfirst($property->getName());

            if (!$reflection->hasMethod($getter) || !$reflection->getMethod($getter)->isPublic()) {
                continue;
            }

            $value = $reflection->getMethod($getter)->invoke($entity);

            self::assertInstanceOf(
                Collection::class,
                $value,
                sprintf('%s::$%s doit être initialisée comme Collection Doctrine.', $entityClass, $property->getName())
            );
            ++$tested;
        }

        if ($tested === 0) {
            self::assertNotEmpty(
                $reflection->getAttributes(ORM\Entity::class),
                sprintf('%s doit rester une entité Doctrine chargeable.', $entityClass)
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
            $reflection = new ReflectionClass($class);

            if ($reflection->getAttributes(ORM\Entity::class) !== []) {
                $entities[] = $class;
            }
        }

        return ProjectClassDiscovery::asDataProvider($entities);
    }
}
