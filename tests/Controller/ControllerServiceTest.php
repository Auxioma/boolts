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

namespace App\Tests\Controller;

use App\Tests\Support\ProjectClassDiscovery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ControllerServiceTest extends KernelTestCase
{
    #[DataProvider('controllerProvider')]
    public function testEveryControllerCanBeAutowiredBySymfony(string $controllerClass): void
    {
        self::bootKernel();

        $container = self::getContainer();

        self::assertTrue(
            $container->has($controllerClass),
            \sprintf('Le contrôleur %s doit être enregistré comme service Symfony.', $controllerClass)
        );

        self::assertInstanceOf($controllerClass, $container->get($controllerClass));
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function controllerProvider(): array
    {
        $controllers = [];

        foreach (ProjectClassDiscovery::concreteClassesIn('src/Controller') as $class) {
            $reflection = new \ReflectionClass($class);

            if ($reflection->isSubclassOf(AbstractController::class)) {
                $controllers[] = $class;
            }
        }

        return ProjectClassDiscovery::asDataProvider($controllers);
    }
}
