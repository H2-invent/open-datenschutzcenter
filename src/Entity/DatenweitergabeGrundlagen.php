<?php

namespace App\Entity;

use App\Repository\DatenweitergabeGrundlagenRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DatenweitergabeGrundlagenRepository::class)]
class DatenweitergabeGrundlagen extends Preset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $name;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'datenweitergabeGrundlagens')]
    private $team;

    #[ORM\Column(type: 'boolean')]
    private $activ = true;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'ignoredDWGrounds')]
    protected $ignoredInTeams;

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
