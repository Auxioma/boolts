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

use App\Repository\DeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviseRepository::class)]
class Devise
{
    public function __toString(): string
    {
        return $this->nom;
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $signe = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'devise')]
    private Collection $devise;

    /**
     * @var Collection<int, Pays>
     */
    #[ORM\OneToMany(targetEntity: Pays::class, mappedBy: 'devise')]
    private Collection $pays;

    public function __construct()
    {
        $this->devise = new ArrayCollection();
        $this->pays = new ArrayCollection();
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

    public function getSigne(): ?string
    {
        return $this->signe;
    }

    public function setSigne(string $signe): static
    {
        $this->signe = $signe;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getDevise(): Collection
    {
        return $this->devise;
    }

    public function addDevise(User $devise): static
    {
        if (!$this->devise->contains($devise)) {
            $this->devise->add($devise);
            $devise->setDevise($this);
        }

        return $this;
    }

    public function removeDevise(User $devise): static
    {
        if ($this->devise->removeElement($devise)) {
            // set the owning side to null (unless already changed)
            if ($devise->getDevise() === $this) {
                $devise->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Pays>
     */
    public function getPays(): Collection
    {
        return $this->pays;
    }

    public function addPay(Pays $pay): static
    {
        if (!$this->pays->contains($pay)) {
            $this->pays->add($pay);
            $pay->setDevise($this);
        }

        return $this;
    }

    public function removePay(Pays $pay): static
    {
        if ($this->pays->removeElement($pay)) {
            // set the owning side to null (unless already changed)
            if ($pay->getDevise() === $this) {
                $pay->setDevise(null);
            }
        }

        return $this;
    }
}
