<?php

namespace App\Entity;

use App\Repository\VVTStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VVTStatusRepository::class)]
class VVTStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $color;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $network;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'vVTStatuses')]
    private $team;

    #[ORM\Column(type: 'boolean')]
    private $activ = true;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getNetwork(): ?bool
    {
        return $this->network;
    }

    public function setNetwork(?bool $network): self
    {
        $this->network = $network;

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
}
