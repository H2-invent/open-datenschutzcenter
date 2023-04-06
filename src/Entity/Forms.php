<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use App\Repository\FormsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: FormsRepository::class)]
class Forms
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updatedAt;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $upload;

    /**
     * @Vich\UploadableField(mapping="forms", fileNameProperty="upload")
     * @var File
     */
    private $uploadFile;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'forms')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $title;

    /**
     * @Assert\NotBlank()
     * @Encrypted()
     */
    #[ORM\Column(type: 'text')]
    private $version;

    #[ORM\Column(type: 'integer')]
    private $activ;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'forms')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * @Encrypted()
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\OneToOne(targetEntity: Forms::class, cascade: ['persist', 'remove'])]
    private $previous;

    #[ORM\ManyToMany(targetEntity: Produkte::class)]
    private $products;

    #[ORM\ManyToMany(targetEntity: AuditTomAbteilung::class)]
    private $departments;

    #[ORM\Column(type: 'integer')]
    private $status;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedForms')]
    private $assignedUser;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $approvedBy;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $approved;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->departments = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUploadFile()
    {
        return $this->uploadFile;
    }

    public function setUploadFile(File $upload = null)
    {
        $this->uploadFile = $upload;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($upload) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getUpload()
    {
        return $this->upload;
    }

    public function setUpload($upload)
    {
        $this->upload = $upload;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getActiv(): ?int
    {
        return $this->activ;
    }

    public function setActiv(int $activ): self
    {
        $this->activ = $activ;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection|Produkte[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Produkte $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Produkte $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    /**
     * @return Collection|AuditTomAbteilung[]
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(AuditTomAbteilung $department): self
    {
        if (!$this->departments->contains($department)) {
            $this->departments[] = $department;
        }

        return $this;
    }

    public function removeDepartment(AuditTomAbteilung $department): self
    {
        if ($this->departments->contains($department)) {
            $this->departments->removeElement($department);
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
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
            case 1:
                return 'In Bearbeitung';
                break;
            case 2:
                return 'Prüfung';
                break;
            case 3:
                return 'Zur Freigabe vorgelegt';
                break;
            case 4:
                return 'Veraltet';
                break;
            default:
                return "Angelegt";
                break;
        }
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

    public function getApprovedBy(): ?User
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(?User $approvedBy): self
    {
        $this->approvedBy = $approvedBy;

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


}
