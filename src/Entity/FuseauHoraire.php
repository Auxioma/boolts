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

use App\Repository\FuseauHoraireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FuseauHoraireRepository::class)]
class FuseauHoraire
{
    public function __toString() {
        return $this->nom;
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'fuseauHoraire')]
    private Collection $FuseauHoraire;

    public function __construct()
    {
        $this->FuseauHoraire = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFuseauHoraire(): Collection
    {
        return $this->FuseauHoraire;
    }

    public function addFuseauHoraire(User $fuseauHoraire): static
    {
        if (!$this->FuseauHoraire->contains($fuseauHoraire)) {
            $this->FuseauHoraire->add($fuseauHoraire);
            $fuseauHoraire->setFuseauHoraire($this);
        }

        return $this;
    }

    public function removeFuseauHoraire(User $fuseauHoraire): static
    {
        if ($this->FuseauHoraire->removeElement($fuseauHoraire)) {
            // set the owning side to null (unless already changed)
            if ($fuseauHoraire->getFuseauHoraire() === $this) {
                $fuseauHoraire->setFuseauHoraire(null);
            }
        }

        return $this;
    }
}
