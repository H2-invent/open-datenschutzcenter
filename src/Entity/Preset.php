<?php

namespace App\Entity;

use App\Repository\ProdukteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

abstract class Preset
{
    #[ORM\Column(type: 'boolean')]
    protected $inherited = false;

    protected $ignoredInTeams;

    public function isInherited(): bool
    {
        return $this->inherited;
    }

    public function setInherited(bool $inherited): self
    {
        $this->inherited = $inherited;

        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getIgnoredInTeams(): ?Collection
    {
        return $this->ignoredInTeams;
    }

    public function addIgnoredInTeam(Team $team): self
    {
        if (!$this->ignoredInTeams->contains($team)) {
            $this->ignoredInTeams[] = $team;
        }

        return $this;
    }

    public function removeIgnoredInTeam(Team $team): self
    {
        if ($this->ignoredInTeams->contains($team)) {
            $this->ignoredInTeams->removeElement($team);
        }

        return $this;
    }

    public function getClass(): string
    {
        return (new \ReflectionClass($this))->getName();
    }
}
