<?php

namespace App\Entity;

use App\Repository\AuditTomAbteilungRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditTomAbteilungRepository::class)]
class AuditTomAbteilung
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $name;

    #[ORM\Column(type: 'boolean')]
    private $activ;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'abteilungen')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    #[ORM\OneToMany(targetEntity: VVT::class, mappedBy: 'abteilung')]
    private $vVTs;

    public function __construct()
    {
        $this->vVTs = new ArrayCollection();
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
    public function getVVTs(): Collection
    {
        return $this->vVTs;
    }

    public function addVVT(VVT $vVT): self
    {
        if (!$this->vVTs->contains($vVT)) {
            $this->vVTs[] = $vVT;
            $vVT->setAbteilung($this);
        }

        return $this;
    }

    public function removeVVT(VVT $vVT): self
    {
        if ($this->vVTs->contains($vVT)) {
            $this->vVTs->removeElement($vVT);
            // set the owning side to null (unless already changed)
            if ($vVT->getAbteilung() === $this) {
                $vVT->setAbteilung(null);
            }
        }

        return $this;
    }
}
