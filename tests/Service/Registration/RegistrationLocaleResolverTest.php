<?php

declare(strict_types=1);

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Tests\Service\Registration;

use App\Entity\Devise;
use App\Entity\FuseauHoraire;
use App\Entity\LangueParler;
use App\Entity\Langues;
use App\Entity\Pays;
use App\Entity\User;
use App\Repository\FuseauHoraireRepository;
use App\Repository\LangueParlerRepository;
use App\Repository\LanguesRepository;
use App\Repository\PaysRepository;
use App\Service\CloudflareLocationService;
use App\Service\GeoIpLocationService;
use App\Service\Registration\RegistrationLocaleResolver;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RegistrationLocaleResolverTest extends TestCase
{
    public function testBrowserHintsFillSpokenLanguageCurrencyAndTimeZone(): void
    {
        $euro = new Devise();
        $france = (new Pays())->setDevise($euro);
        $frLangueParler = (new LangueParler())->setCode('fr')->setName('Français');
        $frLangue = (new Langues())->setName('Français')->setIso('fr');
        $paris = (new FuseauHoraire())->setNom('Europe/Paris');

        $user = (new User())->setEmail('agence@example.test')->setPays($france);

        $resolver = $this->resolver(
            langueParler: ['fr' => $frLangueParler],
            langues: ['fr' => $frLangue],
            fuseau: ['Europe/Paris' => $paris],
            cloudflare: $this->cloudflareLocation(),
            expectFlush: true,
        );

        $resolver->apply($user, [
            'language' => 'fr',
            'locale' => 'fr-FR',
            'timeZone' => 'Europe/Paris',
        ]);

        self::assertTrue($user->getLangueParlers()->contains($frLangueParler));
        self::assertSame($frLangue, $user->getLangues());
        self::assertSame($euro, $user->getDevise());
        self::assertSame($paris, $user->getFuseauHoraire());
    }

    public function testFallsBackToCloudflareHeadersWhenNoBrowserHints(): void
    {
        $euro = new Devise();
        $belgium = (new Pays())->setDevise($euro);
        $brussels = (new FuseauHoraire())->setNom('Europe/Brussels');

        $user = (new User())->setEmail('agence@example.test');

        $resolver = $this->resolver(
            pays: ['BE' => $belgium],
            fuseau: ['Europe/Brussels' => $brussels],
            cloudflare: $this->cloudflareLocation(countryCode: 'BE', timezone: 'Europe/Brussels'),
            expectFlush: true,
        );

        $resolver->apply($user);

        self::assertSame($euro, $user->getDevise());
        self::assertSame($belgium, $user->getPays());
        self::assertSame($brussels, $user->getFuseauHoraire());
    }

    public function testExistingValuesAreNeverOverwritten(): void
    {
        $existingDevise = new Devise();
        $existingFuseau = (new FuseauHoraire())->setNom('America/New_York');
        $existingLangue = (new LangueParler())->setCode('en')->setName('Anglais');

        $user = (new User())->setEmail('agence@example.test');
        $user->setDevise($existingDevise);
        $user->setFuseauHoraire($existingFuseau);
        $user->addLangueParler($existingLangue);
        $user->setLangues((new Langues())->setName('Anglais')->setIso('en'));

        $resolver = $this->resolver(
            cloudflare: $this->cloudflareLocation(countryCode: 'FR', timezone: 'Europe/Paris'),
            expectFlush: false,
        );

        $resolver->apply($user, ['language' => 'fr', 'locale' => 'fr-FR', 'timeZone' => 'Europe/Paris']);

        self::assertSame($existingDevise, $user->getDevise());
        self::assertSame($existingFuseau, $user->getFuseauHoraire());
        self::assertCount(1, $user->getLangueParlers());
        self::assertTrue($user->getLangueParlers()->contains($existingLangue));
    }

    public function testUnknownTimeZoneIsCreatedAndPersisted(): void
    {
        $user = (new User())->setEmail('agence@example.test');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(FuseauHoraire::class));
        $entityManager->expects(self::once())->method('flush');

        $resolver = new RegistrationLocaleResolver(
            $entityManager,
            $this->repo(LangueParlerRepository::class),
            $this->repo(LanguesRepository::class),
            $this->repo(PaysRepository::class),
            $this->repo(FuseauHoraireRepository::class, []),
            $this->cloudflareLocation(timezone: 'Europe/Paris'),
            $this->geoIp(),
            new RequestStack(),
            new NullLogger(),
        );

        $resolver->apply($user, ['timeZone' => 'Europe/Paris']);

        self::assertInstanceOf(FuseauHoraire::class, $user->getFuseauHoraire());
        self::assertSame('Europe/Paris', $user->getFuseauHoraire()->getNom());
    }

    public function testInvalidTimeZoneStringIsIgnored(): void
    {
        $user = (new User())->setEmail('agence@example.test');

        $resolver = $this->resolver(expectFlush: false);

        $resolver->apply($user, ['timeZone' => 'Not/AZone']);

        self::assertNull($user->getFuseauHoraire());
    }

    /**
     * @param array<string, LangueParler> $langueParler
     * @param array<string, Langues>      $langues
     * @param array<string, Pays>         $pays
     * @param array<string, FuseauHoraire> $fuseau
     */
    private function resolver(
        array $langueParler = [],
        array $langues = [],
        array $pays = [],
        array $fuseau = [],
        ?CloudflareLocationService $cloudflare = null,
        bool $expectFlush = false,
    ): RegistrationLocaleResolver {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($expectFlush ? self::once() : self::never())->method('flush');

        return new RegistrationLocaleResolver(
            $entityManager,
            $this->repo(LangueParlerRepository::class, $langueParler, 'code'),
            $this->repo(LanguesRepository::class, $langues, 'iso'),
            $this->repo(PaysRepository::class, $pays, 'iso'),
            $this->repo(FuseauHoraireRepository::class, $fuseau, 'nom'),
            $cloudflare ?? $this->cloudflareLocation(),
            $this->geoIp(),
            new RequestStack(),
            new NullLogger(),
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T>     $class
     * @param array<string, mixed> $byField
     *
     * @return T
     */
    private function repo(string $class, array $byField = [], string $field = 'id'): object
    {
        $repo = $this->createStub($class);
        $repo->method('findOneBy')->willReturnCallback(
            static fn (array $criteria): ?object => $byField[$criteria[$field] ?? ''] ?? null,
        );

        return $repo;
    }

    private function cloudflareLocation(
        ?string $countryCode = null,
        ?string $timezone = null,
    ): CloudflareLocationService {
        $service = $this->createStub(CloudflareLocationService::class);
        $service->method('getLocation')->willReturn([
            'ip' => null,
            'city' => null,
            'countryCode' => $countryCode,
            'continent' => null,
            'region' => null,
            'regionCode' => null,
            'postalCode' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => $timezone,
            'isCloudflare' => null !== $countryCode || null !== $timezone,
        ]);

        return $service;
    }

    private function geoIp(): GeoIpLocationService
    {
        $service = $this->createStub(GeoIpLocationService::class);
        $service->method('locateIp')->willReturn(['success' => false]);

        return $service;
    }
}
