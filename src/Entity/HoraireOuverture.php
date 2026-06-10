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

use App\Repository\HoraireOuvertureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireOuvertureRepository::class)]
class HoraireOuverture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isOpen = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $jour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ouvertureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fermetureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ouvertureApresMidi = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fermetureApresMidi = null;

    #[ORM\ManyToOne(inversedBy: 'horaireOuvertures')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $agence = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isOpen(): bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): static
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(?string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getOuvertureMatin(): ?\DateTimeInterface
    {
        return $this->ouvertureMatin;
    }

    public function setOuvertureMatin(?\DateTimeInterface $ouvertureMatin): static
    {
        $this->ouvertureMatin = $ouvertureMatin;

        return $this;
    }

    public function getFermetureMatin(): ?\DateTimeInterface
    {
        return $this->fermetureMatin;
    }

    public function setFermetureMatin(?\DateTimeInterface $fermetureMatin): static
    {
        $this->fermetureMatin = $fermetureMatin;

        return $this;
    }

    public function getOuvertureApresMidi(): ?\DateTimeInterface
    {
        return $this->ouvertureApresMidi;
    }

    public function setOuvertureApresMidi(?\DateTimeInterface $ouvertureApresMidi): static
    {
        $this->ouvertureApresMidi = $ouvertureApresMidi;

        return $this;
    }

    public function getFermetureApresMidi(): ?\DateTimeInterface
    {
        return $this->fermetureApresMidi;
    }

    public function setFermetureApresMidi(?\DateTimeInterface $fermetureApresMidi): static
    {
        $this->fermetureApresMidi = $fermetureApresMidi;

        return $this;
    }

    public function getAgence(): ?User
    {
        return $this->agence;
    }

    public function setAgence(?User $agence): static
    {
        $this->agence = $agence;

        return $this;
    }
}
