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

namespace App\Entity\SearchBar;

use App\Entity\CategoryBienTransaction;

class FilterCityCountry
{
    private ?string $filter = null;

    private ?CategoryBienTransaction $transactionType = null;

    private ?string $selectedValue = null;

    private ?string $selectedMapboxId = null;

    private ?string $selectedFeatureType = null;

    private ?string $selectedCountryName = null;

    private ?string $selectedCountryCode = null;

    private ?string $selectedRegionName = null;

    private ?string $selectedCityName = null;

    private ?string $selectedPostalCode = null;

    private ?string $selectedLatitude = null;

    private ?string $selectedLongitude = null;

    private ?string $selectedFullAddress = null;

    private ?string $selectedLocale = null;

    private ?string $selectedLocationJson = null;

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): self
    {
        $this->filter = $filter ? mb_trim($filter) : null;

        return $this;
    }

    public function getTransactionType(): ?CategoryBienTransaction
    {
        return $this->transactionType;
    }

    public function setTransactionType(?CategoryBienTransaction $transactionType): self
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    public function getSelectedValue(): ?string
    {
        return $this->selectedValue;
    }

    public function setSelectedValue(?string $selectedValue): self
    {
        $this->selectedValue = $selectedValue ? mb_trim($selectedValue) : null;

        return $this;
    }

    public function getSelectedMapboxId(): ?string
    {
        return $this->selectedMapboxId;
    }

    public function setSelectedMapboxId(?string $selectedMapboxId): self
    {
        $this->selectedMapboxId = $selectedMapboxId ? mb_trim($selectedMapboxId) : null;

        return $this;
    }

    public function getSelectedFeatureType(): ?string
    {
        return $this->selectedFeatureType;
    }

    public function setSelectedFeatureType(?string $selectedFeatureType): self
    {
        $this->selectedFeatureType = $selectedFeatureType ? mb_trim($selectedFeatureType) : null;

        return $this;
    }

    public function getSelectedCountryName(): ?string
    {
        return $this->selectedCountryName;
    }

    public function setSelectedCountryName(?string $selectedCountryName): self
    {
        $this->selectedCountryName = $selectedCountryName ? mb_trim($selectedCountryName) : null;

        return $this;
    }

    public function getSelectedCountryCode(): ?string
    {
        return $this->selectedCountryCode;
    }

    public function setSelectedCountryCode(?string $selectedCountryCode): self
    {
        $this->selectedCountryCode = $selectedCountryCode
            ? mb_strtoupper(mb_trim($selectedCountryCode))
            : null;

        return $this;
    }

    public function getSelectedRegionName(): ?string
    {
        return $this->selectedRegionName;
    }

    public function setSelectedRegionName(?string $selectedRegionName): self
    {
        $this->selectedRegionName = $selectedRegionName ? mb_trim($selectedRegionName) : null;

        return $this;
    }

    public function getSelectedCityName(): ?string
    {
        return $this->selectedCityName;
    }

    public function setSelectedCityName(?string $selectedCityName): self
    {
        $this->selectedCityName = $selectedCityName ? mb_trim($selectedCityName) : null;

        return $this;
    }

    public function getSelectedPostalCode(): ?string
    {
        return $this->selectedPostalCode;
    }

    public function setSelectedPostalCode(?string $selectedPostalCode): self
    {
        $this->selectedPostalCode = $selectedPostalCode ? mb_trim($selectedPostalCode) : null;

        return $this;
    }

    public function getSelectedLatitude(): ?string
    {
        return $this->selectedLatitude;
    }

    public function setSelectedLatitude(?string $selectedLatitude): self
    {
        $this->selectedLatitude = $selectedLatitude ? mb_trim($selectedLatitude) : null;

        return $this;
    }

    public function getSelectedLongitude(): ?string
    {
        return $this->selectedLongitude;
    }

    public function setSelectedLongitude(?string $selectedLongitude): self
    {
        $this->selectedLongitude = $selectedLongitude ? mb_trim($selectedLongitude) : null;

        return $this;
    }

    public function getSelectedFullAddress(): ?string
    {
        return $this->selectedFullAddress;
    }

    public function setSelectedFullAddress(?string $selectedFullAddress): self
    {
        $this->selectedFullAddress = $selectedFullAddress ? mb_trim($selectedFullAddress) : null;

        return $this;
    }

    public function getSelectedLocale(): ?string
    {
        return $this->selectedLocale;
    }

    public function setSelectedLocale(?string $selectedLocale): self
    {
        $this->selectedLocale = $selectedLocale ? mb_trim($selectedLocale) : null;

        return $this;
    }

    public function getSelectedLocationJson(): ?string
    {
        return $this->selectedLocationJson;
    }

    public function setSelectedLocationJson(?string $selectedLocationJson): self
    {
        $this->selectedLocationJson = $selectedLocationJson ? mb_trim($selectedLocationJson) : null;

        return $this;
    }

    public function getSelectedLocation(): array
    {
        if (!$this->selectedLocationJson) {
            return [];
        }

        $location = json_decode($this->selectedLocationJson, true);

        if (!\is_array($location)) {
            return [];
        }

        return $location;
    }
}
