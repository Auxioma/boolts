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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

#[ORM\Entity]
class UserTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseComplement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $villeContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paysContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseComplementContact = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdresseComplement(): ?string
    {
        return $this->adresseComplement;
    }

    public function setAdresseComplement(?string $adresseComplement): static
    {
        $this->adresseComplement = $adresseComplement;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAdresseContact(): ?string
    {
        return $this->adresseContact;
    }

    public function setAdresseContact(?string $adresseContact): static
    {
        $this->adresseContact = $adresseContact;

        return $this;
    }

    public function getVilleContact(): ?string
    {
        return $this->villeContact;
    }

    public function setVilleContact(?string $villeContact): static
    {
        $this->villeContact = $villeContact;

        return $this;
    }

    public function getPaysContact(): ?string
    {
        return $this->paysContact;
    }

    public function setPaysContact(?string $paysContact): static
    {
        $this->paysContact = $paysContact;

        return $this;
    }

    public function getAdresseComplementContact(): ?string
    {
        return $this->adresseComplementContact;
    }

    public function setAdresseComplementContact(?string $adresseComplementContact): static
    {
        $this->adresseComplementContact = $adresseComplementContact;

        return $this;
    }
}
