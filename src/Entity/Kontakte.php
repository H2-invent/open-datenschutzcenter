<?php

namespace App\Entity;

use App\Repository\KontakteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: KontakteRepository::class)]
class Kontakte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private $firma;

    #[ORM\Column(type: 'text', nullable: true)]
    private $anrede;

    #[ORM\Column(type: 'text', nullable: true)]
    private $vorname;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank]
    private $nachname;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $strase;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $plz;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private $ort;

    #[ORM\Column(type: 'text', nullable: true)]
    private $email;

    #[ORM\Column(type: 'text', nullable: true)]
    private $telefon;

    #[ORM\Column(type: 'text', nullable: true)]
    private $bemerkung;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    private $art;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'kontakte')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private $team;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    private $activ;

    #[ORM\Column(type: 'text', nullable: true)]
    private $nummer;

    #[ORM\OneToMany(targetEntity: Datenweitergabe::class, mappedBy: 'kontakt')]
    private $datenweitergaben;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $approved;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $approvedBy;

    public function __construct()
    {
        $this->datenweitergaben = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirma(): ?string
    {
        return $this->firma;
    }

    public function setFirma(?string $firma): self
    {
        $this->firma = $firma;

        return $this;
    }

    public function getAnrede(): ?string
    {
        return $this->anrede;
    }

    public function setAnrede(?string $anrede): self
    {
        $this->anrede = $anrede;

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(?string $vorname): self
    {
        $this->vorname = $vorname;

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(?string $nachname): self
    {
        $this->nachname = $nachname;

        return $this;
    }

    public function getStrase(): ?string
    {
        return $this->strase;
    }

    public function setStrase(string $strase): self
    {
        $this->strase = $strase;

        return $this;
    }

    public function getPlz(): ?string
    {
        return $this->plz;
    }

    public function setPlz(string $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): self
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function getBemerkung(): ?string
    {
        return $this->bemerkung;
    }

    public function setBemerkung(?string $bemerkung): self
    {
        $this->bemerkung = $bemerkung;

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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

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
    public function getDatenweitergaben(): Collection
    {
        return $this->datenweitergaben;
    }

    public function addDatenweitergaben(Datenweitergabe $datenweitergaben): self
    {
        if (!$this->datenweitergaben->contains($datenweitergaben)) {
            $this->datenweitergaben[] = $datenweitergaben;
            $datenweitergaben->setKontakt($this);
        }

        return $this;
    }

    public function removeDatenweitergaben(Datenweitergabe $datenweitergaben): self
    {
        if ($this->datenweitergaben->contains($datenweitergaben)) {
            $this->datenweitergaben->removeElement($datenweitergaben);
            // set the owning side to null (unless already changed)
            if ($datenweitergaben->getKontakt() === $this) {
                $datenweitergaben->setKontakt(null);
            }
        }

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

    public function getName(): string
    {
        return $this->firma ?? $this->getVorname() . $this->getNachname();
    }
}
