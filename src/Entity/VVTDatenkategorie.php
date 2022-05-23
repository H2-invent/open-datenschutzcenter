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
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="vVTDatenkategories")
     */
    private $team;

    /**
     * @ORM\Column(type="text")
     */
    private $datenarten;

    /**
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\ManyToMany(targetEntity=Loeschkonzept::class, mappedBy="vvtdatenkategories")
     */
    private $loeschkonzept;

    public function __construct()
    {
        $this->loeschkonzept = new ArrayCollection();
    }

    public function __toString()
    {
        //shorthand if/else
        return ($this->loeschkonzept) ? $this->name . " (".$this->loeschkonzept.")" : $this->name;
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
}
