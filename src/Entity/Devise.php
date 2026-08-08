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

use App\Repository\DeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviseRepository::class)]
#[ORM\Table(name: 'devise')]
class Devise
{
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
    #[ORM\OneToMany(
        targetEntity: User::class,
        mappedBy: 'devise'
    )]
    private Collection $users;

    /**
     * @var Collection<int, Pays>
     */
    #[ORM\OneToMany(
        targetEntity: Pays::class,
        mappedBy: 'devise'
    )]
    private Collection $pays;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->pays = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
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
        $this->nom = mb_trim($nom);

        return $this;
    }

    public function getSigne(): ?string
    {
        return $this->signe;
    }

    public function setSigne(string $signe): static
    {
        $this->signe = mb_trim($signe);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setDevise($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if (
            $this->users->removeElement($user)
            && $user->getDevise() === $this
        ) {
            $user->setDevise(null);
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

    public function addPay(Pays $pays): static
    {
        if (!$this->pays->contains($pays)) {
            $this->pays->add($pays);
            $pays->setDevise($this);
        }

        return $this;
    }

    public function removePay(Pays $pays): static
    {
        if (
            $this->pays->removeElement($pays)
            && $pays->getDevise() === $this
        ) {
            $pays->setDevise(null);
        }

        return $this;
    }
}
