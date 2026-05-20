<?php

namespace App\Entity;

use App\Repository\FuseauHoraireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FuseauHoraireRepository::class)]
class FuseauHoraire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $utc = null;

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

    public function getUtcOffset(): int
    {
        if (
            preg_match('/UTC([+-]\d{2}):(\d{2})/', $this->nom, $matches)
        ) {

            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            return ($hours * 60) + ($hours >= 0 ? $minutes : -$minutes);
        }

        return 0;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    public function getUtc(): ?string
    {
        return $this->utc;
    }

    public function setUtc(string $utc): static
    {
        $this->utc = $utc;

        return $this;
    }
}
