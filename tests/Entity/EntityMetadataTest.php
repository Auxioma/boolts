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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\SchemaValidator;
use PHPUnit\Framework\Attributes\DataProvider;
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
            \sprintf('%s doit définir au moins un identifiant Doctrine.', $entityClass)
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
            $reflection = new \ReflectionClass($class);

            if ([] !== $reflection->getAttributes(ORM\Entity::class)) {
                $entities[] = $class;
            }
        }

        return ProjectClassDiscovery::asDataProvider($entities);
    }
}
