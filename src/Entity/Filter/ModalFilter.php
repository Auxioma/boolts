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

namespace App\Entity\Filter;

use App\Entity\CategoryBienTransaction;

class ModalFilter
{
    private ?CategoryBienTransaction $natureDeLaPropriete = null;

    /**
     * @var array<int, string>
     */
    private array $typeDePropriete = [];

    /**
     * Localisation de la propriété.
     *
     * @var array<int, string>
     */
    private array $pays = [];

    /**
     * @var array<int, string>
     */
    private array $ville = [];

    /**
     * @var array<int, string>
     */
    private array $quartier = [];

    private ?int $minChambres = null;
    private ?int $maxChambres = null;

    private ?int $minSallesDeBain = null;
    private ?int $maxSallesDeBain = null;

    private ?int $minSurface = null;
    private ?int $maxSurface = null;

    private ?int $minAnneeConstruction = null;
    private ?int $maxAnneeConstruction = null;

    private ?int $minPrix = null;
    private ?int $maxPrix = null;

    /**
     * @var array<int, string>
     */
    private array $dpe = [];

    public function getNatureDeLaPropriete(): ?CategoryBienTransaction
    {
        return $this->natureDeLaPropriete;
    }

    public function setNatureDeLaPropriete(?CategoryBienTransaction $natureDeLaPropriete): self
    {
        $this->natureDeLaPropriete = $natureDeLaPropriete;

        return $this;
    }

    public function getTypeDePropriete(): array
    {
        return $this->typeDePropriete;
    }

    public function setTypeDePropriete(?array $typeDePropriete): self
    {
        $this->typeDePropriete = $typeDePropriete ?? [];

        return $this;
    }

    public function getPays(): array
    {
        return $this->pays;
    }

    public function setPays(?array $pays): self
    {
        $this->pays = $pays ?? [];

        return $this;
    }

    public function getVille(): array
    {
        return $this->ville;
    }

    public function setVille(?array $ville): self
    {
        $this->ville = $ville ?? [];

        return $this;
    }

    public function getQuartier(): array
    {
        return $this->quartier;
    }

    public function setQuartier(?array $quartier): self
    {
        $this->quartier = $quartier ?? [];

        return $this;
    }

    public function getMinChambres(): ?int
    {
        return $this->minChambres;
    }

    public function setMinChambres(?int $minChambres): self
    {
        $this->minChambres = $minChambres;

        return $this;
    }

    public function getMaxChambres(): ?int
    {
        return $this->maxChambres;
    }

    public function setMaxChambres(?int $maxChambres): self
    {
        $this->maxChambres = $maxChambres;

        return $this;
    }

    public function getMinSallesDeBain(): ?int
    {
        return $this->minSallesDeBain;
    }

    public function setMinSallesDeBain(?int $minSallesDeBain): self
    {
        $this->minSallesDeBain = $minSallesDeBain;

        return $this;
    }

    public function getMaxSallesDeBain(): ?int
    {
        return $this->maxSallesDeBain;
    }

    public function setMaxSallesDeBain(?int $maxSallesDeBain): self
    {
        $this->maxSallesDeBain = $maxSallesDeBain;

        return $this;
    }

    public function getMinSurface(): ?int
    {
        return $this->minSurface;
    }

    public function setMinSurface(?int $minSurface): self
    {
        $this->minSurface = $minSurface;

        return $this;
    }

    public function getMaxSurface(): ?int
    {
        return $this->maxSurface;
    }

    public function setMaxSurface(?int $maxSurface): self
    {
        $this->maxSurface = $maxSurface;

        return $this;
    }

    public function getMinAnneeConstruction(): ?int
    {
        return $this->minAnneeConstruction;
    }

    public function setMinAnneeConstruction(?int $minAnneeConstruction): self
    {
        $this->minAnneeConstruction = $minAnneeConstruction;

        return $this;
    }

    public function getMaxAnneeConstruction(): ?int
    {
        return $this->maxAnneeConstruction;
    }

    public function setMaxAnneeConstruction(?int $maxAnneeConstruction): self
    {
        $this->maxAnneeConstruction = $maxAnneeConstruction;

        return $this;
    }

    public function getMinPrix(): ?int
    {
        return $this->minPrix;
    }

    public function setMinPrix(?int $minPrix): self
    {
        $this->minPrix = $minPrix;

        return $this;
    }

    public function getMaxPrix(): ?int
    {
        return $this->maxPrix;
    }

    public function setMaxPrix(?int $maxPrix): self
    {
        $this->maxPrix = $maxPrix;

        return $this;
    }

    public function getDpe(): array
    {
        return $this->dpe;
    }

    public function setDpe(?array $dpe): self
    {
        $this->dpe = $dpe ?? [];

        return $this;
    }
}
