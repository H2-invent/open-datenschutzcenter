<?php

namespace App\Entity;

use App\Repository\DatenweitergabeStandRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DatenweitergabeStandRepository::class)
 */
class DatenweitergabeStand
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $network;

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

    public function getNetwork(): ?bool
    {
        return $this->network;
    }

    public function setNetwork(?bool $network): self
    {
        $this->network = $network;

        return $this;
    }
}
