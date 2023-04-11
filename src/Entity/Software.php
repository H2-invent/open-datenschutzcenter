<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\SoftwareRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SoftwareRepository::class)]
class Software
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $name;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $build;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank]
    private $purchase;

    #[ORM\ManyToMany(targetEntity: VVT::class, inversedBy: 'software')]
    private $vvts;

    #[ORM\Column(type: 'boolean')]
    private $activ;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $license;

    #[ORM\Column(type: 'date', nullable: true)]
    private $licenseExpiration;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $description;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $status;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'software')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'software')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\OneToOne(targetEntity: Software::class, cascade: ['persist', 'remove'])]
    private $previous;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedSoftware')]
    private $assignedUser;

    #[ORM\OneToMany(targetEntity: SoftwareConfig::class, mappedBy: 'software')]
    private $config;

    #[ORM\Column(type: 'text', nullable: true)]
    private $location;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $nummer;

    #[ORM\ManyToMany(targetEntity: Datenweitergabe::class, inversedBy: 'software')]
    private $datenweitergabe;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $reference;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $approved;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $approvedBy;

    #[ORM\Column(type: 'text', nullable: true)]
    private $archiving;

    #[ORM\Column(type: 'text', nullable: true)]
    private $recovery;

    #[ORM\Column(type: 'text', nullable: true)]
    private $permissions;

    #[ORM\Column(type: 'integer')]
    private $licenseType;

    public function __construct()
    {
        $this->vvts = new ArrayCollection();
        $this->config = new ArrayCollection();
        $this->datenweitergabe = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBuild(): ?string
    {
        return $this->build;
    }

    public function setBuild(string $build): self
    {
        $this->build = $build;

        return $this;
    }

    public function getPurchase(): ?\DateTimeInterface
    {
        return $this->purchase;
    }

    public function setPurchase(\DateTimeInterface $purchase): self
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * @return Collection|VVT[]
     */
    public function getVvts(): Collection
    {
        return $this->vvts;
    }

    public function addVvt(VVT $vvt): self
    {
        if (!$this->vvts->contains($vvt)) {
            $this->vvts[] = $vvt;
        }

        return $this;
    }

    public function removeVvt(VVT $vvt): self
    {
        if ($this->vvts->contains($vvt)) {
            $this->vvts->removeElement($vvt);
        }

        return $this;
    }

    public function getActiv(): ?bool
    {
        return $this->activ;
    }

    public function setActiv(bool $activ): self
    {
        $this->activ = $activ;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getLicenseExpiration(): ?\DateTimeInterface
    {
        return $this->licenseExpiration;
    }

    public function setLicenseExpiration(?\DateTimeInterface $licenseExpiration): self
    {
        $this->licenseExpiration = $licenseExpiration;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusString()
    {
        switch ($this->status) {
            case 0:
                return 'Angelegt';
                break;
            case 10:
                return 'In Bearbeitung';
                break;
            case 20:
                return 'In Prüfung';
                break;
            case 30:
                return 'Zur Freigegebe vorgelegt';
                break;
            case 40:
                return 'Freigegeben'; //Nicht mehr in Verwendung
                break;
            case 50:
                return 'Lizenz abgelaufen'; //Nicht mehr in Verwendung
                break;
            case 60:
                return 'Inaktiv';
                break;
            default:
                return "Angelegt";
                break;
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPrevious(): ?self
    {
        return $this->previous;
    }

    public function setPrevious(?self $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    public function getAssignedUser(): ?User
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?User $assignedUser): self
    {
        $this->assignedUser = $assignedUser;

        return $this;
    }

    /**
     * @return Collection|SoftwareConfig[]
     */
    public function getConfig(): Collection
    {
        return $this->config;
    }

    public function addConfig(SoftwareConfig $config): self
    {
        if (!$this->config->contains($config)) {
            $this->config[] = $config;
            $config->setSoftware($this);
        }

        return $this;
    }

    public function removeConfig(SoftwareConfig $config): self
    {
        if ($this->config->contains($config)) {
            $this->config->removeElement($config);
            // set the owning side to null (unless already changed)
            if ($config->getSoftware() === $this) {
                $config->setSoftware(null);
            }
        }

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getNummer(): ?string
    {
        return $this->nummer;
    }

    public function setNummer(?string $nummer): self
    {
        $this->nummer = $nummer;

        return $this;
    }

    /**
     * @return Collection|Datenweitergabe[]
     */
    public function getDatenweitergabe(): Collection
    {
        return $this->datenweitergabe;
    }

    public function addDatenweitergabe(Datenweitergabe $datenweitergabe): self
    {
        if (!$this->datenweitergabe->contains($datenweitergabe)) {
            $this->datenweitergabe[] = $datenweitergabe;
        }

        return $this;
    }

    public function removeDatenweitergabe(Datenweitergabe $datenweitergabe): self
    {
        if ($this->datenweitergabe->contains($datenweitergabe)) {
            $this->datenweitergabe->removeElement($datenweitergabe);
        }

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(?bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): self
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }

    public function getArchiving(): ?string
    {
        return $this->archiving;
    }

    public function setArchiving(?string $archiving): self
    {
        $this->archiving = $archiving;

        return $this;
    }

    public function getRecovery(): ?string
    {
        return $this->recovery;
    }

    public function setRecovery(?string $recovery): self
    {
        $this->recovery = $recovery;

        return $this;
    }

    public function getPermissions(): ?string
    {
        return $this->permissions;
    }

    public function setPermissions(?string $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getLicenseTypeString()
    {
        switch ($this->licenseType) {
            case 0:
                return 'Keine Angabe';
                break;
            case 10:
                return 'Free and Open Source (FOSS)';
                break;
            case 20:
                return 'Kostenlos/Closed Source';
                break;
            case 30:
                return 'Benutzerlizenzen';
                break;
            case 40:
                return 'Gerätelizenzen';
                break;
            case 50:
                return 'Serverlizenzen';
                break;
            case 60:
                return 'Managed Service';
                break;
            case 70:
                return 'Misch-Lizenzen';
                break;
            case 90:
                return 'Andere Lizenz';
                break;
            default:
                return "Keine Angabe";
                break;
        }
    }

    public function getLicenseType(): ?int
    {
        return $this->licenseType;
    }

    public function setLicenseType(int $licenseType): self
    {
        $this->licenseType = $licenseType;

        return $this;
    }
}
