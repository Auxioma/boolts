<?php

namespace App\Tests\Controller;

use App\Controller\Public\HomeController;
use App\Entity\Search\PropertySearchSession;
use PHPUnit\Framework\TestCase;

final class HomeLastSearchSessionTest extends TestCase
{
    public function testCitySearchKeepsTheCityLabel(): void
    {
        $session = (new PropertySearchSession())
            ->setVille('Paris')
            ->setCp('75001')
            ->setPays('France');

        self::assertSame('Paris', $this->buildLabel($session));
    }

    public function testCountryOnlySearchUsesTheCountryLabel(): void
    {
        $session = (new PropertySearchSession())
            ->setVille(null)
            ->setCp(null)
            ->setPays('France');

        self::assertSame('France', $this->buildLabel($session));
    }

    public function testPostcodeSearchUsesPostcodeAndCountryLabel(): void
    {
        $session = (new PropertySearchSession())
            ->setVille(null)
            ->setCp('75001')
            ->setPays('France');

        self::assertSame('75001, France', $this->buildLabel($session));
    }

    private function buildLabel(PropertySearchSession $session): ?string
    {
        $controller = (new \ReflectionClass(HomeController::class))
            ->newInstanceWithoutConstructor();

        $method = new \ReflectionMethod(
            HomeController::class,
            'buildLastSearchSessionLabel'
        );

        return $method->invoke($controller, $session);
    }
}
