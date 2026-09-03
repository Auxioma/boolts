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
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class PropertyTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adresse = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ville = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pays = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fullAddress = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $region = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $district = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $locality = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $neighborhood = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $poi = null;

    #[Assert\NotBlank(groups: ['step_7'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titreDuLogement = null;

    #[Assert\NotBlank(groups: ['step_7'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionLogement = null;

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

    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    public function setFullAddress(?string $fullAddress): static
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(?string $district): static
    {
        $this->district = $district;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): static
    {
        $this->locality = $locality;

        return $this;
    }

    public function getNeighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function setNeighborhood(?string $neighborhood): static
    {
        $this->neighborhood = $neighborhood;

        return $this;
    }

    public function getPoi(): ?string
    {
        return $this->poi;
    }

    public function setPoi(?string $poi): static
    {
        $this->poi = $poi;

        return $this;
    }

    public function getTitreDuLogement(): ?string
    {
        return $this->titreDuLogement;
    }

    public function setTitreDuLogement(?string $titreDuLogement): static
    {
        $this->titreDuLogement = $titreDuLogement;

        return $this;
    }

    public function getDescriptionLogement(): ?string
    {
        return $this->descriptionLogement;
    }

    public function setDescriptionLogement(?string $descriptionLogement): static
    {
        $this->descriptionLogement = $descriptionLogement;

        return $this;
    }
}
