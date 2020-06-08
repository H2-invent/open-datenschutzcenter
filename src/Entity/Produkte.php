<?php

namespace App\Entity;

use App\Repository\ProdukteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProdukteRepository::class)
 */
class Produkte
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
     * @ORM\Column(type="boolean")
     */
    private $activ;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="produktes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToMany(targetEntity=VVT::class, mappedBy="produkt")
     */
    private $Vvts;

    public function __construct()
    {
        $this->Vvts = new ArrayCollection();
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

    public function getActiv(): ?bool
    {
        return $this->activ;
    }

    public function setActiv(bool $activ): self
    {
        $this->activ = $activ;

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

    /**
     * @return Collection|VVT[]
     */
    public function getVvts(): Collection
    {
        return $this->Vvts;
    }

    public function addVvt(VVT $vvt): self
    {
        if (!$this->Vvts->contains($vvt)) {
            $this->Vvts[] = $vvt;
            $vvt->addProdukt($this);
        }

        return $this;
    }

    public function removeVvt(VVT $vvt): self
    {
        if ($this->Vvts->contains($vvt)) {
            $this->Vvts->removeElement($vvt);
            $vvt->removeProdukt($this);
        }

        return $this;
    }
}
