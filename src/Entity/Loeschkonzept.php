<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Entity;

use App\Repository\LoeschkonzeptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoeschkonzeptRepository::class)]
class Loeschkonzept
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text', length: 255)]
    #[Assert\NotBlank]
    private $standartlf;

    #[ORM\Column(type: 'text', length: 255, nullable: true)]
    private $loeschfrist;

    #[ORM\Column(type: 'text', length: 511)]
    #[Assert\NotBlank]
    private $speicherorte;

    #[ORM\Column(type: 'text', length: 255)]
    #[Assert\NotBlank]
    private $loeschbeauftragter;

    #[ORM\Column(type: 'text', length: 1023, nullable: true)]
    private $beschreibung;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'loeschkonzepts')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    #[ORM\OneToOne(targetEntity: Loeschkonzept::class, cascade: ['persist', 'remove'])]
    private $previous;

    #[ORM\Column(type: 'datetime_immutable')]
    private $CreateAt;

    #[ORM\Column(type: 'boolean')]
    private $activ;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'loeschkonzepts')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToMany(targetEntity: VVTDatenkategorie::class, inversedBy: 'loeschkonzept')]
    private $vvtdatenkategories;

    #[ORM\ManyToOne(targetEntity: Loeschkonzept::class, inversedBy: 'parentOf')]
    private $cloneOf;

    #[ORM\OneToMany(targetEntity: Loeschkonzept::class, mappedBy: 'cloneOf')]
    private $parentOf;

    public function __construct()
    {
        $this->vvtdatenkategories = new ArrayCollection();
        $this->parentOf = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->standartlf;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStandartlf(): ?string
    {
        return $this->standartlf;
    }

    public function setStandartlf(string $standartlf): self
    {
        $this->standartlf = $standartlf;

        return $this;
    }

    public function getLoeschfrist(): ?string
    {
        return $this->loeschfrist;
    }

    public function setLoeschfrist(?string $loeschfrist): self
    {
        $this->loeschfrist = $loeschfrist;

        return $this;
    }

    public function getSpeicherorte(): ?string
    {
        return $this->speicherorte;
    }

    public function setSpeicherorte(string $speicherorte): self
    {
        $this->speicherorte = $speicherorte;

        return $this;
    }

    public function getLoeschbeauftragter(): ?string
    {
        return $this->loeschbeauftragter;
    }

    public function setLoeschbeauftragter(string $loeschbeauftragter): self
    {
        $this->loeschbeauftragter = $loeschbeauftragter;

        return $this;
    }

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): self
    {
        $this->beschreibung = $beschreibung;

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

    public function getPrevious(): ?self
    {
        return $this->previous;
    }

    public function setPrevious(?self $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->CreateAt;
    }

    public function setCreateAt(\DateTimeImmutable $CreateAt): self
    {
        $this->CreateAt = $CreateAt;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, VVTDatenkategorie>
     */
    public function getVvtdatenkategories(): Collection
    {
        return $this->vvtdatenkategories;
    }

    public function addVvtdatenkategory(VVTDatenkategorie $vvtdatenkategory): self
    {
        if (!$this->vvtdatenkategories->contains($vvtdatenkategory)) {
            $this->vvtdatenkategories[] = $vvtdatenkategory;
        }

        return $this;
    }

    public function removeVvtdatenkategory(VVTDatenkategorie $vvtdatenkategory): self
    {
        $this->vvtdatenkategories->removeElement($vvtdatenkategory);

        return $this;
    }

    public function getCloneOf(): ?self
    {
        return $this->cloneOf;
    }

    public function setCloneOf(?self $cloneOf): self
    {
        $this->cloneOf = $cloneOf;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getParentOf(): Collection
    {
        return $this->parentOf;
    }

    public function addParentOf(self $parentOf): self
    {
        if (!$this->parentOf->contains($parentOf)) {
            $this->parentOf[] = $parentOf;
            $parentOf->setCloneOf($this);
        }

        return $this;
    }

    public function removeParentOf(self $parentOf): self
    {
        if ($this->parentOf->removeElement($parentOf)) {
            // set the owning side to null (unless already changed)
            if ($parentOf->getCloneOf() === $this) {
                $parentOf->setCloneOf(null);
            }
        }

        return $this;
    }

    
    public function __clone()
    {
        unset($this->vvtdatenkategories);
        $this->vvtdatenkategories = new ArrayCollection();
    }
    
}
