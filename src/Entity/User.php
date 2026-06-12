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

use App\Entity\FormContact\Contact;
use App\Entity\Traits\CreatedAtTraits;
use App\Entity\Traits\DeletedAtTraits;
use App\Entity\Traits\LastLoginAtTraits;
use App\Entity\Traits\UpdatedAtTraits;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`utilisateur`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Index(name: 'IDX_USER_VERIFIED', columns: ['is_verified'])]
#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    /*
    |--------------------------------------------------------------------------
    | Traits de dates
    |--------------------------------------------------------------------------
    */

    use CreatedAtTraits;
    use DeletedAtTraits;
    use LastLoginAtTraits;
    use UpdatedAtTraits;

    /*
    |--------------------------------------------------------------------------
    | Identifiant principal
    |--------------------------------------------------------------------------
    */

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /*
    |--------------------------------------------------------------------------
    | Authentification Symfony
    |--------------------------------------------------------------------------
    */

    #[ORM\Column(length: 250)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    /*
    |--------------------------------------------------------------------------
    | Double authentification par email
    |--------------------------------------------------------------------------
    */

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailAuthCode = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailAuthCodeExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailAuthCodeRequestedAt = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $failedVerificationAttempts = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $emailAuthEnabled = false;

    /*
    |--------------------------------------------------------------------------
    | Informations personnelles
    |--------------------------------------------------------------------------
    */

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    /*
    |--------------------------------------------------------------------------
    | Avatar utilisateur
    |--------------------------------------------------------------------------
    */

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    /*
    |--------------------------------------------------------------------------
    | Adresse utilisateur
    |--------------------------------------------------------------------------
    */

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Pays $pays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseComplement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    /*
    |--------------------------------------------------------------------------
    | Informations professionnelles
    |--------------------------------------------------------------------------
    */

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entreprise = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /*
    |--------------------------------------------------------------------------
    | Informations de contact public
    |--------------------------------------------------------------------------
    */

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
    private ?string $paysContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseComplementContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $whatsApp = null;

    /*
    |--------------------------------------------------------------------------
    | Préférences utilisateur
    |--------------------------------------------------------------------------
    */

    #[ORM\ManyToOne]
    private ?Langues $langues = null;

    #[ORM\ManyToOne]
    private ?Devise $devise = null;

    #[ORM\ManyToOne]
    private ?FuseauHoraire $fuseauHoraire = null;

    /**
     * @var Collection<int, Property>
     */
    #[ORM\OneToMany(targetEntity: Property::class, mappedBy: 'user')]
    private Collection $properties;

    #[Gedmo\Slug(fields: ['entreprise'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Contact>
     */
    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'agence')]
    private Collection $contacts;

    /**
     * @var Collection<int, Favoris>
     */
    #[ORM\OneToMany(targetEntity: Favoris::class, mappedBy: 'user')]
    private Collection $favoris;

    /**
     * @var Collection<int, HoraireOuverture>
     */
    #[ORM\OneToMany(targetEntity: HoraireOuverture::class, mappedBy: 'agence')]
    private Collection $horaireOuvertures;

    /**
     * @var Collection<int, PropertyView>
     */
    #[ORM\OneToMany(targetEntity: PropertyView::class, mappedBy: 'user')]
    private Collection $propertyViews;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->horaireOuvertures = new ArrayCollection();
        $this->propertyViews = new ArrayCollection();
    }

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Identifiant
    |--------------------------------------------------------------------------
    */

    public function getId(): ?int
    {
        return $this->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Authentification Symfony
    |--------------------------------------------------------------------------
    */

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(mb_trim($email));

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
            'password' => null !== $this->password ? hash('crc32c', $this->password) : null,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->roles = $data['roles'] ?? [];
        $this->password = $data['password'] ?? null;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Double authentification email
    |--------------------------------------------------------------------------
    */

    public function isEmailAuthEnabled(): bool
    {
        return $this->emailAuthEnabled;
    }

    public function setEmailAuthEnabled(bool $enabled): static
    {
        $this->emailAuthEnabled = $enabled;

        return $this;
    }

    public function getEmailAuthRecipient(): string
    {
        return (string) $this->email;
    }

    public function getEmailAuthCode(): string
    {
        if (null === $this->emailAuthCode) {
            throw new \LogicException('The email authentication code was not set.');
        }

        return $this->emailAuthCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthCode = $authCode;
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

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Vérification du compte
    |--------------------------------------------------------------------------
    */

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Informations personnelles
    |--------------------------------------------------------------------------
    */

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Avatar utilisateur
    |--------------------------------------------------------------------------
    */

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
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

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Adresse utilisateur
    |--------------------------------------------------------------------------
    */

    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;

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

    public function getAdresseComplement(): ?string
    {
        return $this->adresseComplement;
    }

    public function setAdresseComplement(?string $adresseComplement): static
    {
        $this->adresseComplement = $adresseComplement;

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

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Informations professionnelles
    |--------------------------------------------------------------------------
    */

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(?string $entreprise): static
    {
        $entreprise = null !== $entreprise ? mb_trim($entreprise) : null;

        $this->entreprise = '' !== $entreprise ? $entreprise : null;

        if (null === $this->entreprise) {
            $this->slug = null;
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Informations de contact public
    |--------------------------------------------------------------------------
    */

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
        return $this->paysContact;
    }

    public function setPaysContact(?string $paysContact): static
    {
        $this->paysContact = $paysContact;

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

    /*
    |--------------------------------------------------------------------------
    | Getters / Setters - Préférences utilisateur
    |--------------------------------------------------------------------------
    */

    public function getLangues(): ?Langues
    {
        return $this->langues;
    }

    public function setLangues(?Langues $langues): static
    {
        $this->langues = $langues;

        return $this;
    }

    public function getDevise(): ?Devise
    {
        return $this->devise;
    }

    public function setDevise(?Devise $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    public function getFuseauHoraire(): ?FuseauHoraire
    {
        return $this->fuseauHoraire;
    }

    public function setFuseauHoraire(?FuseauHoraire $fuseauHoraire): static
    {
        $this->fuseauHoraire = $fuseauHoraire;

        return $this;
    }

    /**
     * @return Collection<int, Property>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): static
    {
        if (!$this->properties->contains($property)) {
            $this->properties->add($property);
            $property->setUser($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): static
    {
        if ($this->properties->removeElement($property)) {
            // set the owning side to null (unless already changed)
            if ($property->getUser() === $this) {
                $property->setUser(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): static
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setAgence($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): static
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getAgence() === $this) {
                $contact->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori): static
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setUser($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): static
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getUser() === $this) {
                $favori->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HoraireOuverture>
     */
    public function getHoraireOuvertures(): Collection
    {
        return $this->horaireOuvertures;
    }

    public function addHoraireOuverture(HoraireOuverture $horaireOuverture): static
    {
        if (!$this->horaireOuvertures->contains($horaireOuverture)) {
            $this->horaireOuvertures->add($horaireOuverture);
            $horaireOuverture->setAgence($this);
        }

        return $this;
    }

    public function removeHoraireOuverture(HoraireOuverture $horaireOuverture): static
    {
        if ($this->horaireOuvertures->removeElement($horaireOuverture)) {
            // set the owning side to null (unless already changed)
            if ($horaireOuverture->getAgence() === $this) {
                $horaireOuverture->setAgence(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PropertyView>
     */
    public function getPropertyViews(): Collection
    {
        return $this->propertyViews;
    }

    public function addPropertyView(PropertyView $propertyView): static
    {
        if (!$this->propertyViews->contains($propertyView)) {
            $this->propertyViews->add($propertyView);
            $propertyView->setUser($this);
        }

        return $this;
    }

    public function removePropertyView(PropertyView $propertyView): static
    {
        if ($this->propertyViews->removeElement($propertyView)) {
            // set the owning side to null (unless already changed)
            if ($propertyView->getUser() === $this) {
                $propertyView->setUser(null);
            }
        }

        return $this;
    }
}
