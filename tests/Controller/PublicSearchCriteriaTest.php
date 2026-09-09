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

use App\Controller\Public\SearchController;
use App\Entity\CategoryBienTransaction;
use App\Entity\SearchBar\FilterCityCountry;
use PHPUnit\Framework\TestCase;

final class PublicSearchCriteriaTest extends TestCase
{
    public function testCountrySelectionDoesNotBecomeACityFilter(): void
    {
        $criteria = $this->buildCriteria(
            (new FilterCityCountry())
                ->setTransactionType($this->transactionType())
                ->setFilter('France')
                ->setSelectedValue('France')
                ->setSelectedFeatureType('country')
                ->setSelectedCountryName('France')
                ->setSelectedCountryCode('FR')
        );

        self::assertNull($criteria['ville']);
        self::assertNull($criteria['cp']);
        self::assertSame('France', $criteria['pays']);
    }

    public function testPostcodeSelectionOnlyUsesThePostcodeFilter(): void
    {
        $criteria = $this->buildCriteria(
            (new FilterCityCountry())
                ->setTransactionType($this->transactionType())
                ->setFilter('75001')
                ->setSelectedValue('75001')
                ->setSelectedFeatureType('postcode')
                ->setSelectedCountryName('France')
                ->setSelectedCountryCode('FR')
                ->setSelectedPostalCode('75001')
        );

        self::assertNull($criteria['ville']);
        self::assertSame('75001', $criteria['cp']);
        self::assertSame('France', $criteria['pays']);
    }

    public function testCitySelectionDoesNotForceAPostcodeFilter(): void
    {
        $criteria = $this->buildCriteria(
            (new FilterCityCountry())
                ->setTransactionType($this->transactionType())
                ->setFilter('Paris')
                ->setSelectedValue('Paris')
                ->setSelectedFeatureType('place')
                ->setSelectedCountryName('France')
                ->setSelectedCountryCode('FR')
                ->setSelectedCityName('Paris')
                ->setSelectedPostalCode('75001')
        );

        self::assertSame('Paris', $criteria['ville']);
        self::assertNull($criteria['cp']);
        self::assertSame('France', $criteria['pays']);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCriteria(FilterCityCountry $filter): array
    {
        $controller = (new \ReflectionClass(SearchController::class))
            ->newInstanceWithoutConstructor();

        $method = new \ReflectionMethod(
            SearchController::class,
            'buildCriteriaFromFilter'
        );

        return $method->invoke($controller, $filter);
    }

    private function transactionType(): CategoryBienTransaction
    {
        $transactionType = new CategoryBienTransaction();

        $reflection = new \ReflectionProperty(
            CategoryBienTransaction::class,
            'id'
        );

        $reflection->setValue($transactionType, 1);

        return $transactionType;
    }
}
