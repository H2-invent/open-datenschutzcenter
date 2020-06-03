<?php

namespace App\Entity;

use App\Repository\VorfallRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

/**
 * @ORM\Entity(repositoryClass=VorfallRepository::class)
 */
class Vorfall
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Encrypted()
     */
    private $fakten;

    /**
     * @ORM\Column(type="text")
     * @Encrypted()
     */
    private $auswirkung;

    /**
     * @ORM\Column(type="text")
     * @Encrypted()
     */
    private $massnahmen;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datum;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gemeldet;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="vorfalls")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="vorfalls")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity=Vorfall::class, cascade={"persist", "remove"})
     */
    private $previous;

    /**
     * @ORM\Column(type="text")
     * @Encrypted()
     */
    private $nummer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $betroffeneGemeldet;

    /**
     * @ORM\Column(type="boolean")
     */
    private $auftraggeberGemeldet;

    /**
     * @ORM\ManyToMany(targetEntity=VVTPersonen::class)
     */
    private $personen;

    /**
     * @ORM\ManyToMany(targetEntity=VVTDatenkategorie::class)
     */
    private $daten;

    public function __construct()
    {
        $this->personen = new ArrayCollection();
        $this->daten = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFakten(): ?string
    {
        return $this->fakten;
    }

    public function setFakten(string $fakten): self
    {
        $this->fakten = $fakten;

        return $this;
    }

    public function getAuswirkung(): ?string
    {
        return $this->auswirkung;
    }

    public function setAuswirkung(string $auswirkung): self
    {
        $this->auswirkung = $auswirkung;

        return $this;
    }

    public function getMassnahmen(): ?string
    {
        return $this->massnahmen;
    }

    public function setMassnahmen(string $massnahmen): self
    {
        $this->massnahmen = $massnahmen;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getGemeldet(): ?bool
    {
        return $this->gemeldet;
    }

    public function setGemeldet(bool $gemeldet): self
    {
        $this->gemeldet = $gemeldet;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getBetroffeneGemeldet(): ?bool
    {
        return $this->betroffeneGemeldet;
    }

    public function setBetroffeneGemeldet(bool $betroffeneGemeldet): self
    {
        $this->betroffeneGemeldet = $betroffeneGemeldet;

        return $this;
    }

    public function getAuftraggeberGemeldet(): ?bool
    {
        return $this->auftraggeberGemeldet;
    }

    public function setAuftraggeberGemeldet(bool $auftraggeberGemeldet): self
    {
        $this->auftraggeberGemeldet = $auftraggeberGemeldet;

        return $this;
    }

    /**
     * @return Collection|VVTPersonen[]
     */
    public function getPersonen(): Collection
    {
        return $this->personen;
    }

    public function addPersonen(VVTPersonen $personen): self
    {
        if (!$this->personen->contains($personen)) {
            $this->personen[] = $personen;
        }

        return $this;
    }

    public function removePersonen(VVTPersonen $personen): self
    {
        if ($this->personen->contains($personen)) {
            $this->personen->removeElement($personen);
        }

        return $this;
    }

    /**
     * @return Collection|VVTDatenkategorie[]
     */
    public function getDaten(): Collection
    {
        return $this->daten;
    }

    public function addDaten(VVTDatenkategorie $daten): self
    {
        if (!$this->daten->contains($daten)) {
            $this->daten[] = $daten;
        }

        return $this;
    }

    public function removeDaten(VVTDatenkategorie $daten): self
    {
        if ($this->daten->contains($daten)) {
            $this->daten->removeElement($daten);
        }

        return $this;
    }
}
