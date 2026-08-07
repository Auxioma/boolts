<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Tests\Support\ProjectClassDiscovery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\SchemaValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class EntityMetadataTest extends KernelTestCase
{
    #[DataProvider('entityProvider')]
    public function testEveryEntityHasValidDoctrineMetadataAndIdentifier(string $entityClass): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getClassMetadata($entityClass);

        self::assertSame($entityClass, $metadata->getName());
        self::assertNotEmpty(
            $metadata->getIdentifierFieldNames(),
            sprintf('%s doit définir au moins un identifiant Doctrine.', $entityClass)
        );
    }

    public function testDoctrineMappingIsGloballyValid(): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $errors = (new SchemaValidator($entityManager))->validateMapping();

        self::assertSame(
            [],
            $errors,
            "Erreurs de mapping Doctrine détectées :\n".print_r($errors, true)
        );
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
