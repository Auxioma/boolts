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

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Repository\CategoryBienRepository;
use App\Repository\CategoryBienTransactionRepository;
use App\Service\FooterCitiesProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Countries;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * {{ footer_property_types() }} → toutes les combinaisons CategoryBien × CategoryBienTransaction.
 * Les libellés viennent des traductions des entités (name selon la locale courante).
 *
 * {{ footer_country_slug() }} → slug du pays du visiteur (IP) dans la langue du site (ex. « allemagne », « germany »).
 */
final class FooterPropertyTypesExtension extends AbstractExtension
{
    public function __construct(
        private readonly CategoryBienRepository $categoryBienRepository,
        private readonly CategoryBienTransactionRepository $categoryBienTransactionRepository,
        private readonly FooterCitiesProvider $footerCitiesProvider,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('footer_property_types', $this->getCombinations(...)),
            new TwigFunction('footer_country_slug', $this->getCountrySlug(...)),
        ];
    }

    public function getCountrySlug(): string
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';
        $countryCode = $this->footerCitiesProvider->getCountryCode();

        try {
            $countryName = Countries::getName($countryCode, $locale);
        } catch (\Throwable) {
            $countryName = Countries::getName('FR', $locale);
        }

        return (new AsciiSlugger())->slug($countryName)->lower()->toString();
    }

    /**
     * @return list<array{category: CategoryBien, transaction: CategoryBienTransaction}>
     */
    public function getCombinations(): array
    {
        $categories = $this->categoryBienRepository->findBy([], ['id' => 'ASC']);
        $transactions = $this->categoryBienTransactionRepository->findBy([], ['id' => 'ASC']);

        $combinations = [];

        foreach ($categories as $category) {
            foreach ($transactions as $transaction) {
                $combinations[] = [
                    'category' => $category,
                    'transaction' => $transaction,
                ];
            }
        }

        return $combinations;
    }
}
