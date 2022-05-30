<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Entity;

use App\Repository\VVTDatenkategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=VVTDatenkategorieRepository::class)
 */
class VVTDatenkategorie
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
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="vVTDatenkategories")
     */
    private $team;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $datenarten;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\ManyToMany(targetEntity=Loeschkonzept::class, mappedBy="vvtdatenkategories",cascade={"persist", "remove"})
     */
    private $loeschkonzept;

    /**
     * @ORM\OneToOne(targetEntity=VVTDatenkategorie::class, cascade={"persist", "remove"})
     */
    private $previous;

    /**
     * @ORM\Column(type="datetime_immutable", nullable = true)
     */
    private $CreatedAt;


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="vVTDatenkategories")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=VVTDatenkategorie::class, inversedBy="parentOf")
     */
    private $cloneOf;

    /**
     * @ORM\OneToMany(targetEntity=VVTDatenkategorie::class, mappedBy="cloneOf")
     */
    private $parentOf;

    public function __construct()
    {
        $this->loeschkonzept = new ArrayCollection();
        $this->parentOf = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        if ($this->getLoeschkonzept())
        {
            $l = $this->getLoeschkonzept()->last();
            
            if ($l == false)
            {
                return $this->name;
            }
            else {
                return $this->name . " (". $l. ")";
            }
        } 
        else {
            return $this->name;
        }
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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }


    public function getDatenarten(): ?string
    {
        return $this->datenarten;
    }

    public function setDatenarten(string $datenarten): self
    {
        $this->datenarten = $datenarten;

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

    /**
     * @return Collection<int, Loeschkonzept>
     */
    public function getLoeschkonzept(): Collection
    {
        return $this->loeschkonzept;
    }

    public function addLoeschkonzept(Loeschkonzept $loeschkonzept): self
    {
        if (!$this->loeschkonzept->contains($loeschkonzept)) {
            $this->loeschkonzept[] = $loeschkonzept;
            $loeschkonzept->addVvtdatenkategory($this);
        }

        return $this;
    }

    public function removeLoeschkonzept(Loeschkonzept $loeschkonzept): self
    {
        if ($this->loeschkonzept->removeElement($loeschkonzept)) {
            $loeschkonzept->removeVvtdatenkategory($this);
        }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

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
    public function getLastLoeschkonzept(): ?Loeschkonzept{
       $losch = $this->loeschkonzept->last();
       if($losch !== false){
           return $losch;
       }
       return  null;
    }
}
