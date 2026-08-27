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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

final class ControllerRouteTest extends KernelTestCase
{
    #[DataProvider('controllerProvider')]
    public function testEveryNonCrudControllerIsReferencedByAtLeastOneRoute(string $controllerClass): void
    {
        if (is_subclass_of($controllerClass, AbstractCrudController::class)) {
            self::assertTrue(true);

            return;
        }

        self::bootKernel();

        $router = self::getContainer()->get(RouterInterface::class);
        $routes = $router->getRouteCollection();
        $matchedRoutes = [];

        foreach ($routes as $name => $route) {
            $controller = $route->getDefault('_controller');

            if (!\is_string($controller)) {
                continue;
            }

            $configuredClass = str_contains($controller, '::')
                ? explode('::', $controller, 2)[0]
                : $controller;

            if ($configuredClass === $controllerClass) {
                $matchedRoutes[] = $name;
            }
        }

        self::assertNotEmpty(
            $matchedRoutes,
            \sprintf('Aucune route Symfony ne référence le contrôleur %s.', $controllerClass)
        );
    }

    public function testEveryApplicationControllerRouteTargetsAnExistingPublicAction(): void
    {
        self::bootKernel();

        $router = self::getContainer()->get(RouterInterface::class);
        $routes = $router->getRouteCollection();
        $tested = 0;

        foreach ($routes as $name => $route) {
            $controller = $route->getDefault('_controller');

            if (!\is_string($controller) || !str_starts_with($controller, 'App\\Controller\\')) {
                continue;
            }

            if (str_contains($controller, '::')) {
                [$class, $method] = explode('::', $controller, 2);
            } else {
                $class = $controller;
                $method = '__invoke';
            }

            self::assertTrue(class_exists($class), \sprintf('Route %s : classe %s introuvable.', $name, $class));
            self::assertTrue(method_exists($class, $method), \sprintf('Route %s : action %s::%s() introuvable.', $name, $class, $method));

            $reflectionMethod = new \ReflectionMethod($class, $method);
            self::assertTrue($reflectionMethod->isPublic(), \sprintf('Route %s : %s::%s() doit être publique.', $name, $class, $method));
            ++$tested;
        }

        self::assertGreaterThan(0, $tested, 'Aucune route applicative App\\Controller n’a été trouvée.');
    }

    public function testStripeWebhookRouteMatchesConfiguredDestination(): void
    {
        self::bootKernel();

        $router = self::getContainer()->get(RouterInterface::class);
        $route = $router->getRouteCollection()->get('stripe_webhook');

        self::assertNotNull($route);
        self::assertSame('/webhook/stripe', $route->getPath());
        self::assertSame(['POST'], $route->getMethods());
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
