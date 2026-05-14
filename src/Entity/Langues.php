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

use App\Repository\LanguesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LanguesRepository::class)]
class Langues
{
    public function __toString(): string
    {
        return $this->name;
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $iso = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'langues')]
    private Collection $langue;

    public function __construct()
    {
        $this->langue = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(string $iso): static
    {
        $this->iso = $iso;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getLangue(): Collection
    {
        return $this->langue;
    }

    public function addLangue(User $langue): static
    {
        if (!$this->langue->contains($langue)) {
            $this->langue->add($langue);
            $langue->setLangues($this);
        }

        return $this;
    }

    public function removeLangue(User $langue): static
    {
        if ($this->langue->removeElement($langue)) {
            // set the owning side to null (unless already changed)
            if ($langue->getLangues() === $this) {
                $langue->setLangues(null);
            }
        }

        return $this;
    }
}
