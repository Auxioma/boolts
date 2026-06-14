<?php

namespace App\Entity\SearchBar;

class FilterCityCountry
{
    private ?string $filter = null;

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): self
    {
        $this->filter = $filter;

        return $this;
    }
}