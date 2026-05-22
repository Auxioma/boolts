<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Entity;

use App\Entity\CategoryBien;
use App\Entity\Enum\PerformanceEnergetique;
use App\Repository\PropertyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'properties')]
    private ?CategoryBien $typeBien = null;

    #[ORM\ManyToOne(inversedBy: 'properties')]
    private ?CategoryBienTransaction $typeTransaction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titreDuLogement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionLogement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $anneeConstruction = null;

    #[ORM\Column(enumType: PerformanceEnergetique::class, nullable: true)]
    private ?PerformanceEnergetique $performanceEnergetique = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceInterne = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chambres = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salleDeBains = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surfaceTotal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeBien(): ?CategoryBien
    {
        return $this->typeBien;
    }

    public function setTypeBien(?CategoryBien $typeBien): static
    {
        $this->typeBien = $typeBien;

        return $this;
    }

    public function getTypeTransaction(): ?CategoryBienTransaction
    {
        return $this->typeTransaction;
    }

    public function setTypeTransaction(?CategoryBienTransaction $typeTransaction): static
    {
        $this->typeTransaction = $typeTransaction;

        return $this;
    }

    public function getTitreDuLogement(): ?string
    {
        return $this->titreDuLogement;
    }

    public function setTitreDuLogement(string $titreDuLogement): static
    {
        $this->titreDuLogement = $titreDuLogement;

        return $this;
    }

    public function getDescriptionLogement(): ?string
    {
        return $this->descriptionLogement;
    }

    public function setDescriptionLogement(string $descriptionLogement): static
    {
        $this->descriptionLogement = $descriptionLogement;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(?string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getAnneeConstruction(): ?string
    {
        return $this->anneeConstruction;
    }

    public function setAnneeConstruction(?string $anneeConstruction): static
    {
        $this->anneeConstruction = $anneeConstruction;

        return $this;
    }

    public function getPerformanceEnergetique(): ?PerformanceEnergetique
    {
        return $this->performanceEnergetique;
    }

    public function setPerformanceEnergetique(?PerformanceEnergetique $performanceEnergetique): static
    {
        $this->performanceEnergetique = $performanceEnergetique;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(?string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getReferenceInterne(): ?string
    {
        return $this->referenceInterne;
    }

    public function setReferenceInterne(?string $referenceInterne): static
    {
        $this->referenceInterne = $referenceInterne;

        return $this;
    }

    public function getChambres(): ?string
    {
        return $this->chambres;
    }

    public function setChambres(string $chambres): static
    {
        $this->chambres = $chambres;

        return $this;
    }

    public function getSalleDeBains(): ?string
    {
        return $this->salleDeBains;
    }

    public function setSalleDeBains(string $salleDeBains): static
    {
        $this->salleDeBains = $salleDeBains;

        return $this;
    }

    public function getSurfaceTotal(): ?string
    {
        return $this->surfaceTotal;
    }

    public function setSurfaceTotal(string $surfaceTotal): static
    {
        $this->surfaceTotal = $surfaceTotal;

        return $this;
    }
}
