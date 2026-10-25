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

namespace App\Entity\Booster;

use App\Entity\Shared\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[ORM\Entity]
#[ORM\Table(name: 'booster_pack')]
class BoosterPack implements TranslatableInterface
{
    use TimestampableTrait;
    use TranslatableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $code;

    #[ORM\Column]
    private int $boostQuantity = 1;

    #[ORM\Column(options: ['default' => 7])]
    private int $boostDurationDays = 7;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    public function __construct()
    {
        $this->initializeTimestamps();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getName() ?: $this->code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    // Nom et description sont traduits (BoosterPackTranslation) :
    // ces accesseurs lisent/écrivent la traduction de la locale courante.

    public function getName(): string
    {
        return $this->translate()->getName() ?? '';
    }

    public function setName(string $name): static
    {
        $this->translate()->setName($name);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->translate()->getDescription();
    }

    public function setDescription(?string $description): static
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    // Accès explicite au français et à l'anglais (formulaire d'administration).

    public function getNameFr(): ?string
    {
        return $this->translate('fr', false)->getName();
    }

    public function setNameFr(?string $name): static
    {
        $this->translate('fr', false)->setName($name);

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->translate('en', false)->getName();
    }

    public function setNameEn(?string $name): static
    {
        $this->translate('en', false)->setName($name);

        return $this;
    }

    public function getDescriptionFr(): ?string
    {
        return $this->translate('fr', false)->getDescription();
    }

    public function setDescriptionFr(?string $description): static
    {
        $this->translate('fr', false)->setDescription($description);

        return $this;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->translate('en', false)->getDescription();
    }

    public function setDescriptionEn(?string $description): static
    {
        $this->translate('en', false)->setDescription($description);

        return $this;
    }

    public function getBoostQuantity(): int
    {
        return $this->boostQuantity;
    }

    public function setBoostQuantity(int $boostQuantity): static
    {
        $this->boostQuantity = $boostQuantity;

        return $this;
    }

    public function getBoostDurationDays(): int
    {
        return $this->boostDurationDays;
    }

    public function setBoostDurationDays(int $boostDurationDays): static
    {
        $this->boostDurationDays = $boostDurationDays;

        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
