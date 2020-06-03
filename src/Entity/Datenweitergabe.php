<?php

namespace App\Entity;

use App\Repository\DatenweitergabeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

/**
 * @ORM\Entity(repositoryClass=DatenweitergabeRepository::class)
 * @Vich\Uploadable
 */
class Datenweitergabe
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $gegenstand;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $verantwortlich;

    /**
     * @ORM\ManyToOne(targetEntity=Kontakte::class, inversedBy="datenweitergaben")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $kontakt;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $vertragsform;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\Date()
     * @Assert\NotBlank()
     */
    private $zeichnungsdatum;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="datenweitergaben")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\OneToOne(targetEntity=Datenweitergabe::class, cascade={"persist", "remove"})
     */
    private $previous;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Encrypted()
     */
    private $nummer;

    /**
     * @ORM\ManyToOne(targetEntity=DatenweitergabeStand::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $stand;

    /**
     * @ORM\ManyToOne(targetEntity=DatenweitergabeGrundlagen::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $grundlage;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $art;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="datenweitergabes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="profil_picture", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\ManyToMany(targetEntity=VVT::class, mappedBy="datenweitergaben")
     */
    private $verfahren;

    public function __construct()
    {
        $this->verfahren = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGegenstand(): ?string
    {
        return $this->gegenstand;
    }

    public function setGegenstand(string $gegenstand): self
    {
        $this->gegenstand = $gegenstand;

        return $this;
    }

    public function getVerantwortlich(): ?string
    {
        return $this->verantwortlich;
    }

    public function setVerantwortlich(string $verantwortlich): self
    {
        $this->verantwortlich = $verantwortlich;

        return $this;
    }

    public function getKontakt(): ?Kontakte
    {
        return $this->kontakt;
    }

    public function setKontakt(?Kontakte $kontakt): self
    {
        $this->kontakt = $kontakt;

        return $this;
    }

    public function getVertragsform(): ?string
    {
        return $this->vertragsform;
    }

    public function setVertragsform(string $vertragsform): self
    {
        $this->vertragsform = $vertragsform;

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

    public function getZeichnungsdatum(): ?\DateTimeInterface
    {
        return $this->zeichnungsdatum;
    }

    public function setZeichnungsdatum(?\DateTimeInterface $zeichnungsdatum): self
    {
        $this->zeichnungsdatum = $zeichnungsdatum;

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

    public function getActiv(): ?bool
    {
        return $this->activ;
    }

    public function setActiv(bool $activ): self
    {
        $this->activ = $activ;

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

    public function getNummer(): ?string
    {
        return $this->nummer;
    }

    public function setNummer(string $nummer): self
    {
        $this->nummer = $nummer;

        return $this;
    }

    public function getStand(): ?DatenweitergabeStand
    {
        return $this->stand;
    }

    public function setStand(?DatenweitergabeStand $stand): self
    {
        $this->stand = $stand;

        return $this;
    }

    public function getGrundlage(): ?DatenweitergabeGrundlagen
    {
        return $this->grundlage;
    }

    public function setGrundlage(?DatenweitergabeGrundlagen $grundlage): self
    {
        $this->grundlage = $grundlage;

        return $this;
    }

    public function getArt(): ?int
    {
        return $this->art;
    }

    public function setArt(int $art): self
    {
        $this->art = $art;

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

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
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

    /**
     * @return Collection|VVT[]
     */
    public function getVerfahren(): Collection
    {
        return $this->verfahren;
    }

    public function addVerfahren(VVT $verfahren): self
    {
        if (!$this->verfahren->contains($verfahren)) {
            $this->verfahren[] = $verfahren;
            $verfahren->addDatenweitergaben($this);
        }

        return $this;
    }

    public function removeVerfahren(VVT $verfahren): self
    {
        if ($this->verfahren->contains($verfahren)) {
            $this->verfahren->removeElement($verfahren);
            $verfahren->removeDatenweitergaben($this);
        }

        return $this;
    }
}
