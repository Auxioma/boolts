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

use App\Entity\Traits\UpdatedAtTraits;
use App\Repository\HomeVideoRepository;
use App\Service\Intl\VisitorLocaleResolver;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Vidéo de fond de la home, administrable : 2 emplacements par langue du site.
 * La home en tire une au hasard parmi celles de la langue de la page.
 */
#[ORM\Entity(repositoryClass: HomeVideoRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_HOME_VIDEO_LOCALE_SLOT', columns: ['locale', 'emplacement'])]
#[UniqueEntity(
    fields: ['locale', 'emplacement'],
    message: 'Cet emplacement est déjà utilisé pour cette langue (2 vidéos maximum par langue).',
)]
#[Vich\Uploadable]
class HomeVideo
{
    use UpdatedAtTraits;

    /** Nombre de vidéos par langue. */
    public const SLOTS = [1, 2];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Code langue du site (cf. VisitorLocaleResolver::SUPPORTED). */
    #[ORM\Column(length: 5)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: VisitorLocaleResolver::SUPPORTED)]
    private ?string $locale = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Choice(choices: self::SLOTS)]
    private ?int $emplacement = null;

    #[Vich\UploadableField(mapping: 'home_videos', fileNameProperty: 'videoName')]
    #[Assert\File(
        mimeTypes: ['video/mp4', 'video/webm'],
        mimeTypesMessage: 'La vidéo doit être au format MP4 ou WebM.',
    )]
    private ?File $videoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $videoName = null;

    #[Vich\UploadableField(mapping: 'home_videos', fileNameProperty: 'posterName')]
    #[Assert\Image(mimeTypes: ['image/jpeg', 'image/png', 'image/webp'])]
    private ?File $posterFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getEmplacement(): ?int
    {
        return $this->emplacement;
    }

    public function setEmplacement(?int $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function setVideoFile(?File $videoFile = null): static
    {
        $this->videoFile = $videoFile;

        if (null !== $videoFile) {
            // Modification d'un champ mappé : nécessaire pour déclencher l'upload Vich
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    public function setVideoName(?string $videoName): static
    {
        $this->videoName = $videoName;

        return $this;
    }

    public function setPosterFile(?File $posterFile = null): static
    {
        $this->posterFile = $posterFile;

        if (null !== $posterFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getPosterFile(): ?File
    {
        return $this->posterFile;
    }

    public function getPosterName(): ?string
    {
        return $this->posterName;
    }

    public function setPosterName(?string $posterName): static
    {
        $this->posterName = $posterName;

        return $this;
    }

    /** Une vidéo est obligatoire (à la création, ou déjà envoyée). */
    #[Assert\Callback]
    public function validateVideo(ExecutionContextInterface $context): void
    {
        if (null === $this->videoFile && null === $this->videoName) {
            $context->buildViolation('Ajoutez une vidéo.')
                ->atPath('videoFile')
                ->addViolation();
        }
    }

    public function __toString(): string
    {
        return strtoupper((string) $this->locale).' – vidéo '.$this->emplacement;
    }
}
