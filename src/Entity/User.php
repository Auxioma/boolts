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

use App\Entity\Traits\CreatedAtTraits;
use App\Entity\Traits\DeletedAtTraits;
use App\Entity\Traits\LastLoginAtTraits;
use App\Entity\Traits\UpdatedAtTraits;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Index(name: 'IDX_USER_VERIFIED', columns: ['is_verified'])]
#[ORM\Table(name: '`utilisateur`')]
#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use CreatedAtTraits;
    use DeletedAtTraits;
    use LastLoginAtTraits;
    use UpdatedAtTraits;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private ?string $email;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailAuthCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailAuthCodeExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailAuthCodeRequestedAt = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $failedVerificationAttempts = 0;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Pays $pays = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $AdresseComplement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Pays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entreprise = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostalContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $villeContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $PaysContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseComplementContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $whatsApp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(mb_trim($email));

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
public function __serialize(): array
{
    return [
        'id' => $this->id,
        'email' => $this->email,
        'roles' => $this->roles,
        'password' => hash('crc32c', $this->password),
    ];
}

public function __unserialize(array $data): void
{
    $this->id = $data['id'] ?? null;
    $this->email = $data['email'] ?? null;
    $this->roles = $data['roles'] ?? [];
    $this->password = $data['password'] ?? null;
}

    public function getEmailAuthCode(): ?string
    {
        return $this->emailAuthCode;
    }

    public function setEmailAuthCode(?string $emailAuthCode): static
    {
        $this->emailAuthCode = $emailAuthCode;

        return $this;
    }

    public function getEmailAuthCodeExpiresAt(): ?\DateTimeImmutable
    {
        return $this->emailAuthCodeExpiresAt;
    }

    public function setEmailAuthCodeExpiresAt(?\DateTimeImmutable $emailAuthCodeExpiresAt): static
    {
        $this->emailAuthCodeExpiresAt = $emailAuthCodeExpiresAt;

        return $this;
    }

    public function getEmailAuthCodeRequestedAt(): ?\DateTimeImmutable
    {
        return $this->emailAuthCodeRequestedAt;
    }

    public function setEmailAuthCodeRequestedAt(?\DateTimeImmutable $emailAuthCodeRequestedAt): static
    {
        $this->emailAuthCodeRequestedAt = $emailAuthCodeRequestedAt;

        return $this;
    }

    public function getFailedVerificationAttempts(): int
    {
        return $this->failedVerificationAttempts;
    }

    public function setFailedVerificationAttempts(int $attempts): static
    {
        $this->failedVerificationAttempts = $attempts;

        return $this;
    }

    public function incrementFailedVerificationAttempts(): static
    {
        ++$this->failedVerificationAttempts;

        return $this;
    }

    public function clearEmailAuthCode(): static
    {
        $this->emailAuthCode = null;
        $this->emailAuthCodeExpiresAt = null;
        $this->emailAuthCodeRequestedAt = null;
        $this->failedVerificationAttempts = 0;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getAdresseComplement(): ?string
    {
        return $this->AdresseComplement;
    }

    public function setAdresseComplement(?string $AdresseComplement): static
    {
        $this->AdresseComplement = $AdresseComplement;

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
        return $this->Ville;
    }

    public function setVille(?string $Ville): static
    {
        $this->Ville = $Ville;

        return $this;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(string $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->emailContact;
    }

    public function setEmailContact(?string $emailContact): static
    {
        $this->emailContact = $emailContact;

        return $this;
    }

    public function getNumeroContact(): ?string
    {
        return $this->numeroContact;
    }

    public function setNumeroContact(?string $numeroContact): static
    {
        $this->numeroContact = $numeroContact;

        return $this;
    }

    public function getAdresseContact(): ?string
    {
        return $this->adresseContact;
    }

    public function setAdresseContact(?string $adresseContact): static
    {
        $this->adresseContact = $adresseContact;

        return $this;
    }

    public function getCodePostalContact(): ?string
    {
        return $this->codePostalContact;
    }

    public function setCodePostalContact(?string $codePostalContact): static
    {
        $this->codePostalContact = $codePostalContact;

        return $this;
    }

    public function getVilleContact(): ?string
    {
        return $this->villeContact;
    }

    public function setVilleContact(?string $villeContact): static
    {
        $this->villeContact = $villeContact;

        return $this;
    }

    public function getPaysContact(): ?string
    {
        return $this->PaysContact;
    }

    public function setPaysContact(?string $PaysContact): static
    {
        $this->PaysContact = $PaysContact;

        return $this;
    }

    public function getAdresseComplementContact(): ?string
    {
        return $this->adresseComplementContact;
    }

    public function setAdresseComplementContact(?string $adresseComplementContact): static
    {
        $this->adresseComplementContact = $adresseComplementContact;

        return $this;
    }

    public function getWhatsApp(): ?string
    {
        return $this->whatsApp;
    }

    public function setWhatsApp(?string $whatsApp): static
    {
        $this->whatsApp = $whatsApp;

        return $this;
    }
}
