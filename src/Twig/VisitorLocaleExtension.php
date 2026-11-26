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

namespace App\Twig;

use App\Repository\DeviseRepository;
use App\Repository\LanguesRepository;
use App\Repository\PaysRepository;
use App\Service\Intl\UrlCountryResolver;
use App\Service\Intl\VisitorCurrencyResolver;
use App\Service\Intl\VisitorLocaleResolver;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Languages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Pays, langue et devise du visiteur pour les templates communs : pastilles du
 * footer et modales de choix (pays / langue / devise) partagées avec le bandeau
 * de la homepage.
 */
final class VisitorLocaleExtension extends AbstractExtension
{
    /** @var array<string, mixed>|null */
    private ?array $modalData = null;

    public function __construct(
        private readonly UrlCountryResolver $urlCountryResolver,
        private readonly VisitorCurrencyResolver $visitorCurrencyResolver,
        private readonly VisitorLocaleResolver $visitorLocaleResolver,
        private readonly RequestStack $requestStack,
        private readonly PaysRepository $paysRepository,
        private readonly LanguesRepository $languesRepository,
        private readonly DeviseRepository $deviseRepository,
        /** @var list<string> Pays proposés au lancement (codes ISO, cf. app.launch_countries). */
        #[Autowire('%app.launch_countries%')]
        private readonly array $launchCountries,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('visitor_country_iso', $this->urlCountryResolver->currentIso(...)),
            new TwigFunction('visitor_currency_code', $this->currencyCode(...)),
            new TwigFunction('locale_modal_data', $this->modalData(...)),
        ];
    }

    /**
     * Code ISO 4217 de la devise d'affichage (EUR par défaut).
     */
    public function currencyCode(): string
    {
        return $this->visitorCurrencyResolver->resolve()?->getCode() ?? 'EUR';
    }

    /**
     * Données des modales de choix, calculées une seule fois par requête.
     *
     * @return array{
     *     countries: list<array{iso: string, name: string}>,
     *     languages: list<array{iso: string, name: string}>,
     *     currencies: list<array{code: string, name: string}>,
     *     suggestedCountry: string,
     *     suggestedLocale: string,
     *     suggestedCurrency: string,
     * }
     */
    public function modalData(): array
    {
        if (null !== $this->modalData) {
            return $this->modalData;
        }

        $request = $this->requestStack->getMainRequest();
        $locale = $request?->getLocale() ?? 'fr';

        /*
         * Pays et langue suggérés : pays détecté par IP s'il est francophone ou
         * anglophone (avec sa langue), sinon France / français.
         * Le pays suggéré doit faire partie des pays du lancement.
         */
        $visitorLocale = null !== $request ? $this->visitorLocaleResolver->resolve($request) : null;
        $countryLocale = $visitorLocale['countryLocale'] ?? null;
        $detectedCountry = strtoupper((string) ($visitorLocale['countryCode'] ?? ''));

        return $this->modalData = [
            'countries' => $this->countries($locale),
            'languages' => $this->languages(),
            'currencies' => $this->currencies($locale),
            'suggestedCountry' => null !== $countryLocale && \in_array($detectedCountry, $this->launchCountries, true) ? $detectedCountry : 'FR',
            'suggestedLocale' => $countryLocale ?? 'fr',
            'suggestedCurrency' => $this->visitorCurrencyResolver->automatic()?->getCode() ?? 'EUR',
        ];
    }

    /**
     * Pays du lancement présents en base, nommés dans la langue de la page et triés alphabétiquement.
     *
     * @return list<array{iso: string, name: string}>
     */
    private function countries(string $locale): array
    {
        $countries = [];

        foreach ($this->paysRepository->findBy(['iso' => $this->launchCountries]) as $pays) {
            $iso = strtoupper((string) $pays->getIso());

            $countries[] = [
                'iso' => $iso,
                'name' => Countries::exists($iso) ? Countries::getName($iso, $locale) : (string) $pays->getNom(),
            ];
        }

        $collator = new \Collator($locale);
        usort($countries, static fn (array $a, array $b): int => $collator->compare($a['name'], $b['name']));

        return $countries;
    }

    /**
     * Langues en base supportées par le site (VisitorLocaleResolver::SUPPORTED), chacune nommée dans sa propre langue.
     *
     * @return list<array{iso: string, name: string}>
     */
    private function languages(): array
    {
        $languages = [];

        foreach ($this->languesRepository->findBy(['iso' => VisitorLocaleResolver::SUPPORTED], ['id' => 'ASC']) as $langue) {
            $iso = strtolower((string) $langue->getIso());

            $languages[] = [
                'iso' => $iso,
                'name' => mb_convert_case(Languages::getName($iso, $iso), \MB_CASE_TITLE),
            ];
        }

        return $languages;
    }

    /**
     * Devises en base (avec code ISO 4217), nommées dans la langue de la page et triées alphabétiquement.
     *
     * @return list<array{code: string, name: string}>
     */
    private function currencies(string $locale): array
    {
        $currencies = [];

        foreach ($this->deviseRepository->findAll() as $devise) {
            $code = strtoupper((string) $devise->getCode());

            if ('' === $code) {
                continue;
            }

            $name = Currencies::exists($code) ? Currencies::getName($code, $locale) : (string) $devise->getNom();

            $currencies[] = [
                'code' => $code,
                'name' => mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1),
            ];
        }

        $collator = new \Collator($locale);
        usort($currencies, static fn (array $a, array $b): int => $collator->compare($a['name'], $b['name']));

        return $currencies;
    }
}
