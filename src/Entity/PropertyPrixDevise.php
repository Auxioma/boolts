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

namespace App\Entity;

use App\Repository\PropertyPrixDeviseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Montants d'un bien convertis dans une devise cible.
 *
 * Les montants saisis par l'agence restent dans Property (devise de référence = Property::devise)
 * et ne sont jamais réécrits : cette table ne contient que des valeurs dérivées, recalculables
 * à partir de la référence et du taux de change fourni par l'API.
 */
#[ORM\Entity(repositoryClass: PropertyPrixDeviseRepository::class)]
#[ORM\Table(name: 'property_prix_devise')]
#[ORM\UniqueConstraint(name: 'uniq_property_prix_devise', columns: ['property_id', 'devise_id'])]
class PropertyPrixDevise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prixDevises')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Property $property = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Devise $devise = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $prix = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $montantLoyerHorsCharge = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $montantDepotDeGarantie = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $montantDesCharges = null;

    /**
     * Taux appliqué : 1 unité de la devise de référence = tauxDeChange unités de la devise cible.
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 24, scale: 10)]
    private ?string $tauxDeChange = null;

    /**
     * Date du taux renvoyée par l'API (sert à savoir quand recalculer).
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $convertedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(?Property $property): static
    {
        $this->property = $property;

        return $this;
    }

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(Devise $devise): static
    {
        $this->devise = $devise;

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

    public function getMontantLoyerHorsCharge(): ?string
    {
        return $this->montantLoyerHorsCharge;
    }

    public function setMontantLoyerHorsCharge(?string $montantLoyerHorsCharge): static
    {
        $this->montantLoyerHorsCharge = $montantLoyerHorsCharge;

        return $this;
    }

    public function getMontantDepotDeGarantie(): ?string
    {
        return $this->montantDepotDeGarantie;
    }

    public function setMontantDepotDeGarantie(?string $montantDepotDeGarantie): static
    {
        $this->montantDepotDeGarantie = $montantDepotDeGarantie;

        return $this;
    }

    public function getMontantDesCharges(): ?string
    {
        return $this->montantDesCharges;
    }

    public function setMontantDesCharges(?string $montantDesCharges): static
    {
        $this->montantDesCharges = $montantDesCharges;

        return $this;
    }

    public function getTauxDeChange(): ?string
    {
        return $this->tauxDeChange;
    }

    public function setTauxDeChange(string $tauxDeChange): static
    {
        $this->tauxDeChange = $tauxDeChange;

        return $this;
    }

    public function getConvertedAt(): ?\DateTimeImmutable
    {
        return $this->convertedAt;
    }

    public function setConvertedAt(\DateTimeImmutable $convertedAt): static
    {
        $this->convertedAt = $convertedAt;

        return $this;
    }
}
