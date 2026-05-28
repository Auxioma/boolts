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

use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(groups: ['step_1'])]
    #[ORM\ManyToOne(inversedBy: 'properties')]
    private ?CategoryBien $typeBien = null;

    #[Assert\NotBlank(groups: ['step_2'])]
    #[ORM\ManyToOne(inversedBy: 'properties')]
    private ?CategoryBienTransaction $typeTransaction = null;

    #[Assert\NotBlank(groups: ['step_7'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titreDuLogement = null;

    #[Assert\NotBlank(groups: ['step_7'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionLogement = null;

    #[Assert\NotBlank(groups: ['step_8'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prix = null;

    #[Assert\NotBlank(groups: ['step_4'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $anneeConstruction = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[Assert\NotBlank(groups: ['step_3'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pays = null;

    #[Assert\NotBlank(groups: ['step_8'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceInterne = null;

    #[Assert\NotBlank(groups: ['step_4'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $chambres = null;

    #[Assert\NotBlank(groups: ['step_4'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salleDeBains = null;

    #[Assert\NotBlank(groups: ['step_4'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surfaceTotal = null;

    #[ORM\Column(enumType: StatutAnnonceImmobiliere::class)]
    private StatutAnnonceImmobiliere $statut = StatutAnnonceImmobiliere::BROUILLON;

    /**
     * @var Collection<int, Caracteristique>
     */
    #[ORM\ManyToMany(targetEntity: Caracteristique::class, inversedBy: 'properties')]
    private Collection $caracteristique;

    /**
     * @var Collection<int, PropertyImage>
     */
    #[Assert\Count(min: 1, groups: ['step_6'])]
    #[ORM\OneToMany(
        targetEntity: PropertyImage::class,
        mappedBy: 'property',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $propertyImages;

    #[Assert\NotBlank(groups: ['step_5'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dpe = null;

    #[Assert\NotBlank(groups: ['step_5'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dpeLettre = null;

    #[Assert\NotBlank(groups: ['step_5'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ges = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gesLettre = null;

    #[Assert\NotBlank(groups: ['step_5'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dpeMin = null;

    #[Assert\NotBlank(groups: ['step_5'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dpeMax = null;

    #[Assert\NotBlank(groups: ['step_5'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateIndexationEnergie = null;

    #[Assert\NotBlank(groups: ['step_8'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $montantLoyerHorsCharge = null;

    #[Assert\NotBlank(groups: ['step_8'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $montantDepotDeGarantie = null;

    #[Assert\NotBlank(groups: ['step_8'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $montantDesCharges = null;

    #[ORM\ManyToOne(inversedBy: 'properties')]
    private ?User $user = null;

    public function __construct()
    {
        $this->caracteristique = new ArrayCollection();
        $this->propertyImages = new ArrayCollection();
    }

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

    public function setChambres(?string $chambres): static
    {
        $this->chambres = $chambres;

        return $this;
    }

    public function getSalleDeBains(): ?string
    {
        return $this->salleDeBains;
    }

    public function setSalleDeBains(?string $salleDeBains): static
    {
        $this->salleDeBains = $salleDeBains;

        return $this;
    }

    public function getSurfaceTotal(): ?string
    {
        return $this->surfaceTotal;
    }

    public function setSurfaceTotal(?string $surfaceTotal): static
    {
        $this->surfaceTotal = $surfaceTotal;

        return $this;
    }

    /**
     * @return Collection<int, Caracteristique>
     */
    public function getCaracteristique(): Collection
    {
        return $this->caracteristique;
    }

    public function addCaracteristique(Caracteristique $caracteristique): static
    {
        if (!$this->caracteristique->contains($caracteristique)) {
            $this->caracteristique->add($caracteristique);
        }

        return $this;
    }

    public function removeCaracteristique(Caracteristique $caracteristique): static
    {
        $this->caracteristique->removeElement($caracteristique);

        return $this;
    }

    public function getStatut(): StatutAnnonceImmobiliere
    {
        return $this->statut;
    }

    public function setStatut(StatutAnnonceImmobiliere $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, PropertyImage>
     */
    public function getPropertyImages(): Collection
    {
        return $this->propertyImages;
    }

    public function addPropertyImage(PropertyImage $propertyImage): static
    {
        if (!$this->propertyImages->contains($propertyImage)) {
            $this->propertyImages->add($propertyImage);
            $propertyImage->setProperty($this);
        }

        return $this;
    }

    public function removePropertyImage(PropertyImage $propertyImage): static
    {
        if ($this->propertyImages->removeElement($propertyImage)) {
            if ($propertyImage->getProperty() === $this) {
                $propertyImage->setProperty(null);
            }
        }

        return $this;
    }

    public function getDpe(): ?string
    {
        return $this->dpe;
    }

    public function setDpe(?string $dpe): static
    {
        $this->dpe = $dpe;

        return $this;
    }

    public function getDpeLettre(): ?string
    {
        return $this->dpeLettre;
    }

    public function setDpeLettre(?string $dpeLettre): static
    {
        $this->dpeLettre = $dpeLettre;

        return $this;
    }

    public function getGes(): ?string
    {
        return $this->ges;
    }

    public function setGes(?string $ges): static
    {
        $this->ges = $ges;

        return $this;
    }

    public function getGesLettre(): ?string
    {
        return $this->gesLettre;
    }

    public function setGesLettre(?string $gesLettre): static
    {
        $this->gesLettre = $gesLettre;

        return $this;
    }

    public function getDpeMin(): ?string
    {
        return $this->dpeMin;
    }

    public function setDpeMin(?string $dpeMin): static
    {
        $this->dpeMin = $dpeMin;

        return $this;
    }

    public function getDpeMax(): ?string
    {
        return $this->dpeMax;
    }

    public function setDpeMax(?string $dpeMax): static
    {
        $this->dpeMax = $dpeMax;

        return $this;
    }

    public function getDateIndexationEnergie(): ?\DateTimeImmutable
    {
        return $this->dateIndexationEnergie;
    }

    public function setDateIndexationEnergie(?\DateTimeImmutable $dateIndexationEnergie): static
    {
        $this->dateIndexationEnergie = $dateIndexationEnergie;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
